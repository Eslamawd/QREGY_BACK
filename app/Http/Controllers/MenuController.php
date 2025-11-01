<?php

namespace App\Http\Controllers;

use App\Models\Menu;
use App\Models\Restaurant;
use Illuminate\Http\Request;
use Stichoza\GoogleTranslate\GoogleTranslate;

class MenuController extends Controller
{
    public function index()
    {
        return response()->json(Menu::with('categories.items')->get());
    }


    public function store(Request $request)
    {

           $validated = $request->validate([
            'name' => 'required|string|max:255',
            'restaurant_id' => 'required|exists:restaurants,id',
        ]);

        $restaurant = Restaurant::findOrFail($validated['restaurant_id']);
         // تحقق من ملكية المستخدم
    if ($restaurant->user_id !== auth()->id()) {
        return response()->json(['error' => 'Unauthorized'], 403);
    }

        
        // ✅ ترجمة الاسم تلقائيًا إلى الإنجليزية
        $translator = new GoogleTranslate('en');
        $translatedName = $translator->translate($validated['name']);
        $menu = Menu::create([
            'name' => $validated['name'],
            'name_en' => $translatedName,
            'restaurant_id' => $validated['restaurant_id']
        ]);
        return response()->json($menu, 201);
    }

    public function show(Menu $menu)
    {
        return response()->json($menu->load('categories.items'));
    }

    public function update(Request $request, Menu $menu)
    {

         // تحقق من ملكية المستخدم
    if ($menu->restaurant->user_id !== auth()->id()) {
        return response()->json(['error' => 'Unauthorized'], 403);
    }

        
           $validated = $request->validate([
            'name' => 'required|string|max:255',
        ]);

        
        // ✅ ترجمة الاسم تلقائيًا إلى الإنجليزية
        $translator = new GoogleTranslate('en');
        $translatedName = $translator->translate($validated['name']);
        $menu->update([
            'name' => $validated['name'],
            'name_en' => $translatedName,
        ]);
        return response()->json($menu);
    }

    public function destroy(Menu $menu)
    {
                 // تحقق من ملكية المستخدم
    if ($menu->restaurant->user_id !== auth()->id()) {
        return response()->json(['error' => 'Unauthorized'], 403);
    }

        $menu->delete();
        return response()->json(null, 204);
    }
}
