<?php

namespace App\Models;

use App\Enums\UserRole;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;
use Laravel\Passport\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable implements \OwenIt\Auditing\Contracts\Auditable
{
    use HasApiTokens, HasFactory, HasRoles, Notifiable, SoftDeletes;
    use \OwenIt\Auditing\Auditable;

    /**
     * Không ghi các trường nhạy cảm vào nhật ký hoạt động.
     *
     * @var array<int, string>
     */
    protected $auditExclude = [
        'password',
        'remember_token',
        'google_id',
    ];

    protected $fillable = [
        'username',
        'gender',
        'email',
        'password',
        'google_id',
        'avatar_url',
        'phone_number',
        'is_active',
        'is_protected',
        'must_change_password',
        'lock_reason',
        'locked_by',
        'locked_at',
        'email_verified_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'is_active' => 'boolean',
        'is_protected' => 'boolean',
        'must_change_password' => 'boolean',
        'locked_at' => 'datetime',
    ];

    /**
     * Check if the user has the Admin role.
     */
    public function isAdmin(): bool
    {
        return $this->hasRole(UserRole::ADMIN->value);
    }

    /**
     * Check if the user has the Customer role.
     */
    public function isCustomer(): bool
    {
        return $this->hasRole(UserRole::CUSTOMER->value);
    }

    /**
     * Check if the user has the Staff role.
     */
    public function isStaff(): bool
    {
        return $this->hasRole(UserRole::STAFF->value);
    }

    /**
     * URL ảnh đại diện sẵn sàng để hiển thị.
     *
     * avatar_url có thể là đường dẫn tương đối trong disk "public" (ảnh do người dùng
     * tự upload, ví dụ: avatars/xxx.jpg) hoặc một URL tuyệt đối từ Google OAuth
     * (ví dụ: https://lh3.googleusercontent.com/...). Hai trường hợp này cần xử lý
     * khác nhau, nếu không sẽ tạo ra đường dẫn sai dạng "/storage/https://...".
     * *
     * URL ảnh đại diện để hiển thị. Xử lý được cả 3 trường hợp:
     * - avatar_url là URL đầy đủ (vd ảnh Google) → dùng nguyên.
     * - avatar_url là đường dẫn tương đối trong disk "public" (ảnh tự upload).
     * - chưa có ảnh → sinh ảnh chữ cái đầu từ ui-avatars theo tên.
     
     */
    public function getAvatarDisplayUrlAttribute(): ?string
    {
        if (! $this->avatar_url) {
            return null;
        }

        if (Str::startsWith($this->avatar_url, ['http://', 'https://'])) {
            return $this->avatar_url;
        }

        return asset('storage/'.$this->avatar_url);
    }

    /**
     * Find the user instance for Passport password grant authentication.
     */
    public function findForPassport(string $username): ?self
    {
        return $this->where('username', $username)->first();
    }

    /**
     * Get the addresses for the user.
     */
    public function addresses(): HasMany
    {
        return $this->hasMany(Address::class);
    }

    /**
     * Get the orders for the user.
     */
    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    /**
     * Get the reviews for the user.
     */
    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }

    /**
     * Get the wishlists for the user.
     */
    public function wishlists(): HasMany
    {
        return $this->hasMany(Wishlist::class);
    }

    /**
     * Get the cart associated with the user.
     */
    public function cart(): HasOne
    {
        return $this->hasOne(Cart::class);
    }

    /**
     * Get the product views (browsing history) for the user.
     */
    public function productViews(): HasMany
    {
        return $this->hasMany(ProductView::class);
    }

    /**
     * Get the search history for the user.
     */
    public function searchHistories(): HasMany
    {
        return $this->hasMany(SearchHistory::class);
    }
}