<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Category extends Model implements \OwenIt\Auditing\Contracts\Auditable
{
    use HasFactory, SoftDeletes;
    use \OwenIt\Auditing\Auditable;

    protected $fillable = [
        'name',
        'slug',
        'parent_id',
        'outfit_type',
        'style_group',
        'status',
    ];

    /**
     * Với mỗi outfit_type, các outfit_type nào được phép gợi ý phối cùng.
     * Dùng cho khối "Phối cùng phong cách" (AiStylistService::getMixAndMatch).
     *
     * - top/bottom: phối chéo với nhau + áo khoác + phụ kiện (không gợi lại đồ cùng slot).
     * - skirt (váy): phối với áo (top), khác với dress vì váy không phải bộ đồ hoàn chỉnh.
     * - dress (đầm): đã là bộ đồ hoàn chỉnh -> chỉ gợi thêm áo khoác/phụ kiện.
     * - outerwear/accessory: có thể khoác/đeo cùng mọi loại còn lại.
     */
    public static function outfitCompatibilityMap(): array
    {
        return [
            'top'       => ['bottom', 'skirt', 'outerwear', 'accessory'],
            'bottom'    => ['top', 'outerwear', 'accessory'],
            'skirt'     => ['top', 'outerwear', 'accessory'],
            'dress'     => ['outerwear', 'accessory'],
            'outerwear' => ['top', 'bottom', 'skirt', 'dress', 'accessory'],
            'accessory' => ['top', 'bottom', 'skirt', 'dress', 'outerwear'],
        ];
    }

    /**
     * Các outfit_type tương thích để phối cùng với category hiện tại.
     * Trả về [] nếu category không có outfit_type (VD: danh mục cha).
     */
    public function compatibleOutfitTypes(): array
    {
        return self::outfitCompatibilityMap()[$this->outfit_type] ?? [];
    }

    /**
     * Với mỗi style_group, các style_group nào XUNG ĐỘT (không nên phối cùng) —
     * VD: áo sơ mi/quần tây "formal" hoặc "smart_casual" không nên ghép với quần
     * jogger/short "sporty" (đúng loại trang phục nhưng sai độ trang trọng).
     * 'neutral' (phụ kiện) và 'casual' không xung đột với ai — linh hoạt phối mọi kiểu.
     */
    public static function styleConflictMap(): array
    {
        return [
            'formal'       => ['sporty'],
            'smart_casual' => ['sporty'],
            'sporty'       => ['formal', 'smart_casual'],
            'casual'       => [],
            'neutral'      => [],
        ];
    }

    /**
     * Các style_group KHÔNG được chọn khi phối cùng category hiện tại.
     * Trả về [] nếu category không gắn style_group -> không áp dụng luật này.
     */
    public function conflictingStyleGroups(): array
    {
        return self::styleConflictMap()[$this->style_group] ?? [];
    }

    protected $casts = [
        'status' => 'boolean',
    ];

    /**
     * Get the products in this category.
     */
    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    /**
     * Get only active (đang bán) products in this category.
     */
    public function activeProducts(): HasMany
    {
        return $this->hasMany(Product::class)->where('status', true);
    }

    /**
     * Get the parent category.
     */
    public function parentCategory(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'parent_id');
    }

    /**
     * Get the children categories.
     */
    public function childrenCategories(): HasMany
    {
        return $this->hasMany(Category::class, 'parent_id');
    }
}
