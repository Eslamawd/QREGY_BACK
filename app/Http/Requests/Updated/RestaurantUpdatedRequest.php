<?php

namespace App\Http\Requests\Updated;

use Illuminate\Foundation\Http\FormRequest;

class RestaurantUpdatedRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // اسمح بالتحديث (ممكن تضيف شرط ملكية لاحقاً)
    }

    public function rules(): array
    {
        return [
            'name' => 'sometimes|string|max:255',
            'type' => 'sometimes|string|in:restaurant,coffee',
            'phone' => 'sometimes|string|max:20',
            'address' => 'sometimes|string|max:255',
            'latitude' => 'sometimes|nullable|numeric|between:-90,90',
            'longitude' => 'sometimes|nullable|numeric|between:-180,180',
            'delivery_radius_km' => 'sometimes|nullable|numeric|min:1|max:100',
            'open_time' => 'sometimes|nullable|date_format:H:i',
            'close_time' => 'sometimes|nullable|date_format:H:i',
            'logo' => 'sometimes|image|mimes:jpg,jpeg,png|max:2048',
            'cover' => 'sometimes|image|mimes:jpg,jpeg,png|max:4096',
        ];
    }
}
