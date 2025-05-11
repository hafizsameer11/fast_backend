<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use App\Models\SendParcel;
use App\Repositories\SendParcelRepository;
use Illuminate\Http\Request;

class BookingController extends Controller
{
    protected $sendParcelRepository;
    public function __construct(SendParcelRepository $sendParcelRepository)
    {
        $this->sendParcelRepository = $sendParcelRepository;
    }
    public function getBookingsData()
    {
        $bookings = SendParcel::whereNotNull('payment_method')->with('user', 'rider', 'acceptedBid')->latest()->get();
        $totalBookings = count($bookings);
        $activeBookings = SendParcel::where('status', 'in_transit')->whereNotNull('payment_method')->with('user', 'rider', 'acceptedBid')->latest()->get();
        $completedBookings = SendParcel::where('status', 'delivered')->whereNotNull('payment_method')->with('user', 'rider', 'acceptedBid')->latest()->get();
        $cancelledBookings = SendParcel::where('status', 'cancelled')->whereNotNull('payment_method')->with('user', 'rider', 'acceptedBid')->latest()->get();
        $data = [
            'totalBookings' => $totalBookings,
            'activeBookings' => $activeBookings,
            'completedBookings' => $completedBookings,
            'cancelledBookings' => $cancelledBookings
        ];
        return ResponseHelper::success($data, "Bookings data retrieved successfully");
    }
    
}
