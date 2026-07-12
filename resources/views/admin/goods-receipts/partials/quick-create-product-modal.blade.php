<div class="modal fade" id="qcpModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="border-radius:12px;">
            <div class="modal-header">
                <h5 class="modal-title fw-bold" style="font-size:16px;">Tạo nhanh sản phẩm</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="form-text mb-3">
                    Sản phẩm sẽ được tạo ở trạng thái <strong>nháp</strong> (ẩn khỏi website). Vào trang
                    <strong>Quản lý sản phẩm</strong> để bổ sung ảnh, mô tả, thương hiệu và công bố sau.
                </div>

                <div class="edit-field position-relative">
                    <label for="qcpName">Tên sản phẩm <span class="text-danger">*</span></label>
                    <input type="text" id="qcpName" class="form-control" placeholder="Ví dụ: Áo thun cotton trắng" autocomplete="off">
                    <div id="qcpSimilarBox" class="gr-picker-panel" style="position:absolute; display:none;"></div>
                </div>

                <div class="edit-field">
                    <label for="qcpCategory">Danh mục <span class="text-danger">*</span></label>
                    <select id="qcpCategory" class="form-select">
                        <option value="">— Chọn danh mục —</option>
                        @foreach($quickCreateCategories as $cat)
                            <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                            @foreach($cat->childrenCategories as $child)
                                <option value="{{ $child->id }}">— {{ $child->name }}</option>
                            @endforeach
                        @endforeach
                    </select>
                </div>

                <div class="row g-2">
                    <div class="col-6">
                        <div class="edit-field">
                            <label for="qcpColor">Màu sắc <span class="text-danger">*</span></label>
                            <select id="qcpColor" class="form-select">
                                <option value="">— Chọn —</option>
                                @foreach($quickCreateColors as $color)
                                    <option value="{{ $color->id }}">{{ $color->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="edit-field">
                            <label for="qcpSize">Kích thước <span class="text-danger">*</span></label>
                            <select id="qcpSize" class="form-select">
                                <option value="">— Chọn —</option>
                                @foreach($quickCreateSizes as $size)
                                    <option value="{{ $size->id }}">{{ $size->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>

                <div class="edit-field mb-0">
                    <label for="qcpCostPrice">Giá vốn <span class="text-danger">*</span></label>
                    <input type="number" id="qcpCostPrice" class="form-control" min="0" step="1000" placeholder="0">
                </div>

                <div class="text-danger small mt-2" id="qcpError" style="display:none;"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light border" data-bs-dismiss="modal">Hủy</button>
                <button type="button" class="btn btn-primary fw-bold" id="qcpSubmitBtn">
                    <i class="fa-solid fa-plus me-1"></i> Tạo &amp; thêm vào phiếu
                </button>
            </div>
        </div>
    </div>
</div>
