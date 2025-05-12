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
        $activeBookings = SendParcel::where('status', 'in_transit')->whereNotNull('payment_method')->with('user', 'rider', 'acceptedBid')->latest()->count();
        $completedBookings = SendParcel::where('status', 'delivered')->whereNotNull('payment_method')->with('user', 'rider', 'acceptedBid')->latest()->count();
        $cancelledBookings = SendParcel::where('status', 'cancelled')->whereNotNull('payment_method')->with('user', 'rider', 'acceptedBid')->latest()->count();
        $data = [
            'totalBookings' => $totalBookings,
            'activeBookings' => $activeBookings,
            'completedBookings' => $completedBookings,
            'cancelledBookings' => $cancelledBookings,
            'bookings' => $bookings,
        ];
        return ResponseHelper::success($data, "Bookings data retrieved successfully");
    }
    
}
