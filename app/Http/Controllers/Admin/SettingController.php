<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    public function index()
    {
        return response()->json(Setting::all());
    }

    public function upsertMultiple(Request $request)
    {
        $request->validate([
            'settings' => 'required|array',
            'settings.*' => 'nullable|string',
        ]);

        $updated = [];

        foreach ($request->settings as $name => $value) {
            $setting = Setting::updateOrCreate(
                ['name' => $name],
                ['value' => $value]
            );
            $updated[] = $setting;
        }

        return response()->json([
            'message' => 'Settings updated successfully.',
            'data' => $updated,
        ]);
    }
}
