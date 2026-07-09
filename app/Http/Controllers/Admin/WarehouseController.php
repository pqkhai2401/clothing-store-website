<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Warehouse;
use Illuminate\Http\Request;

class WarehouseController extends Controller
{
    /**
     * Tạo mới kho hàng từ AJAX popup.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'       => ['required', 'string', 'max:120'],
            'city'       => ['nullable', 'string', 'max:120'],
            'is_default' => ['nullable', 'boolean'],
        ], [
            'name.required' => 'Vui lòng nhập tên kho.',
            'name.max'      => 'Tên kho không được vượt quá 120 ký tự.',
        ]);

        $isDefault = filter_var($request->input('is_default', false), FILTER_VALIDATE_BOOLEAN);

        // Nếu đặt làm mặc định → bỏ mặc định của kho cũ
        if ($isDefault) {
            Warehouse::where('is_default', true)->update(['is_default' => false]);
        }

        $warehouse = Warehouse::create([
            'name'       => $validated['name'],
            'city'       => $validated['city'] ?? null,
            'is_default' => $isDefault,
            'status'     => true,
        ]);

        return response()->json([
            'message'   => "Tạo kho \"{$warehouse->full_name}\" thành công.",
            'warehouse' => [
                'id'         => $warehouse->id,
                'name'       => $warehouse->name,
                'city'       => $warehouse->city,
                'full_name'  => $warehouse->full_name,
                'is_default' => $warehouse->is_default,
            ],
        ], 201);
    }
}
