<?php
// app/Http/Controllers/PaymentController.php

namespace App\Http\Controllers;

use App\Models\PaymentReq;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use App\Models\User;
use Illuminate\Support\Facades\Log;

class PaymentController extends Controller
{

    /**
     * عرض جميع طلبات الدفع (للمسؤول فقط)
     */
    public function index()
    {
        $payments = PaymentReq::with('user')->orderBy('created_at', 'desc')->paginate(10);
        return response()->json(['payments' => $payments]);
    }

    /**
     * عرض طلبات الدفع الخاصة بالمستخدم الحالي
     */
    public function getByUser(Request $request)
    {
        $user = $request->user();
        $payments = $user->payments()->orderBy('created_at', 'desc')->paginate(10);
        return response()->json(['payments' => $payments]);
    }

        /**
        * تحديث حالة طلب الدفع (للمسؤول فقط)
        */
    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:pending,approved,rejected',
        ]);

        $payment = PaymentReq::findOrFail($id);
        $payment->update(['status' => $request->status]);

        return response()->json(['message' => 'Payment status updated successfully']);
    }

    /**
     * استلام طلبات الدفع عبر التحويل البنكي
     */

    public function banking(Request $request)
    {
        // من المفترض أنك بتستقبل بيانات الدفع من الـ frontend
       $validated =  $request->validate([
            'amount' => 'required|numeric|min:1',
            'image'  => 'required|image|mimes:jpg,jpeg,png,webp|max:2048', 
        ]);

        $user = $request->user();

         if ($request->hasFile('image')) {
        $imagePath = $request->file('image')->store('banking', 'public'); // غيّرت المجلد إلى 'products'
        $validated['image'] = $imagePath;
    }
        $validated['user_id'] = $user->id;
        $paymentReq = PaymentReq::create($validated);
     
            return response()->json([
                'message' => 'تم شحن المحفظة بنجاح',
                'payment' => $paymentReq,
            ]);
       
    }



}
