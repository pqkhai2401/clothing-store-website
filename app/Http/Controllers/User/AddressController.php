<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Address;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AddressController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $addresses = $request->user()
            ->addresses()
            ->orderByDesc('is_default')
            ->orderByDesc('id')
            ->get(['id', 'city', 'district', 'ward', 'apartment_number', 'is_default']);

        return response()->json($addresses);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'apartment_number' => ['required', 'string', 'max:255'],
            'ward'             => ['required', 'string', 'max:255'],
            'district'         => ['nullable', 'string', 'max:255'],
            'city'             => ['required', 'string', 'max:255'],
            'is_default'       => ['boolean'],
        ], [
            'apartment_number.required' => 'Vui lòng nhập địa chỉ cụ thể.',
            'ward.required'             => 'Vui lòng chọn phường/xã.',
            'city.required'             => 'Vui lòng chọn tỉnh/thành phố.',
        ]);

        $user = $request->user();

        if (! empty($validated['is_default'])) {
            $user->addresses()->where('is_default', true)->update(['is_default' => false]);
        }

        $address = $user->addresses()->create($validated);

        return response()->json([
            'address' => $address->only(['id', 'city', 'district', 'ward', 'apartment_number', 'is_default']),
            'message' => 'Đã lưu địa chỉ.',
        ], 201);
    }

    public function destroy(Request $request, Address $address): JsonResponse
    {
        abort_unless($address->user_id === $request->user()->id, 403);

        $address->delete();

        return response()->json(['message' => 'Đã xóa địa chỉ.']);
    }
}