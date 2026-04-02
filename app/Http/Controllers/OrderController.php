<?php

namespace App\Http\Controllers;

use App\Jobs\SendNewOrderNotification;
use App\Jobs\SendUpdateOrderNotification;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderItemOption;
use App\Models\Item;
use App\Models\Restaurant;
use App\Models\Table;
use App\Services\WebSocketService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http; // لو هنبعت للـ WebSocket Server

class OrderController extends Controller
{

     protected $webSocket;

    public function __construct(WebSocketService $webSocket)
    {
        $this->webSocket = $webSocket;
    }
    public function index()
    {
        // كل الطلبات الجديدة أو الجارية
        return response()->json(
            Order::with(['table', 'orderItems.item', 'orderItems.options'])->latest()->get()
        );
    }

    public function show(Order $order) {
    return response()->json($order);
    }

    public function getByKitchen(Request $request)
    {
        $restaurant = $request->get('restaurant');

        $orders = Order::with(['table', 'orderItems.item', 'orderItems.options'])
            ->where('restaurant_id', $restaurant->id)
            ->whereIn('status', ['pending', 'in_progress'])
            ->latest()
            ->get();

            if ($orders->isEmpty()) {
                return response()->json(['message' => 'No orders found for this kitchen.'], 404);
            }

        return response()->json($orders);
    }
    public function getByCashier(Request $request)
    {
        $restaurant = $request->get('restaurant');

        $orders = Order::with(['table', 'orderItems.item', 'orderItems.options'])
            ->where('restaurant_id', $restaurant->id)
            ->whereIn('status', ['delivered', 'ready'])
            ->latest()
            ->get();

            if ($orders->isEmpty()) {
                return response()->json(['message' => 'No orders found for this cashier.'], 404);
            }

        return response()->json($orders);
    }
    
    public function store(Request $request)
    {
        $validated = $request->validate([
            'restaurant_id' => 'required|integer|exists:restaurants,id',
            'table_id' => 'nullable|integer|exists:tables,id',
            'total_price' => 'required|numeric|min:0',
            'customer_lat' => 'nullable|numeric|between:-90,90|required_with:customer_lng',
            'customer_lng' => 'nullable|numeric|between:-180,180|required_with:customer_lat',
            'delivery_address' => 'nullable|string|max:500',
            'items' => 'required|array|min:1',
            'items.*.item_id' => 'required|integer|exists:items,id',
            'items.*.comment' => 'nullable|string|max:500',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.options' => 'nullable|array',
            'items.*.options.*' => 'integer|exists:item_options,id',
        ]);

        /** @var Restaurant|null $restaurant */
        $restaurant = $request->input('restaurant');
        if (!$restaurant instanceof Restaurant) {
            $restaurant = Restaurant::findOrFail($validated['restaurant_id']);
        }

        if (!empty($validated['table_id'])) {
            $table = Table::where('id', $validated['table_id'])
                ->where('restaurant_id', $restaurant->id)
                ->first();

            if (!$table) {
                return response()->json([
                    'message' => 'Selected table does not belong to this restaurant.',
                ], 422);
            }
        }

        $isDeliveryOrder = empty($validated['table_id']);
        if (
            $isDeliveryOrder
            && $restaurant->latitude !== null
            && $restaurant->longitude !== null
        ) {
            if (!isset($validated['customer_lat'], $validated['customer_lng'])) {
                return response()->json([
                    'message' => 'Customer location is required for delivery orders.',
                    'code' => 'customer_location_required',
                ], 422);
            }

            $distanceKm = $this->calculateDistanceKm(
                (float) $validated['customer_lat'],
                (float) $validated['customer_lng'],
                (float) $restaurant->latitude,
                (float) $restaurant->longitude,
            );

            $deliveryRadiusKm = (float) ($restaurant->delivery_radius_km ?? 10);

            if ($distanceKm > $deliveryRadiusKm) {
                return response()->json([
                    'message' => 'Customer is outside the delivery range for this restaurant.',
                    'code' => 'out_of_delivery_range',
                    'distance_km' => round($distanceKm, 2),
                    'delivery_radius_km' => round($deliveryRadiusKm, 2),
                ], 422);
            }
        }

        // إنشاء الطلب
        $order = Order::create([
            'restaurant_id' => $restaurant->id,
            'table_id' => $validated['table_id'] ?? null,
            'total_price' => $validated['total_price'],
            'delivery_address' => $validated['delivery_address'] ?? null,
            'customer_lat' => $validated['customer_lat'] ?? null,
            'customer_lng' => $validated['customer_lng'] ?? null,
            'status' => 'pending',
        ]);

        foreach ($validated['items'] as $itemData) {
            $item = Item::find($itemData['item_id']);
            $orderItem = OrderItem::create([
                'order_id' => $order->id,
                'item_id' => $item->id,
                'comment'=> $itemData['comment'] ?? null,
                'quantity' => $itemData['quantity'],
                'price' => $item->price,
                'subtotal' => $item->price * $itemData['quantity'],
               
            ]);

            if (!empty($itemData['options'])) {
                foreach ($itemData['options'] as $optionId) {
                    OrderItemOption::create([
                        'order_item_id' => $orderItem->id,
                        'item_option_id' => $optionId,
                    ]);
                }
            }
        }

        // 🔔 إرسال إشعار للمطبخ / الكاشير عبر WebSocket Server

           // ✅ كده الكود أنضف
         $data = Order::with([
       'table:id,name', // تحميل اسم الطاولة فقط لتقليل البيانات
       'restaurant:id,name',
       'orderItems.item',
       'orderItems.options'
   ])->find($order->id);

   
        SendNewOrderNotification::dispatch($data);

        return response()->json($data, 201);
    }

    private function calculateDistanceKm(float $lat1, float $lng1, float $lat2, float $lng2): float
    {
        $earthRadiusKm = 6371;
        $dLat = deg2rad($lat2 - $lat1);
        $dLng = deg2rad($lng2 - $lng1);

        $a = sin($dLat / 2) * sin($dLat / 2)
            + cos(deg2rad($lat1)) * cos(deg2rad($lat2))
            * sin($dLng / 2) * sin($dLng / 2);
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $earthRadiusKm * $c;
    }

    public function updateStatus(Request $request, $id)
    {
        $validated = $request->validate([
            'status' => 'required|in:pending,in_progress,ready,delivered,payid,cancelled',
        ]);
        $order = Order::findOrFail($id);
        $order->update(['status' => $validated['status']]);

        // إرسال تحديث الحالة للمطبخ أو الكاشير
SendUpdateOrderNotification::dispatch(
            $order->id, 
            $order->restaurant_id, 
            $order->status // 👈 المتغير المفقود
        );

        return response()->json($order);
    }



}
