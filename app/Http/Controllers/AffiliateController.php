<?php

namespace App\Http\Controllers;

use App\Models\Affiliate;
use Illuminate\Http\Request;

class AffiliateController extends Controller
{
    //

    public function index(Request $request)
    {
        $user = auth()->user();
        $affiliate = $user->affiliate()->with('earnings.user.subscripe')->paginate(10);
        return response()->json(['affiliate' => $affiliate]);
        //
    }
    
    public function getByAdmin(Request $request)
    {
        $affiliate = Affiliate::with('user')->paginate(10);
        return response()->json(['affiliate' => $affiliate]);
        //
    }
    public function showByAdmin(Request $request, Affiliate $affiliate)
    {
        $affiliate->load('user', 'earnings');
        return response()->json(['affiliate' => $affiliate]);
        //
    }

}
