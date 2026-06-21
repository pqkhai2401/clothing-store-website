<?php

namespace App\Models;

use App\Enums\UserRole;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Passport\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes;

    protected $fillable = [
        'username',
        'display_name',
        'email',
        'password',
        'google_id',
        'avatar_url',
        'phone_number',
        'role_id',
        'is_active',
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
    ];

    /**
     * Check if the user has the Admin role.
     */
    public function isAdmin(): bool
    {
        return $this->role?->name === UserRole::ADMIN->value;
    }

    /**
     * Check if the user has the Customer role.
     */
    public function isCustomer(): bool
    {
        return $this->role?->name === UserRole::CUSTOMER->value;
    }

    /**
     * Check if the user has the Staff role.
     */
    public function isStaff(): bool
    {
        return $this->role?->name === UserRole::STAFF->value;
    }

    /**
     * Find the user instance for Passport password grant authentication.
     */
    public function findForPassport(string $username): ?self
    {
        return $this->where('username', $username)->first();
    }

    /**
     * Get the role associated with the user.
     */
    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
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
