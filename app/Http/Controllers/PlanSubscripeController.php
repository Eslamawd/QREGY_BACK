<?php

namespace App\Http\Controllers;

use App\Models\PlanSubscripe;
use Illuminate\Http\Request;
use Stichoza\GoogleTranslate\GoogleTranslate;

class PlanSubscripeController extends Controller
{
    public function index()
    {
        return response()->json(PlanSubscripe::all());
    }

    public function show($id)
    {
        $plan = PlanSubscripe::find($id);
        if (!$plan) {
            return response()->json(['message' => 'الخطة غير موجودة'], 404);
        }
        return response()->json($plan);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
            'duration_days' => 'required|integer|min:1',
            'max_restaurants' => 'required|integer|min:1',
            'max_tables' => 'required|integer|min:1',
            'max_items' => 'required|integer|min:1',
            'vip_support' => 'boolean',
            'features' => 'nullable|array',
            'features.*.title' => 'nullable|string|max:255',
            'features.*.title_ar' => 'nullable|string|max:255',
        ]);

        $translator = new GoogleTranslate('en');
        $validated['name_en'] = $translator->translate($validated['name']);
      
        $plan = PlanSubscripe::create($validated);
        return response()->json($plan, 201);
    }

    public function update(Request $request, $id)
    {
        $plan = PlanSubscripe::find($id);
        if (!$plan) {
            return response()->json(['message' => 'الخطة غير موجودة'], 404);
        }

        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'price' => 'sometimes|numeric|min:0',
            'duration_days' => 'sometimes|integer|min:1',
            'max_restaurants' => 'sometimes|integer|min:1',
            'max_tables' => 'sometimes|integer|min:1',
            'max_items' => 'sometimes|integer|min:1',
            'vip_support' => 'sometimes|boolean',
            'features' => 'nullable|array',
            'features.*.title' => 'nullable|string|max:255',
            'features.*.title_ar' => 'nullable|string|max:255',
        ]);

        if (isset($validated['name'])) {
            $translator = new GoogleTranslate('en');
            $validated['name_en'] = $translator->translate($validated['name']);
        }

      

        $plan->update($validated);
        return response()->json($plan);
    }

    public function destroy($id)
    {
        $plan = PlanSubscripe::find($id);
        if (!$plan) {
            return response()->json(['message' => 'الخطة غير موجودة'], 404);
        }

        $plan->delete();
        return response()->json(['message' => 'تم حذف الخطة بنجاح']);
    }
}
