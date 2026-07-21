<div class="product-table-wrap">
    <div class="table-responsive">
        <table class="table table-hover product-table align-middle" id="reviewTable">
            <thead>
                <tr>
                    <th style="width: 44px;">
                        <input type="checkbox" class="form-check-input product-check hk-cb-all">
                    </th>
                    <th class="ps-3" style="width:60px;">ID</th>
                    <th style="width:60px;">Ảnh</th>
                    <th style="width:200px;">Sản phẩm</th>
                    <th style="width:150px;">Khách hàng</th>
                    <th style="width:110px;">Đánh giá</th>
                    <th>Nhận xét</th>
                    <th style="width:150px;">Trạng thái</th>
                    <th style="width:110px;">Ngày</th>
                    <th class="text-center" style="width:120px;">Thao tác</th>
                </tr>
            </thead>
            <tbody>
                @forelse($reviews as $review)
                    <tr>
                        <td>
                            <input type="checkbox" class="form-check-input product-check hk-cb-row" value="{{ $review->id }}">
                        </td>
                        <td class="ps-3" style="opacity:.45;">{{ $review->id }}</td>
                        <td>
                            @if($review->product?->thumbnail)
                                <img src="{{ asset($review->product->thumbnail) }}"
                                    class="product-thumb" alt="">
                            @else
                                <div style="width:40px;height:40px;background:#f3f4f6;border-radius:4px;border:1px solid #e5e7eb;display:flex;align-items:center;justify-content:center;">
                                    <i class="fa-regular fa-image text-muted" style="font-size:12px;"></i>
                                </div>
                            @endif
                        </td>
                        <td>
                            <div class="fw-semibold" style="font-size:12px; line-height:1.4;">
                                {{ $review->product?->name ?? 'SP đã bị xóa' }}
                            </div>
                        </td>
                        <td>
                            <div class="fw-semibold" style="font-size:12px;">{{ $review->user?->username ?? '—' }}</div>
                            @if($review->user?->email)
                                <div class="text-muted" style="font-size:11px;">{{ $review->user->email }}</div>
                            @endif
                        </td>
                        <td>
                            <div class="stars">
                                @for($i = 1; $i <= 5; $i++)
                                    @if($i <= $review->rating)
                                        <i class="fa-solid fa-star"></i>
                                    @else
                                        <i class="fa-regular fa-star empty"></i>
                                    @endif
                                @endfor
                            </div>
                            <div class="text-muted" style="font-size:11px;">{{ $review->rating }}/5</div>
                        </td>
                        <td>
                            <span style="font-size:12px;">
                                {{ $review->comment ? \Illuminate\Support\Str::limit($review->comment, 80) : '—' }}
                            </span>
                        </td>
                        <td>
                            {{-- Badge trạng thái kiểm duyệt --}}
                            @php
                                $statusMeta = [
                                    'pending'  => ['Chờ duyệt',  'rv-badge-pending'],
                                    'approved' => ['Đã duyệt',   'rv-badge-approved'],
                                    'rejected' => ['Từ chối',    'rv-badge-rejected'],
                                    'flagged'  => ['Chờ Admin',  'rv-badge-flagged'],
                                ];
                                [$statusLabel, $statusClass] = $statusMeta[$review->status] ?? ['—', 'rv-badge-pending'];
                            @endphp
                            <span class="rv-badge {{ $statusClass }}">{{ $statusLabel }}</span>

                            {{-- Điểm tin cậy AI + lý do (hiện tooltip khi rê chuột) --}}
                            @if(!is_null($review->ai_score) || $review->ai_reason)
                                <div class="rv-ai-info" title="{{ $review->ai_reason }}">
                                    <i class="fa-solid fa-robot"></i>
                                    AI: {{ $review->ai_score ?? 0 }}%
                                </div>
                            @endif
                        </td>
                        <td class="text-muted" style="font-size:12px;">
                            {{ $review->created_at?->format('d/m/Y') ?? '—' }}
                        </td>
                        <td class="text-center">
                            <div class="d-flex align-items-center justify-content-center gap-1">
                                {{-- Nút DUYỆT: hiện khi review chưa ở trạng thái approved --}}
                                @if($review->status !== 'approved')
                                    <form action="{{ route('admin.reviews.approve', $review->id) }}" method="POST"
                                          onsubmit="event.preventDefault(); window.showConfirm({title: 'Xác nhận', message: 'Duyệt và hiển thị công khai đánh giá này?', type: 'info'}).then(ok => { if(ok) this.submit(); });" class="d-inline">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="rv-row-action-btn text-success" title="Duyệt">
                                            <i class="fa-solid fa-check"></i>
                                        </button>
                                    </form>
                                @endif

                                {{-- Nút TỪ CHỐI: hiện khi review chưa ở trạng thái rejected --}}
                                @if($review->status !== 'rejected')
                                    <form action="{{ route('admin.reviews.reject', $review->id) }}" method="POST"
                                          onsubmit="event.preventDefault(); window.showConfirm({title: 'Xác nhận', message: 'Từ chối và ẩn đánh giá này khỏi website?', type: 'warning'}).then(ok => { if(ok) this.submit(); });" class="d-inline">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="rv-row-action-btn text-warning" title="Từ chối">
                                            <i class="fa-solid fa-ban"></i>
                                        </button>
                                    </form>
                                @endif

                                {{-- Nút XÓA (đưa vào thùng rác) --}}
                                <button type="button" class="rv-row-action-btn text-danger" title="Xóa"
                                    data-delete-url="{{ route('admin.reviews.destroy', $review->id) }}"
                                    data-delete-name="{{ $review->user?->username ?? 'đánh giá này' }}"
                                    data-delete-type="đánh giá">
                                    <i class="fa-regular fa-trash-can"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr data-empty-row>
                        <td colspan="10" class="text-center py-5">
                            <i class="fa-solid fa-inbox text-muted mb-3" style="font-size:42px;display:block;"></i>
                            <div class="fw-semibold text-muted">Chưa có đánh giá nào</div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="bg-white border border-top-0 rounded-bottom px-3 py-2">
    @include('layouts.components.pagination', ['paginator' => $reviews, 'itemLabel' => 'đánh giá', 'bulkDeleteUrl' => route('admin.reviews.bulkDelete')])
</div>
