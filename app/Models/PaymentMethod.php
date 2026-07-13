<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class PaymentMethod extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'status',
        'image',
    ];

    protected $casts = [
        'status' => 'boolean',
    ];

    /**
     * Get the orders that use this payment method.
     */
    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    /**
     * Phương thức này có phải PayOS (thanh toán online qua QR) hay không.
     */
    public function isPayos(): bool
    {
        return str_contains(strtolower($this->name ?? ''), 'payos');
    }

    /**
     * Phương thức này có phải MoMo (thanh toán online qua QR) hay không.
     */
    public function isMomo(): bool
    {
        return str_contains(strtolower($this->name ?? ''), 'momo');
    }

    /**
     * Có phải cổng thanh toán online (QR, thanh toán ngay) — PayOS hoặc MoMo.
     * Dùng cho: nút "Tiếp tục thanh toán", supersede đơn treo, tự hủy đơn quá hạn.
     */
    public function isOnlineGateway(): bool
    {
        return $this->isPayos() || $this->isMomo();
    }

    /**
     * ID các phương thức là cổng thanh toán online (PayOS/MoMo) — dùng cho các truy vấn cần
     * lọc theo cổng online (vd ẩn đơn online chưa thanh toán). Cache trong vòng đời request
     * để không truy vấn lặp lại.
     */
    public static function onlineGatewayIds(): array
    {
        static $ids = null;

        if ($ids === null) {
            $ids = static::all()->filter->isOnlineGateway()->pluck('id')->all();
        }

        return $ids;
    }
}
