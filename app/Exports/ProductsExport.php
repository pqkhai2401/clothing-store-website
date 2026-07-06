<?php

namespace App\Exports;

use App\Http\Controllers\Admin\ProductController;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ProductsExport implements FromQuery, WithHeadings, WithMapping, WithStyles
{
    public function __construct(private ?Request $request = null)
    {
    }

    public function query()
    {
        $query = Product::query()
            ->with(['category', 'brand'])
            ->withSum('productVariants', 'stock');

        // Ưu tiên tuyệt đối: nếu có danh sách ID được chọn sẵn trên bảng (chọn nhiều bằng checkbox),
        // chỉ xuất đúng các sản phẩm đó, bỏ qua toàn bộ bộ lọc khác.
        $ids = $this->request?->input('ids');
        if (! empty($ids)) {
            $idList = array_values(array_filter(array_map('intval', explode(',', (string) $ids))));

            return $query->whereIn('id', $idList)->orderBy('id');
        }

        $search = trim((string) $this->request?->input('search'));
        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhereHas('category', fn ($categoryQuery) => $categoryQuery->where('name', 'like', "%{$search}%"))
                    ->orWhereHas('brand', fn ($brandQuery) => $brandQuery->where('name', 'like', "%{$search}%"));
            });
        }

        $parentCategoryId = $this->request?->input('parent_category_id');
        $categoryId = $this->request?->input('category_id');
        if ($parentCategoryId) {
            $childIds = Category::where('parent_id', $parentCategoryId)->pluck('id');
            $allIds = $childIds->push((int) $parentCategoryId)->unique()->values();
            $query->whereIn('category_id', $allIds);
        } elseif ($categoryId) {
            $query->where('category_id', $categoryId);
        }

        $brandId = $this->request?->input('brand_id');
        if ($brandId) {
            $query->where('brand_id', $brandId);
        }

        $sizeId = $this->request?->input('size_id');
        if ($sizeId) {
            $query->whereHas('productVariants', fn ($variantQuery) => $variantQuery->where('size_id', $sizeId));
        }

        $colorId = $this->request?->input('color_id');
        if ($colorId) {
            $query->whereHas('productVariants', fn ($variantQuery) => $variantQuery->where('color_id', $colorId));
        }

        $status = $this->request?->input('status');
        if (in_array($status, ['0', '1'], true)) {
            $query->where('status', (bool) (int) $status);
        }

        $stockStatus = $this->request?->input('stock_status');
        if ($stockStatus === 'low_stock') {
            $query->whereRaw(
                '(select coalesce(sum(product_variants.stock), 0) from product_variants where product_variants.product_id = products.id) < ?',
                [ProductController::LOW_STOCK_THRESHOLD]
            );
        }

        return $query->orderBy('id');
    }

    public function headings(): array
    {
        return [
            'ID',
            'Tên sản phẩm',
            'Danh mục',
            'Thương hiệu',
            'Giá bán',
            'Giảm giá (%)',
            'Tổng tồn kho',
            'Trạng thái',
        ];
    }

    public function map($product): array
    {
        return [
            $product->id,
            $product->name,
            $product->category?->name ?? '—',
            $product->brand?->name ?? '—',
            (float) $product->price,
            (int) $product->discount,
            (int) ($product->product_variants_sum_stock ?? 0),
            $product->status ? 'Đang bán' : 'Ẩn',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        $highestRow = $sheet->getHighestRow();
        $highestColumn = $sheet->getHighestColumn();
        $range = "A1:{$highestColumn}{$highestRow}";

        $sheet->getStyle($range)->applyFromArray([
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => '000000'],
                ],
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'color' => ['rgb' => 'FFFFFF'],
            ],
        ]);

        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
