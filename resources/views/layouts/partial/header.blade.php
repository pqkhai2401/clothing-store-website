@php
    $routeName = request()->route()?->getName() ?? '';

    $pageGroups = [
        'admin.products'   => 'Sản phẩm',
        'admin.goods-receipts' => 'Kho hàng',
        'admin.categories' => 'Danh mục',
        'admin.suppliers'  => 'Nhà cung cấp',
        'admin.brands'     => 'Thương hiệu',
        'admin.colors'     => 'Màu sắc',
        'admin.sizes'      => 'Kích thước',
        'admin.orders'     => 'Đơn hàng',
        'admin.reviews'    => 'Đánh giá',
        'admin.customers'  => 'Quản lý khách hàng',
        'admin.staff'      => 'Quản lý nhân sự',
        'admin.vouchers'    => 'Khuyến mãi',
        'admin.logs'        => 'Nhật ký hệ thống',
        'admin.settings'    => 'Cài đặt hệ thống',
    ];

    $pageLabel = null;
    foreach ($pageGroups as $prefix => $label) {
        if ($routeName === $prefix || str_starts_with($routeName, $prefix . '.')) {
            $pageLabel = $label;
            break;
        }
    }

    $actionLabel = match ($routeName) {
        'admin.staff.create'      => 'Thêm mới quản trị viên',
        'admin.staff.edit'        => 'Chỉnh sửa quản trị viên',
        'admin.customers.create'  => 'Thêm mới khách hàng',
        'admin.customers.edit'    => 'Chỉnh sửa khách hàng',
        'admin.users.create'      => 'Thêm mới tài khoản',
        'admin.products.create'   => 'Thêm mới sản phẩm',
        'admin.vouchers.create'   => 'Thêm mới voucher',
        'admin.orders.create'     => 'Thêm mới đơn hàng',
        'admin.products.trash'    => 'Thùng rác sản phẩm',
        'admin.categories.trash'  => 'Thùng rác danh mục',
        'admin.brands.trash'      => 'Thùng rác thương hiệu',
        'admin.colors.trash'      => 'Thùng rác màu sắc',
        'admin.sizes.trash'       => 'Thùng rác kích thước',
        'admin.reviews.trash'     => 'Thùng rác đánh giá',
        'admin.goods-receipts.trash' => 'Thùng rác nhập kho',
        'admin.vouchers.trash'    => 'Thùng rác voucher',
        'admin.settings'          => 'Cài đặt hệ thống',
        default => null,
    };

    $pageUrl = match ($routeName) {
        'admin.staff.create'     => route('admin.staff.list'),
        'admin.staff.edit'       => route('admin.staff.list'),
        'admin.customers.create' => route('admin.customers.list'),
        'admin.customers.edit'   => route('admin.customers.list'),
        'admin.users.create'     => route('admin.users.list'),
        'admin.products.create'  => route('admin.products.list'),
        'admin.goods-receipts.trash' => route('admin.goods-receipts.list'),
        'admin.products.trash'   => route('admin.products.list'),
        'admin.categories.trash' => route('admin.categories.list'),
        'admin.brands.trash'     => route('admin.brands.list'),
        'admin.colors.trash'     => route('admin.colors.list'),
        'admin.sizes.trash'      => route('admin.sizes.list'),
        'admin.reviews.trash'    => route('admin.reviews.list'),
        'admin.orders.create'    => route('admin.orders.list'),
        'admin.vouchers.create'  => route('admin.vouchers.list'),
        'admin.vouchers.trash'   => route('admin.vouchers.list'),
        'admin.settings'         => route('admin.settings'),
        default => null,
    };
@endphp

<nav class="app-header hk-topbar">
    <div class="hk-topbar-inner">
        <div class="hk-topbar-breadcrumb">
            <button type="button"
                    class="btn btn-link hk-topbar-toggle p-0 me-3"
                    data-lte-toggle="sidebar"
                    title="Mở rộng/Thu gọn sidebar">
                <i class="fa-solid fa-bars"></i>
            </button>

            <a href="{{ route('admin.dashboard') }}" class="hk-topbar-link">Dashboard</a>

            @if($pageLabel)
                <i class="fa-solid fa-chevron-right hk-topbar-sep"></i>
                @if($pageUrl)
                    <a href="{{ $pageUrl }}" class="hk-topbar-link">{{ $pageLabel }}</a>
                @else
                    <span class="hk-topbar-page">{{ $pageLabel }}</span>
                @endif
            @endif

            @if($actionLabel)
                <i class="fa-solid fa-chevron-right hk-topbar-sep"></i>
                <span class="hk-topbar-page">{{ $actionLabel }}</span>
            @endif
        </div>

        <button type="button" class="btn btn-link hk-topbar-bell p-0" title="Thông báo">
            <i class="fa-regular fa-bell"></i>
        </button>
    </div>
</nav>
