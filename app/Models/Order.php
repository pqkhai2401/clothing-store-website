<?php

namespace App\Models;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
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
        'source',
        'created_by',
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
        'refund_amount',
        'refunded_at',
        'completed_at',
    ];

    protected $casts = [
        'total_money' => 'decimal:2',
        'shipping_fee' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'refund_amount' => 'decimal:2',
        'payos_payload' => 'array',
        'momo_payload' => 'array',
        'completed_at' => 'datetime',
        'refunded_at' => 'datetime',
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
     * Các yêu cầu hủy đơn do khách gửi (đơn đã xác nhận/đang giao thì không tự hủy được).
     */
    public function cancelRequests(): HasMany
    {
        return $this->hasMany(OrderCancelRequest::class);
    }

    /** Yêu cầu hủy đang chờ admin duyệt (nếu có) — mỗi đơn chỉ có tối đa 1. */
    public function pendingCancelRequest(): HasOne
    {
        return $this->hasOne(OrderCancelRequest::class)
            ->where('status', OrderCancelRequest::STATUS_PENDING)
            ->latestOfMany();
    }

    /**
     * Get the voucher applied to this order.
     */
    public function voucher(): BelongsTo
    {
        return $this->belongsTo(Voucher::class);
    }

    /**
     * Nhân sự đã tạo đơn này (chỉ có giá trị với đơn source = admin; đơn khách tự đặt thì null).
     */
    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
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

    /**
     * Loại các đơn cổng online (PayOS/MoMo) còn 'pending' + 'chưa thanh toán' khỏi kết quả.
     * Đây là đơn "đang chờ thanh toán" — trạng thái sống ở giỏ hàng, chưa phải đơn đã chốt,
     * nên không hiện ở danh sách đơn của user lẫn danh sách xử lý của admin. Đơn online đã
     * thanh toán, hoặc COD/chuyển khoản, vẫn hiển thị bình thường.
     */
    public function scopeExcludingUnpaidOnline(Builder $query): Builder
    {
        $onlineIds = PaymentMethod::onlineGatewayIds();

        if (empty($onlineIds)) {
            return $query;
        }

        // NOT(online AND pending AND unpaid) = (không phải online) HOẶC (đã xử lý) HOẶC (đã trả).
        return $query->where(function (Builder $q) use ($onlineIds) {
            $q->whereNotIn('payment_method_id', $onlineIds)
                ->orWhere('status', '!=', OrderStatus::PENDING->value)
                ->orWhere('payment_status', '!=', PaymentStatus::UNPAID->value);
        });
    }

    /**
     * Xóa khỏi giỏ hàng các biến thể đã mua trong đơn này — gọi khi thanh toán online
     * thành công. Chỉ xóa đúng biến thể của đơn (khớp product_variant_id) để không mất
     * các sản phẩm người dùng lỡ thêm mới vào giỏ sau khi đặt đơn.
     */
    public function clearPurchasedItemsFromCart(): void
    {
        $cart = $this->user?->cart;

        if (! $cart) {
            return;
        }

        $variantIds = $this->orderItems()->pluck('product_variant_id');

        $cart->cartItems()->whereIn('product_variant_id', $variantIds)->delete();
    }
}
