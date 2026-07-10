<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Order extends Model implements \OwenIt\Auditing\Contracts\Auditable
{
    use HasFactory, SoftDeletes;
    use \OwenIt\Auditing\Auditable;

    protected $fillable = [
        'user_id',
        'address_id',
        'payment_method_id',
        'voucher_id',
        'order_code',
        'payos_order_code',
        'payos_payload',
        'momo_order_id',
        'momo_payload',
        'phone',
        'note',
        'total_money',
        'shipping_fee',
        'discount_amount',
        'status',
        'payment_status',
    ];

    protected $casts = [
        'total_money' => 'decimal:2',
        'shipping_fee' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'payos_payload' => 'array',
        'momo_payload' => 'array',
    ];

    /**
     * Get the user that placed the order.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the shipping address for the order.
     */
    public function address(): BelongsTo
    {
        return $this->belongsTo(Address::class);
    }

    /**
     * Get the payment method used for the order.
     */
    public function paymentMethod(): BelongsTo
    {
        return $this->belongsTo(PaymentMethod::class);
    }

    /**
     * Get the items in this order.
     */
    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    /**
     * Get the voucher applied to this order.
     */
    public function voucher(): BelongsTo
    {
        return $this->belongsTo(Voucher::class);
    }

    /**
     * URL để tiếp tục thanh toán (quay lại trang QR) theo đúng cổng của đơn.
     * Trả null nếu không phải cổng online (COD/chuyển khoản).
     */
    public function paymentResumeUrl(): ?string
    {
        $method = $this->paymentMethod;

        if (! $method) {
            return null;
        }

        if ($method->isPayos()) {
            return route('checkout.payos.show', $this->id);
        }

        if ($method->isMomo()) {
            return route('checkout.momo.show', $this->id);
        }

        return null;
    }
}
