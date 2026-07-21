<div class="modal fade" id="qcpModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="border-radius:12px;">
            <div class="modal-header">
                <h5 class="modal-title fw-bold" style="font-size:16px;">Thêm nhanh sản phẩm</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="form-text mb-3">
                    Sản phẩm sẽ được tạo ở trạng thái <strong>nháp</strong> (ẩn khỏi website). Vào trang
                    <strong>Quản lý sản phẩm</strong> để bổ sung ảnh, mô tả và công bố sau.
                </div>

                <div class="edit-field position-relative">
                    <label for="qcpName">Tên sản phẩm <span class="text-danger">*</span></label>
                    <input type="text" id="qcpName" class="form-control" placeholder="Ví dụ: Áo thun cotton trắng" autocomplete="off">
                    <div id="qcpSimilarBox" class="gr-picker-panel" style="position:absolute; display:none;"></div>
                </div>

                <div class="edit-field">
                    <label for="qcpCategoryTrigger">Danh mục <span class="text-danger">*</span></label>
                    <input type="hidden" id="qcpCategory" value="">
                    <div class="hk-cat-filter qcp-dropdown" id="qcpCategoryFilter">
                        <button type="button" class="hk-cat-trigger" id="qcpCategoryTrigger" aria-haspopup="listbox" aria-expanded="false">
                            <span class="hk-cat-trigger-label" id="qcpCategoryLabel">— Chọn danh mục —</span>
                            <i class="fa-solid fa-chevron-down hk-cat-arrow"></i>
                        </button>
                        <div class="hk-cat-panel" id="qcpCategoryPanel" hidden>
                            <div class="hk-cat-list" id="qcpCategoryList" role="listbox">
                                <button type="button" class="hk-cat-item is-active" data-value="" data-label="— Chọn danh mục —">— Chọn danh mục —</button>
                                @foreach($quickCreateCategories as $cat)
                                    <button type="button" class="hk-cat-item" data-value="{{ $cat->id }}" data-label="{{ $cat->name }}">{{ $cat->name }}</button>
                                    @foreach($cat->childrenCategories as $child)
                                        <button type="button" class="hk-cat-item" data-value="{{ $child->id }}" data-label="— {{ $child->name }}">— {{ $child->name }}</button>
                                    @endforeach
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>

                <div class="edit-field">
                    <label for="qcpBrandTrigger">Thương hiệu</label>
                    <input type="hidden" id="qcpBrand" value="">
                    <div class="hk-cat-filter qcp-dropdown" id="qcpBrandFilter">
                        <button type="button" class="hk-cat-trigger" id="qcpBrandTrigger" aria-haspopup="listbox" aria-expanded="false">
                            <span class="hk-cat-trigger-label" id="qcpBrandLabel">— Không chọn —</span>
                            <i class="fa-solid fa-chevron-down hk-cat-arrow"></i>
                        </button>
                        <div class="hk-cat-panel" id="qcpBrandPanel" hidden>
                            <div class="hk-cat-list" id="qcpBrandList" role="listbox">
                                <button type="button" class="hk-cat-item is-active" data-value="" data-label="— Không chọn —">— Không chọn —</button>
                                @foreach($quickCreateBrands as $brand)
                                    <button type="button" class="hk-cat-item" data-value="{{ $brand->id }}" data-label="{{ $brand->name }}">{{ $brand->name }}</button>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>

                <div class="edit-field">
                    <label class="d-block">Giới tính <span class="text-danger">*</span></label>
                    <div class="d-flex gap-3">
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="qcpGender" id="qcpGenderMen" value="men">
                            <label class="form-check-label" for="qcpGenderMen">Nam</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="qcpGender" id="qcpGenderWomen" value="women">
                            <label class="form-check-label" for="qcpGenderWomen">Nữ</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="qcpGender" id="qcpGenderUnisex" value="unisex" checked>
                            <label class="form-check-label" for="qcpGenderUnisex">Unisex</label>
                        </div>
                    </div>
                </div>

                <hr>

                <div class="row g-2">
                    <div class="col-6">
                        <div class="edit-field">
                            <label for="qcpColorTrigger">Màu sắc <span class="text-danger">*</span></label>
                            <input type="hidden" id="qcpColor" value="">
                            <div class="hk-cat-filter qcp-dropdown" id="qcpColorFilter">
                                <button type="button" class="hk-cat-trigger" id="qcpColorTrigger" aria-haspopup="listbox" aria-expanded="false">
                                    <span class="hk-cat-trigger-label" id="qcpColorLabel">— Chọn —</span>
                                    <i class="fa-solid fa-chevron-down hk-cat-arrow"></i>
                                </button>
                                <div class="hk-cat-panel" id="qcpColorPanel" hidden>
                                    <div class="hk-cat-list" id="qcpColorList" role="listbox">
                                        <button type="button" class="hk-cat-item is-active" data-value="" data-label="— Chọn —">— Chọn —</button>
                                        @foreach($quickCreateColors as $color)
                                            <button type="button" class="hk-cat-item" data-value="{{ $color->id }}" data-label="{{ $color->name }}">{{ $color->name }}</button>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="edit-field">
                            <label for="qcpSizeTrigger">Kích thước <span class="text-danger">*</span></label>
                            <input type="hidden" id="qcpSize" value="">
                            <div class="hk-cat-filter qcp-dropdown" id="qcpSizeFilter">
                                <button type="button" class="hk-cat-trigger" id="qcpSizeTrigger" aria-haspopup="listbox" aria-expanded="false">
                                    <span class="hk-cat-trigger-label" id="qcpSizeLabel">— Chọn —</span>
                                    <i class="fa-solid fa-chevron-down hk-cat-arrow"></i>
                                </button>
                                <div class="hk-cat-panel" id="qcpSizePanel" hidden>
                                    <div class="hk-cat-list" id="qcpSizeList" role="listbox">
                                        <button type="button" class="hk-cat-item is-active" data-value="" data-label="— Chọn —">— Chọn —</button>
                                        @foreach($quickCreateSizes as $size)
                                            <button type="button" class="hk-cat-item" data-value="{{ $size->id }}" data-label="{{ $size->name }}">{{ $size->name }}</button>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="edit-field mb-0">
                    <label for="qcpSku">SKU</label>
                    <input type="text" id="qcpSku" class="form-control" placeholder="Để trống để tự động sinh" autocomplete="off">
                </div>

                <div class="text-danger small mt-2" id="qcpError" style="display:none;"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light border" data-bs-dismiss="modal">Hủy</button>
                <button type="button" class="btn btn-primary fw-bold" id="qcpSubmitBtn">
                    <i class="fa-regular fa-floppy-disk me-1"></i> Lưu
                </button>
            </div>
        </div>
    </div>
</div>

@once
@push('styles')
<style>
.qcp-dropdown.hk-cat-filter { width: 100%; }
.qcp-dropdown .hk-cat-panel {
    left: 0;
    right: auto;
    width: 100%;
}
#qcpColorPanel .hk-cat-list,
#qcpSizePanel .hk-cat-list {
    max-height: 132px;
}
</style>
@endpush
@endonce
