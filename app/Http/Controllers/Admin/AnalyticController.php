<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SendParcel;
use App\Models\User;
use Illuminate\Http\Request;
use Carbon\Carbon;

class AnalyticController extends Controller
{
    public function dashboard()
    {

        $users = User::get();
        $totalUsers = $users->count();
        $totalRider = $users->where('role', 'rider')->count();
        $totalActiveRider = $users->where('is_active', 1)->count();
        $revenue = SendParcel::sum('amount');

        $ridesQuery = SendParcel::whereNotNull('payment_method')
            ->where('status', 'delivered')
            ->with('user', 'rider', 'acceptedBid');
        $totaldelivered = $ridesQuery->where('status','delivered')->count();
        $totalActive = $ridesQuery->where('status','in_transit')->count();
        $totalscheduled = $ridesQuery->where('status','ordered')->count();
        $totalpickup = $ridesQuery->where('status','picked_up')->count();



        // Get monthly sendParcel count and earnings for the current year
        $monthlySendParcel = [];
        $monthlyEarnings = [];

        for ($month = 1; $month <= 12; $month++) {
            $count = User::whereHas('sendParcel', function ($query) use ($month) {
            $query->whereMonth('created_at', $month)
                  ->whereYear('created_at', now()->year);
            })->with(['sendParcel' => function ($query) use ($month) {
            $query->whereMonth('created_at', $month)
                  ->whereYear('created_at', now()->year);
            }])->get()->pluck('sendParcel')->flatten()->count();

            $earning = User::whereHas('sendParcel', function ($query) use ($month) {
            $query->whereMonth('created_at', $month)
                  ->whereYear('created_at', now()->year);
            })->with(['sendParcel' => function ($query) use ($month) {
            $query->whereMonth('created_at', $month)
                  ->whereYear('created_at', now()->year);
            }])->get()->pluck('sendParcel')->flatten()->sum('amount'); // assuming 'amount' is the earning field

            $monthlySendParcel[] = $count;
            $monthlyEarnings[] = $earning;
        }

        // Current month sendParcel status counts
        $currentMonthStatusCounts = [
            SendParcel::whereNotNull('payment_method')->where('status','delivered')->count(),
            SendParcel::whereNotNull('payment_method')->where('status','is_transit')->count(),
            SendParcel::whereNotNull('payment_method')->where('status','ordered')->count(),
        ]; 
        $bookings = SendParcel::whereNotNull('payment_method')
            ->with('user', 'rider', 'acceptedBid')
            ->latest()
            ->take(5)
            ->get();
        // Example return (customize as needed)
        return response()->json([
            'site' => [
                'totalUsers' => $totalUsers,
                'totalRider' => $totalRider,
                'totalActiveRider' => $totalActiveRider,
                'revenue' => $revenue,
            ],
            "rides" => [
                'totaldelivered' => $totaldelivered,
                'totalActive' => $totalActive,
                'totalscheduled' => $totalscheduled,
                'totalpickup' => $totalpickup,
            ], 
            'monthlySendParcel' => $monthlySendParcel,
            'monthlyEarnings' => $monthlyEarnings,
            'currentMonthStatusCounts' => $currentMonthStatusCounts,
            'bookings' => $bookings,
        ]);

    }
}
