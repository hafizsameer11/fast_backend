<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AppNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class AppNotificationController extends Controller
{
    public function index()
    {
        $notifications = AppNotification::latest()->get();
        return response()->json([
            'notifications' => $notifications,
            'message' => 'Notifications retrieved successfully.'
        ], 200);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string',
            'content' => 'nullable|string',
            'location' => 'nullable|string',
            'image' => 'nullable|image|max:2048'
        ]);

        if ($request->hasFile('image')) {
            $validated['image'] = $request->file('image')->store('notifications', 'public');
        }

        $notification = AppNotification::create($validated);
        return response()->json([
            'notification' => $notification,
            'message' => 'Notification created successfully.'
        ], 201);
    }
    public function update(Request $request, $id)
    {
        $notification = AppNotification::findOrFail($id);

        $validated = $request->validate([
            'title' => 'required|string',
            'content' => 'nullable|string',
            'location' => 'nullable|string',
            'image' => 'nullable', // not validating as file here
        ]);

        if ($request->hasFile('image')) {
            // Validate as file here only if it's actually uploaded
            $request->validate([
                'image' => 'image|max:2048'
            ]);

            // Delete old image if exists
            if ($notification->image) {
                Storage::disk('public')->delete($notification->image);
            }

            // Store new image
            $validated['image'] = $request->file('image')->store('notifications', 'public');
        } else {
            // Keep existing image if not updated
            $validated['image'] = $notification->image;
        }

        $notification->update($validated);
        return response()->json([
            'notification' => $notification,
            'message' => 'Notification updated successfully.'
        ], 200);
    }


    public function destroy($id)
    {
        $notification = AppNotification::findOrFail($id);

        if ($notification->image) {
            Storage::disk('public')->delete($notification->image);
        }

        $notification->delete();

        return response()->json(['message' => 'Notification deleted successfully.']);
    }
}
