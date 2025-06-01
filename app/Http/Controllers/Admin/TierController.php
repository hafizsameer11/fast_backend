<?php

// app/Http/Controllers/TierController.php
namespace App\Http\Controllers\Admin;

use App\Models\Tier;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class TierController extends Controller
{
    public function index()
    {
        return Tier::all();
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'tier' => 'required|integer',
            'no_of_rides' => 'required|integer',
            'commission' => 'required|numeric',
            'tier_amount' => 'required|numeric',
            'status' => 'required|in:active,inactive',
        ]);

        $tier = Tier::create($validated);
        return response()->json($tier, 201);
    }

    public function update(Request $request, Tier $tier)
    {
        $validated = $request->validate([
            'tier' => 'required|integer',
            'no_of_rides' => 'required|integer',
            'commission' => 'required|numeric',
            'tier_amount' => 'required|numeric',
            'status' => 'required|in:active,inactive',
        ]);

        $tier->update($validated);
        return response()->json($tier);
    }

    public function destroy(Tier $tier)
    {
        $tier->delete();
        return response()->json(['message' => 'Tier deleted']);
    }
}
