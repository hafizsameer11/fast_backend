<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AppBanner;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class AppBannerController extends Controller
{
    public function index()
    {
        return response()->json(AppBanner::latest()->get());
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'location' => 'required|string',
            'subject' => 'required|string',
            'image' => 'required|image',
        ]);

        if ($request->hasFile('image')) {
            $validated['image'] = $request->file('image')->store('banners', 'public');
        }

        $banner = AppBanner::create($validated);
        return response()->json(['message' => 'Banner created', 'data' => $banner]);
    }

    public function update(Request $request, $id)
    {
        $banner = AppBanner::findOrFail($id);

        $validated = $request->validate([
            'location' => 'required|string',
            'subject' => 'required|string',
            'image' => 'nullable',
        ]);

        if ($request->hasFile('image')) {
            $request->validate([
                'image' => 'image|max:2048'
            ]);

            if ($banner->image) {
                Storage::disk('public')->delete($banner->image);
            }

            $validated['image'] = $request->file('image')->store('banners', 'public');
        } else {
            $validated['image'] = $banner->image;
        }

        $banner->update($validated);
        return response()->json(['message' => 'Banner updated', 'data' => $banner]);
    }

    public function destroy($id)
    {
        $banner = AppBanner::findOrFail($id);

        if ($banner->image) {
            Storage::disk('public')->delete($banner->image);
        }

        $banner->delete();
        return response()->json(['message' => 'Banner deleted']);
    }
}
