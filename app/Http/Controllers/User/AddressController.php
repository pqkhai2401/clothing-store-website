<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Address;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AddressController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $addresses = $request->user()
            ->addresses()
            ->orderByDesc('is_default')
            ->orderByDesc('id')
            ->get(['id', 'city', 'ward', 'apartment_number', 'is_default']);

        return response()->json($addresses);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'apartment_number' => ['required', 'string', 'max:255'],
            'ward'             => ['required', 'string', 'max:255'],
            'city'             => ['required', 'string', 'max:255'],
            'is_default'       => ['boolean'],
        ], [
            'apartment_number.required' => 'Vui lòng nhập địa chỉ cụ thể.',
            'ward.required'             => 'Vui lòng chọn phường/xã.',
            'city.required'             => 'Vui lòng chọn tỉnh/thành phố.',
        ]);

        $user = $request->user();

        $address = DB::transaction(function () use ($user, $validated) {
            $makeDefault = ! empty($validated['is_default']);

            if ($makeDefault) {
                $this->unsetOtherDefaults($user->id);
            }

            return $user->addresses()->create($validated);
        });

        return response()->json([
            'address' => $address->only(['id', 'city', 'ward', 'apartment_number', 'is_default']),
            'message' => 'Đã lưu địa chỉ.',
        ], 201);
    }

    public function update(Request $request, Address $address): JsonResponse
    {
        abort_unless($address->user_id === $request->user()->id, 403);

        $validated = $request->validate([
            'apartment_number' => ['required', 'string', 'max:255'],
            'ward'             => ['required', 'string', 'max:255'],
            'city'             => ['required', 'string', 'max:255'],
            'is_default'       => ['boolean'],
        ], [
            'apartment_number.required' => 'Vui lòng nhập địa chỉ cụ thể.',
            'ward.required'             => 'Vui lòng chọn phường/xã.',
            'city.required'             => 'Vui lòng chọn tỉnh/thành phố.',
        ]);

        DB::transaction(function () use ($address, $validated) {
            if (! empty($validated['is_default'])) {
                $this->unsetOtherDefaults($address->user_id, $address->id);
            }

            $address->update($validated);
        });

        return response()->json([
            'address' => $address->fresh()->only(['id', 'city', 'ward', 'apartment_number', 'is_default']),
            'message' => 'Đã cập nhật địa chỉ.',
        ]);
    }

    public function destroy(Request $request, Address $address): JsonResponse
    {
        abort_unless($address->user_id === $request->user()->id, 403);

        $address->delete();

        return response()->json(['message' => 'Đã xóa địa chỉ.']);
    }

    /**
     * Bỏ cờ mặc định ở các địa chỉ khác của user (trong transaction + khóa dòng để chống
     * race-condition 2 request cùng set default -> user có 2 địa chỉ mặc định).
     */
    private function unsetOtherDefaults(int $userId, ?int $exceptId = null): void
    {
        Address::where('user_id', $userId)
            ->where('is_default', true)
            ->when($exceptId !== null, fn ($q) => $q->where('id', '!=', $exceptId))
            ->lockForUpdate()
            ->get()
            ->each(fn (Address $a) => $a->update(['is_default' => false]));
    }
}
