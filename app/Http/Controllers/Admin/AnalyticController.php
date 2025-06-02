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
        $statuses = ['delivered', 'canceled', 'in_transit'];
        $currentMonthStatusCounts = [];
        foreach ($statuses as $status) {
            $currentMonthStatusCounts[$status] = SendParcel::where('status', $status)
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->count();
        }
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
            'monthlySendParcel' => $monthlySendParcel,
            'monthlyEarnings' => $monthlyEarnings,
            'currentMonthStatusCounts' => $currentMonthStatusCounts,
            'bookings' => $bookings,
        ]);

    }
}
