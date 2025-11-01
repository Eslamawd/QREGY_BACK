<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderItemOption;
use App\Models\Item;
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
        // إنشاء الطلب
        $order = Order::create([
            'restaurant_id' => $request->restaurant_id,
            'table_id' => $request->table_id,
            'total_price' => $request->total_price,
            'status' => 'pending',
        ]);

        foreach ($request->items as $itemData) {
            $item = Item::find($itemData['item_id']);
            $orderItem = OrderItem::create([
                'order_id' => $order->id,
                'item_id' => $item->id,
                'comment'=> $itemData['comment'],
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
        $this->webSocket->sendNewOrder($data);

        return response()->json($data, 201);
    }

    public function updateStatus(Request $request, $id)
    {
        $validated = $request->validate([
            'status' => 'required|in:pending,in_progress,ready,delivered,payid,cancelled',
        ]);
        $order = Order::findOrFail($id);
        $order->update(['status' => $validated['status']]);

        // إرسال تحديث الحالة للمطبخ أو الكاشير

        $this->webSocket->sendOrderUpdated($order->id, $order->restaurant_id, $order->status);

        return response()->json($order);
    }

    public function removeOrderItem(Order $order, $itemId)
{
    

    $orderItem = $order->orderItems()->where('id', $itemId)->first();

    if (!$orderItem) {
        return response()->json(['message' => 'Item not found in order'], 404);
    }

    $orderItem->delete();

    // تحديث السعر الكلي للطلب
    $order->total_price = $order->orderItems()->sum('subtotal');
    $order->save();

    $orderResult = $order->load('orderItems.item.options');

    $this->webSocket->sendOrderUpdatedAll($order->id, $order->restaurant_id, $orderResult);

    return response()->json(['message' => 'Item removed successfully', 'order' => $orderResult]);
}

public function updateOrderItemQuantity(Request $request,Order $order, $itemId)
{
    $request->validate([
        'quantity' => 'required|integer|min:1'
    ]);

 

    $orderItem = $order->orderItems()->where('id', $itemId)->first();

    $orderItem->quantity = $request->quantity;
    $orderItem->subtotal = $orderItem->price * $request->quantity;
    $orderItem->save();

    $order->total_price = $order->orderItems()->sum('subtotal');
    $order->save();

    $this->webSocket->sendOrderUpdated($order->id, $order->restaurant_id, 'item_updated');

    return response()->json(['message' => 'Quantity updated', 'order' => $order->load('orderItems.item.options')]);
}


}
