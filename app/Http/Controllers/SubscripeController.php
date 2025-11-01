<?php

namespace App\Http\Controllers;

use App\Mail\SendSubMail;
use App\Models\PlanSubscripe;
use App\Models\Subscripe;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Container\Attributes\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\ValidationException;

class SubscripeController extends Controller
{
    //

    public function index () {
        
        $user = auth()->user();
        $subscriptions = $user->subscripe()->latest()->paginate(10);
        return response()->json(['subscriptions' => $subscriptions]);
    }
    
    public function getByAdmin () {
  $subscriptions = Subscripe::with('user:id,name,email,phone')->paginate(10);
        return response()->json(['subscriptions' => $subscriptions]);
    }

public function store(Request $request, $id)
{
    $user = auth()->user();
    $plan = PlanSubscripe::findOrFail($id);

    $price = $plan->price;
    $durationDays = $plan->duration_days;

    $totalInCents = (int) round($price * 100);
    $balance = $user->balanceInt;

    // ✅ تحقق من رصيد المستخدم
    if ($balance < $totalInCents) {
        return response()->json([
            'message' => 'Your wallet balance is insufficient.',
        ], 422);
    }

    DB::beginTransaction();

    try {
        $activeSubscription = $user->activeSubscription;

        if ($activeSubscription && $activeSubscription->end_date > now()) {
            $endDate = Carbon::parse($activeSubscription->end_date);
            $daysLeft = now()->diffInDays($endDate, false);

            // ✅ السماح بالتجديد فقط قبل 7 أيام أو أقل
            if ($daysLeft > 7) {
                return response()->json([
                    'message' => 'You can only renew your subscription within 7 days before it expires.',
                    'days_left' => $daysLeft
                ], 403);
            }

            // ✅ الاشتراك الجديد يبدأ بعد نهاية القديم
            $startsAt = $endDate;
        } else {
            // ✅ لو مفيش اشتراك شغال
            $startsAt = now();
        }

        $endsAt = $startsAt->copy()->addDays($durationDays);

        // ✅ إنشاء اشتراك جديد
        $newSubscription = Subscripe::create([
            'user_id'    => $user->id,
            'plan_id'    => $plan->id,
            'plan'       => $plan->name,
            'price'      => $price,
            'start_date' => $startsAt,
            'end_date'   => $endsAt,
        ]);

        // ✅ خصم الرصيد بعد نجاح العملية
        $user->withdraw($totalInCents);
        $affiliate = optional($user->affiliateEarnings)->affiliate;

         if ($affiliate && $affiliate->user) {
         $affiliate->user->deposit($totalInCents * 0.15); // 15% عمولة
        }

        DB::commit();

        // ✅ إرسال إيميل تأكيد بعد الاشتراك
        Mail::to($user->email)->send(new SendSubMail($newSubscription));

        return response()->json([
            'message'      => 'Subscription purchased successfully.',
            'subscription' => $newSubscription,
        ], 201);

    } catch (\Exception $e) {
        DB::rollBack();

        return response()->json([
            'message' => 'Something went wrong: ' . $e->getMessage(),
        ], 500);
    }
}


public function count(){
    $count = Subscripe::count();
    return response()->json(['count' => $count]);
}

public function getRevenue() {
    $revenue = Subscripe::sum('price');
    return response()->json(['revenue' => $revenue]);
}

}
