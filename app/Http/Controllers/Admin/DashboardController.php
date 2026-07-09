<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        // ────────────────────────────────────────────────────────────────
        // DỮ LIỆU TĨNH (mockup) — sẽ thay bằng truy vấn thật ở bước sau.
        // ────────────────────────────────────────────────────────────────

        $stats = [
            'revenue'         => 1248500000,
            'revenueDelta'    => 12.5,
            'orders'          => 3842,
            'ordersDelta'     => 8,
            'pending'         => 48,
            'pendingDelta'    => -2.4,
            'sellingProducts' => 1205,
            'totalProducts'   => 1248,
            'sellingDelta'    => 1.2,
            'totalCustomers'  => 24590,
            'newCustomers'    => 542,
            'shipping'        => 156,
            'completed'       => 3420,
            'cancelled'       => 218,
            'hiddenProducts'  => 43,
            'lowStock'        => 12,
        ];

        // Biểu đồ doanh thu (kỳ này vs kỳ trước) — đơn vị: triệu đồng
        $revenueChart = [
            'labels'   => ['T2', 'T3', 'T4', 'T5', 'T6', 'T7', 'CN'],
            'current'  => [120, 190, 150, 280, 220, 310, 420],
            'previous' => [100, 160, 140, 210, 190, 280, 350],
        ];

        // Tỷ lệ trạng thái đơn hàng (donut)
        $statusRatio = [
            ['label' => 'Hoàn thành', 'value' => 240, 'color' => '#16A34A'],
            ['label' => 'Đang giao',  'value' => 156, 'color' => '#2563EB'],
            ['label' => 'Chờ xử lý',  'value' => 48,  'color' => '#D97706'],
            ['label' => 'Đã hủy',     'value' => 22,  'color' => '#DC2626'],
        ];

        // Đơn hàng mới nhất
        $recentOrders = [
            ['code' => '#HK-1209', 'name' => 'Nguyễn Văn An',  'phone' => '0901234567', 'total' => 1250000, 'status' => 'pending', 'status_label' => 'Chờ xử lý', 'payment' => 'COD',           'payment_class' => 'neutral', 'date' => '09/07/2026'],
            ['code' => '#HK-1208', 'name' => 'Lê Thị Bình',    'phone' => '0987654321', 'total' => 890000,  'status' => 'ship',    'status_label' => 'Đang giao', 'payment' => 'Đã thanh toán', 'payment_class' => 'done',    'date' => '09/07/2026'],
            ['code' => '#HK-1207', 'name' => 'Trần Minh Tâm',  'phone' => '0912345678', 'total' => 2100000, 'status' => 'done',    'status_label' => 'Hoàn thành', 'payment' => 'Đã thanh toán', 'payment_class' => 'done',    'date' => '08/07/2026'],
            ['code' => '#HK-1206', 'name' => 'Phạm Ngọc Lan',  'phone' => '0933445566', 'total' => 450000,  'status' => 'cancel',  'status_label' => 'Đã hủy',    'payment' => 'Đã hoàn tiền',  'payment_class' => 'neutral', 'date' => '08/07/2026'],
            ['code' => '#HK-1205', 'name' => 'Vũ Quốc Bảo',    'phone' => '0977001122', 'total' => 1740000, 'status' => 'done',    'status_label' => 'Hoàn thành', 'payment' => 'Đã thanh toán', 'payment_class' => 'done',    'date' => '08/07/2026'],
        ];

        // Top sản phẩm bán chạy
        $topProducts = [
            ['name' => 'Áo Polo Classic',    'cat' => 'Áo · Nam',      'sold' => 1240, 'revenue' => '434 tr ₫', 'tone' => 'green'],
            ['name' => 'Quần Jeans Slimfit', 'cat' => 'Quần · Nam',    'sold' => 950,  'revenue' => '760 tr ₫', 'tone' => 'blue'],
            ['name' => 'Đầm suông Linen',    'cat' => 'Đầm · Nữ',      'sold' => 712,  'revenue' => '413 tr ₫', 'tone' => 'purple'],
            ['name' => 'Áo khoác Bomber',    'cat' => 'Khoác · Unisex', 'sold' => 560,  'revenue' => '528 tr ₫', 'tone' => 'amber'],
        ];

        // Sản phẩm sắp hết hàng
        $lowStockProducts = [
            ['name' => 'Áo thun Basic',   'variant' => 'Trắng / L', 'stock' => 4],
            ['name' => 'Áo khoác Blazer', 'variant' => 'Đen / XL',  'stock' => 8],
            ['name' => 'Quần short Kaki',  'variant' => 'Be / M',    'stock' => 9],
            ['name' => 'Chân váy Xếp ly',  'variant' => 'Hồng / S',   'stock' => 10],
        ];

        // Đánh giá mới nhất
        $latestReviews = [
            ['user' => 'Hoàng M.', 'product' => 'Áo thun Basic',      'stars' => 5, 'text' => 'Vải mát, form chuẩn, sẽ ủng hộ shop tiếp.',   'time' => '10 phút trước'],
            ['user' => 'Ngọc L.',  'product' => 'Quần Jeans Slimfit',  'stars' => 4, 'text' => 'Đẹp nhưng hơi ôm so với mô tả một chút.',    'time' => '40 phút trước'],
            ['user' => 'Tú A.',    'product' => 'Đầm suông Linen',     'stars' => 5, 'text' => 'Chất liệu tốt, đóng gói cẩn thận, giao nhanh.', 'time' => '2 giờ trước'],
            ['user' => 'Bình P.',  'product' => 'Áo khoác Bomber',     'stars' => 2, 'text' => 'Màu thực tế khác ảnh khá nhiều, hơi tiếc.',   'time' => '4 giờ trước'],
        ];

        return view('admin.dashboard', compact(
            'stats',
            'revenueChart',
            'statusRatio',
            'recentOrders',
            'topProducts',
            'lowStockProducts',
            'latestReviews'
        ));
    }
}
