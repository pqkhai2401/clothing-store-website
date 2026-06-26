@php
    $routeName = request()->route()?->getName() ?? '';

    $pageGroups = [
        'admin.products'   => 'Sản phẩm',
        'admin.categories' => 'Danh mục',
        'admin.brands'     => 'Thương hiệu',
        'admin.colors'     => 'Màu sắc',
        'admin.sizes'      => 'Kích thước',
        'admin.orders'     => 'Đơn hàng',
        'admin.reviews'    => 'Đánh giá',
        'admin.customers'  => 'Quản lý khách hàng',
        'admin.staff'      => 'Quản lý nhân sự',
    ];

    $pageLabel = null;
    foreach ($pageGroups as $prefix => $label) {
        if ($routeName === $prefix || str_starts_with($routeName, $prefix . '.')) {
            $pageLabel = $label;
            break;
        }
    }
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
                <span class="hk-topbar-page">{{ $pageLabel }}</span>
            @endif
        </div>

        <button type="button" class="btn btn-link hk-topbar-bell p-0" title="Thông báo">
            <i class="fa-regular fa-bell"></i>
        </button>
    </div>
</nav>
