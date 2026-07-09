{{--
    Partial: partials.size-chart

    Hiển thị bảng số đo tham khảo theo Size (S/M/L/XL).
    - Nếu sản phẩm dành cho Nữ (gender = women)  -> chỉ hiện bảng Nữ.
    - Nếu sản phẩm dành cho Nam (gender = men)    -> chỉ hiện bảng Nam.
    - Nếu sản phẩm Unisex (gender = unisex)       -> hiện 2 tab để người dùng
      tự chuyển đổi qua lại giữa bảng số đo Nam và Nữ.

    Biến đầu vào:
    - $gender: chuỗi 'men' | 'women' | 'unisex', lấy từ $product->gender ở nơi gọi.
--}}

@php
    // Số liệu mẫu tạm thời (placeholder), có thể chỉnh sửa lại cho khớp
    // với số đo thực tế của HK STORE sau này.

    // Bảng Nữ: Vòng ngực / Vòng eo / Vòng mông (đơn vị: cm)
    $womenSizeChart = [
        ['size' => 'S',  'bust' => 82, 'waist' => 66, 'hip' => 86],
        ['size' => 'M',  'bust' => 86, 'waist' => 70, 'hip' => 90],
        ['size' => 'L',  'bust' => 90, 'waist' => 74, 'hip' => 94],
        ['size' => 'XL', 'bust' => 94, 'waist' => 78, 'hip' => 98],
    ];

    // Bảng Nam: Vòng ngực / Vòng eo / Chiều cao gợi ý (đơn vị: cm)
    $menSizeChart = [
        ['size' => 'S',  'chest' => 88,  'waist' => 72, 'height' => '160 - 165'],
        ['size' => 'M',  'chest' => 92,  'waist' => 76, 'height' => '165 - 170'],
        ['size' => 'L',  'chest' => 96,  'waist' => 80, 'height' => '170 - 175'],
        ['size' => 'XL', 'chest' => 100, 'waist' => 84, 'height' => '175 - 180'],
    ];
@endphp

<div class="size-chart-wrapper">
    @if($gender === 'unisex')
        {{-- Sản phẩm Unisex: hiện 2 tab để người dùng tự chuyển đổi Nam / Nữ --}}
        <ul class="nav nav-tabs size-chart-tabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#sizeChartWomen" type="button" role="tab">
                    Nữ
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" data-bs-toggle="tab" data-bs-target="#sizeChartMen" type="button" role="tab">
                    Nam
                </button>
            </li>
        </ul>

        <div class="tab-content pt-3">
            <div class="tab-pane fade show active" id="sizeChartWomen" role="tabpanel">
                @include('partials.size-chart-table', ['type' => 'women', 'rows' => $womenSizeChart])
            </div>
            <div class="tab-pane fade" id="sizeChartMen" role="tabpanel">
                @include('partials.size-chart-table', ['type' => 'men', 'rows' => $menSizeChart])
            </div>
        </div>
    @elseif($gender === 'men')
        {{-- Sản phẩm dành riêng cho Nam --}}
        @include('partials.size-chart-table', ['type' => 'men', 'rows' => $menSizeChart])
    @else
        {{-- Mặc định (women hoặc không xác định) -> hiện bảng Nữ --}}
        @include('partials.size-chart-table', ['type' => 'women', 'rows' => $womenSizeChart])
    @endif
</div>
