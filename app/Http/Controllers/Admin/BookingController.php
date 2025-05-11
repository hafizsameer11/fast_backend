<?php

namespace App\Http\Controllers\Admin;

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
}
