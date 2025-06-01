<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Carbon\Carbon;

class AnalyticController extends Controller
{
    public function UserAnalytics()
    {
        $users = User::with('sendParcel')->get();
        $monthlyEarnings = array_fill(0, 12, 0); // index 0 = Jan
        $monthlyRides = array_fill(0, 12, 0);

        foreach ($users as $user) {
            foreach ($user->sendParcel as $parcel) {
                $month = Carbon::parse($parcel->created_at)->month - 1; // 0-based index
                $monthlyRides[$month]++;
                $monthlyEarnings[$month] += $parcel->price ?? 0; // Use actual field name
            }
        }

        // Pie chart counts
        $completed = 0;
        $active = 0;
        $scheduled = 0;

        foreach ($users as $user) {
            foreach ($user->sendParcel as $parcel) {
                switch ($parcel->status) {
                    case 'delivered':
                        $completed++;
                        break;
                    case 'in_transit':
                        $active++;
                        break;
                    case 'cancelled':
                        $scheduled++;
                        break;
                }
            }
        }

        return response()->json([
            'monthlyrides' => $monthlyRides,
            'piegraphs' => [$completed, $active, $scheduled],

        ]);
    }
}
