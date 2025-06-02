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
        $totaldelivered = $ridesQuery->where('status', 'delivered')->count();
        $totalActive = $ridesQuery->where('status', 'in_transit')->count();
        $totalscheduled = $ridesQuery->where('status', 'ordered')->count();
        $totalpickup = $ridesQuery->where('status', 'picked_up')->count();



        // Get monthly sendParcel count and earnings for the current year
        $monthlySendParcel = [];
        $monthlyEarnings = [];

        for ($month = 1; $month <= 12; $month++) {
            $count = User::whereHas('sendParcel', function ($query) use ($month) {
                $query->whereMonth('created_at', $month)
                    ->whereYear('created_at', now()->year);
            })->with([
                        'sendParcel' => function ($query) use ($month) {
                            $query->whereMonth('created_at', $month)
                                ->whereYear('created_at', now()->year);
                        }
                    ])->get()->pluck('sendParcel')->flatten()->count();

            $earning = User::whereHas('sendParcel', function ($query) use ($month) {
                $query->whereMonth('created_at', $month)
                    ->whereYear('created_at', now()->year);
            })->with([
                        'sendParcel' => function ($query) use ($month) {
                            $query->whereMonth('created_at', $month)
                                ->whereYear('created_at', now()->year);
                        }
                    ])->get()->pluck('sendParcel')->flatten()->sum('amount'); // assuming 'amount' is the earning field

            $monthlySendParcel[] = $count;
            $monthlyEarnings[] = $earning;
        }

        // Current month sendParcel status counts
        $currentMonthStatusCounts = [
            SendParcel::whereNotNull('payment_method')->where('status', 'delivered')->count(),
            SendParcel::whereNotNull('payment_method')->where('status', 'is_transit')->count(),
            SendParcel::whereNotNull('payment_method')->where('status', 'ordered')->count(),
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

    public function UserAnalytics()
    {
        $usersQuery = User::get();
        // Monthly number array of users created in the current year
        $monthlyUserCreated = [];
        for ($month = 1; $month <= 12; $month++) {
            $count = User::whereYear('created_at', now()->year)
                ->whereMonth('created_at', $month)
                ->count();
            $monthlyUserCreated[] = $count;
        }
        $pieData = [
            $usersQuery->count(),
            $usersQuery->where('is_active', 1)->count(),
            $usersQuery->where('is_active', 0)->count()
        ];
        $startDate = Carbon::now()->subMonths(2)->startOfMonth();
        $endDate = Carbon::now()->subMonths(2)->endOfMonth();
        
        $LeavestartDate = Carbon::now()->subMonths(12)->startOfMonth();
        $LeaveendDate = Carbon::now()->subMonths(12)->endOfMonth();

        return response()->json([
            'monthlyUserCreated' => $monthlyUserCreated,
            'preData' => $pieData,
            "cardData" => [
                [
                    "name" => 'Total Users',
                    "value" => $usersQuery->count(),
                ],
                [
                    "name" => 'Total Active Users',
                    "value" => $usersQuery->where('is_active', 1)->count(),
                ],
                [
                    "name" => 'Total Inactive Users',
                    "value" => $usersQuery->where('is_active', 0)->count(),
                ],
                [
                    "name" => 'Total Block Users',
                    "value" => $usersQuery->where('is_active', 3)->count(),
                ],
                [
                    "name" => 'Total New Users',
                    "value" => $usersQuery
                        ->where('is_active', 1)
                        ->where(function ($query) {
                            $query->whereDate('created_at', Carbon::today())
                                ->orWhereDate('created_at', Carbon::yesterday());
                        })
                        ->count(),
                ],
                [
                    "name" => 'Churn Rate',
                    "value" => User::whereHas('sendParcel', function ($query) use ($startDate, $endDate) {
                            $query->whereBetween('created_at', [$startDate, $endDate])
                                ->whereRaw('created_at = (SELECT MAX(created_at) FROM send_parcels WHERE user_id = users.id)');
                        })
                        ->count(),
                ],
                [
                    "name" => 'Retention Rate',
                    "value" => User::whereHas('sendParcel', function ($query) use ($LeavestartDate, $LeaveendDate) {
                            $query->whereBetween('created_at', [$LeavestartDate, $LeaveendDate])
                                ->whereRaw('created_at = (SELECT MAX(created_at) FROM send_parcels WHERE user_id = users.id)');
                        })
                        ->count(),
                ],
            ]
        ]);

    }



}
