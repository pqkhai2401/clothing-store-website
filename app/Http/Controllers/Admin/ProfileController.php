<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Address;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class ProfileController extends Controller
{
    public function show(): JsonResponse
    {
        $user    = auth()->user()->load('roles');
        $address = $user->addresses()->first();

        return response()->json([
            'username'         => $user->username,
            'email'            => $user->email,
            'phone_number'     => $user->phone_number ?? '',
            'role_label'       => $this->getRoleLabel($user),
            'status_label'     => $user->is_active ? 'Hoạt động' : 'Ngừng hoạt động',
            'city'             => $address?->city ?? '',
            'ward'             => $address?->ward ?? '',
            'apartment_number' => $address?->apartment_number ?? '',
            'can_edit_email'   => $user->isAdmin(),
        ]);
    }

    public function update(Request $request): JsonResponse
    {
        $user = auth()->user();

        $emailRules = $user->isAdmin()
            ? ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)->whereNull('deleted_at')]
            : ['prohibited'];

        $validated = $request->validate([
            'username'         => ['required', 'string', 'max:255'],
            'email'            => $emailRules,
            'phone_number'     => ['nullable', 'string', 'max:20',
                                   Rule::unique('users', 'phone_number')->ignore($user->id)->whereNull('deleted_at')],
            'city'             => ['nullable', 'string', 'max:255'],
            'ward'             => ['nullable', 'string', 'max:255'],
            'apartment_number' => ['nullable', 'string', 'max:255'],
            'current_password' => ['nullable', 'required_with:password', 'current_password'],
            'password'         => ['nullable', 'string', 'min:8', 'confirmed'],
        ], [
            'username.required'         => 'Vui lòng nhập họ và tên.',
            'email.required'            => 'Vui lòng nhập email.',
            'email.email'               => 'Email không hợp lệ.',
            'email.unique'              => 'Email này đã được sử dụng.',
            'phone_number.unique'       => 'Số điện thoại này đã được sử dụng.',
            'current_password.required_with' => 'Vui lòng nhập mật khẩu hiện tại.',
            'current_password.current_password' => 'Mật khẩu hiện tại không đúng.',
            'password.min'              => 'Mật khẩu mới phải có ít nhất 8 ký tự.',
            'password.confirmed'        => 'Xác nhận mật khẩu không khớp.',
        ]);

        $user->username     = $validated['username'];
        if ($user->isAdmin()) {
            $user->email = $validated['email'];
        }
        $user->phone_number = $validated['phone_number'] ?? null;

        if (!empty($validated['password'])) {
            $user->password = Hash::make($validated['password']);
            $user->must_change_password = false;
        }

        $user->save();

        $address          = $user->addresses()->first() ?? new Address();
        $address->user_id = $user->id;
        $address->city             = $validated['city'] ?? '';
        $address->ward             = $validated['ward'] ?? '';
        $address->apartment_number = $validated['apartment_number'] ?? '';
        $address->save();

        return response()->json([
            'success' => true,
            'message' => 'Cập nhật hồ sơ thành công.',
            'user'    => [
                'username'     => $user->username,
                'email'        => $user->email,
                'phone_number' => $user->phone_number,
                'role'         => $this->getRoleLabel($user),
                'status'       => $user->is_active ? 'Hoạt động' : 'Ngừng hoạt động',
            ],
        ]);
    }

    private function getRoleLabel($user): string
    {
        return match ($user->roles->first()?->name) {
            'admin' => 'Quản trị viên',
            'staff' => 'Nhân viên',
            default => 'Chưa có vai trò',
        };
    }
}
