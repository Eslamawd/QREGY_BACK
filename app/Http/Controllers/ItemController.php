<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Item;
use App\Http\Requests\Created\ItemRequest;
use App\Http\Requests\Updated\ItemUpdatedRequest;
use App\Models\Restaurant;
use Illuminate\Support\Facades\Storage;
use Stichoza\GoogleTranslate\GoogleTranslate;

class ItemController extends Controller
{
    public function store(ItemRequest $request)
    {

    $category = Category::findOrFail($request->category_id);
    $restaurant = $category->menu->restaurant;
    $user = $restaurant->user; // صححت هنا

    // تحقق من ملكية المستخدم
    if ($restaurant->user_id !== auth()->id()) {
        return response()->json(['error' => 'Unauthorized'], 403);
    }

    // تحقق من الحد الأقصى للطاولات حسب خطة الاشتراك
    $maxItems = optional($user->activeSubscription->planSub)->max_items ?? 0;
    $currentItems = $category->items()->count();

    if ($currentItems >= $maxItems) {
        return response()->json([
            'message' => 'You have reached the maximum number of Items allowed for your plan.'
        ], 403);
    }


        $translator = new GoogleTranslate('en');
        $data = $request->validated();
           // ✅ رفع الصورة وتخزين مسارها
        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('items', 'public');
        }

      
        $data['name_en'] = $translator->translate($data['name']);
        $data['description_en'] = $translator->translate($data['description']);

        $item = Item::create($data);

        return response()->json($item, 201);
    }

    public function show(Item $item)
    {
        return response()->json($item->load('options'));
    }

    public function update(ItemUpdatedRequest $request, Item $item)
    {
                       // تحقق من ملكية المستخدم
    if ($item->category->menu->restaurant->user_id !== auth()->id()) {
        return response()->json(['error' => 'Unauthorized'], 403);
    }
        $translator = new GoogleTranslate('en');
        $data = $request->validated();

        // ✅ لو في صورة جديدة، نحذف القديمة ونرفع الجديدة
        if ($request->hasFile('image')) {
            if ($item->image && Storage::disk('public')->exists($item->image)) {
                Storage::disk('public')->delete($item->image);
            }
            $data['image'] = $request->file('image')->store('items', 'public');
        }
        if (isset($data['name'])) {
            $data['name_en'] = $translator->translate($data['name']);
        }
        if (isset($data['description'])) {
            $data['description_en'] = $translator->translate($data['description']);
        }

        $item->update($data);

        return response()->json($item);
    }

    public function destroy(Item $item)
    {
                             // تحقق من ملكية المستخدم
    if ($item->category->menu->restaurant->user_id !== auth()->id()) {
        return response()->json(['error' => 'Unauthorized'], 403);
    }
       if ($item->image && Storage::disk('public')->exists($item->image)) {
            Storage::disk('public')->delete($item->image);
        }

        $item->delete();
        return response()->json(null, 204);
    }
}
