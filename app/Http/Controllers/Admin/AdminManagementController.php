<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\AddUserRequest;
use App\Models\User;
use Illuminate\Http\Request;

class AdminManagementController extends Controller
{
    public function index()
    {
        $admin = User::whereNotIn('role', ['user', 'rider'])->get();
        $total = User::whereNotIn('role', ['user', 'rider'])->count();
        $active = User::whereNotIn('role', ['user', 'rider'])->where('is_active', 1)->count();
        $inactive = $total - $active;
        return response()->json([
            'admins' => $admin,
            'total' => $total,
            'active' => $active,
            'inactive' => $inactive
        ], 200);
    }
    public function addUser(AddUserRequest $request)
    {
        try {
            $validatedData = $request->validated();

            // Hash the password
            $validatedData['password'] = bcrypt($validatedData['password']);

            if ($request->hasFile('profile_picture')) {
                $file = $request->file('profile_picture');
                $filePath = $file->store('profile_pictures', 'public'); // Save to 'storage/app/public/profile_pictures'
                $validatedData['profile_picture'] = $filePath;
            }
            // add opt_verified
            $validatedData['otp_verified'] = 1;
            $validatedData['is_active'] = 1;

            // Create the user
            $user = User::create($validatedData);

            return ResponseHelper::success($user, 'User added successfully');
        } catch (\Throwable $th) {
            return ResponseHelper::error($th->getMessage());
        }
    }
    public function updateUser(Request $request, $id)
    {
        try {
            $user = User::findOrFail($id);

            $data = $request->only([
                'name',
                'email',
                'phone',
                'role',
                'is_active'
            ]);

            if ($request->filled('password')) {
                $data['password'] = bcrypt($request->input('password'));
            }

            if ($request->hasFile('profile_picture')) {
                $file = $request->file('profile_picture');
                $filePath = $file->store('profile_pictures', 'public');
                $data['profile_picture'] = $filePath;
            }

            $user->update($data);

            return ResponseHelper::success($user, 'User updated successfully');
        } catch (\Throwable $th) {
            return ResponseHelper::error($th->getMessage());
        }
    }

}
