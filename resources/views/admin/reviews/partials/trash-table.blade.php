<div class="card-body p-0">
    <div class="table-responsive">
        <table class="table table-hover table-bordered mgmt-table align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th class="ps-3" style="width:60px;">ID</th>
                    <th style="width:50px;">Ảnh</th>
                    <th style="width:200px;">Sản phẩm</th>
                    <th style="width:140px;">Khách hàng</th>
                    <th style="width:100px;">Đánh giá</th>
                    <th>Nhận xét</th>
                    <th style="width:130px;">Đã xóa lúc</th>
                    <th class="text-center" style="width:130px;">Thao tác</th>
                </tr>
            </thead>
            <tbody>
                @forelse($reviews as $review)
                    <tr>
                        <td class="ps-3" style="opacity:.45;">{{ $review->id }}</td>
                        <td>
                            @if($review->product?->thumbnail)
                                <img src="{{ asset($review->product->thumbnail) }}" class="product-thumb" alt="">
                            @else
                                <div style="width:36px;height:36px;background:#f3f4f6;border-radius:3px;border:1px solid #e5e7eb;"></div>
                            @endif
                        </td>
                        <td class="text-muted" style="font-size:12px;">{{ $review->product?->name ?? 'SP đã bị xóa' }}</td>
                        <td class="text-muted" style="font-size:12px;">{{ $review->user?->username ?? '-' }}</td>
                        <td>
                            <div class="stars">
                                @for($i = 1; $i <= 5; $i++)
                                    <i class="{{ $i <= $review->rating ? 'fa-solid' : 'fa-regular empty' }} fa-star"></i>
                                @endfor
                            </div>
                        </td>
                        <td style="font-size:12px;">{{ \Illuminate\Support\Str::limit($review->comment, 60) ?? '-' }}</td>
                        <td><span class="deleted-at">{{ $review->deleted_at?->format('d/m/Y H:i') }}</span></td>
                        <td class="text-center">
                            <div class="d-flex align-items-center justify-content-center gap-1">
                                <form method="POST" action="{{ route('admin.reviews.restore', $review->id) }}">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="rv-row-action-btn text-success" title="Khôi phục">
                                        <i class="fa-solid fa-rotate-left"></i>
                                    </button>
                                </form>
                                <button type="button" class="rv-row-action-btn text-danger" title="Xóa vĩnh viễn"
                                    data-delete-url="{{ route('admin.reviews.forceDelete', $review->id) }}"
                                    data-delete-name="{{ $review->user?->username ?? 'đánh giá này' }}"
                                    data-delete-type="đánh giá (vĩnh viễn)">
                                    <i class="fa-regular fa-trash-can"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="text-center py-5">
                            <i class="fa-solid fa-trash text-muted mb-3" style="font-size:42px;display:block;"></i>
                            <div class="fw-semibold text-muted">Thùng rác trống</div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="card-footer bg-white">
    @include('layouts.components.pagination', ['paginator' => $reviews, 'itemLabel' => 'đánh giá'])
</div>
