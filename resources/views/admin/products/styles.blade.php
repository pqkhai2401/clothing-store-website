<style>
    .product-admin-page {
        background: #f8fafc;
        min-height: calc(100vh - 56px);
        padding-top: 0 !important;
    }

    .product-header-title {
        color: #000000;
        font-size: 25px;
        font-weight: 800;
        letter-spacing: 0;
        margin-top: 15px;
    }

    .product-header-desc {
        color: #64748b;
        font-size: 16px;
    }

    .product-toolbar {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 16px;
        margin: 30px 0 16px;
    }

    .product-toolbar-left {
        display: flex;
        align-items: center;
        gap: 10px;
        flex: 1 1 auto;
        flex-wrap: nowrap;
        min-width: 0;
    }

    .product-search {
        min-height: 38px;
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        background: #fff;
        box-shadow: 0 1px 2px rgba(15, 23, 42, 0.04);
        font-size: 14px;
        width: min(380px, 100%);
        flex: 0 1 380px;
    }

    .product-search:focus {
        border-color: #111827;
        box-shadow: none;
    }

    /* Custom category dropdown */
    .hk-cat-filter {
        position: relative;
        width: 220px;
        flex: 0 0 220px;
    }

    .hk-cat-trigger {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 8px;
        width: 100%;
        min-height: 38px;
        padding: 0 12px;
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        background: #fff;
        box-shadow: 0 1px 2px rgba(15, 23, 42, 0.04);
        font-size: 14px;
        color: #374151;
        text-align: left;
        cursor: pointer;
        transition: border-color .15s;
    }

    .hk-cat-trigger:hover,
    .hk-cat-trigger.is-open {
        border-color: #111827;
    }

    .hk-cat-trigger-label {
        flex: 1;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .hk-cat-arrow {
        font-size: 11px;
        color: #6b7280;
        transition: transform .2s;
        flex-shrink: 0;
    }

    .hk-cat-trigger.is-open .hk-cat-arrow {
        transform: rotate(180deg);
    }

    .hk-cat-panel {
        position: absolute;
        top: calc(100% + 6px);
        left: 0;
        z-index: 1050;
        width: 240px;
        background: #fff;
        border: 1px solid #e5e7eb;
        border-radius: 10px;
        box-shadow: 0 8px 32px rgba(15, 23, 42, 0.14);
        overflow: hidden;
    }

    .hk-cat-search-wrap {
        display: flex;
        align-items: center;
        gap: 8px;
        padding: 10px 12px 8px;
        border-bottom: 1px solid #f3f4f6;
    }

    .hk-cat-search-icon {
        font-size: 13px;
        color: #9ca3af;
        flex-shrink: 0;
    }

    .hk-cat-search-input {
        flex: 1;
        border: 0;
        outline: none;
        font-size: 13px;
        color: #111827;
        background: transparent;
    }

    .hk-cat-search-input::placeholder {
        color: #9ca3af;
    }

    .hk-cat-list {
        max-height: 240px;
        overflow-y: auto;
        padding: 6px 0;
    }

    .hk-cat-item {
        display: block;
        width: 100%;
        padding: 8px 14px;
        border: 0;
        background: transparent;
        font-size: 13px;
        color: #374151;
        text-align: left;
        cursor: pointer;
        transition: background .12s;
    }

    .hk-cat-item:hover {
        background: #f3f4f6;
    }

    .hk-cat-item.is-active {
        color: #111827;
        font-weight: 700;
        background: #f0f9ff;
    }

    .hk-cat-empty {
        padding: 12px 14px;
        font-size: 13px;
        color: #9ca3af;
        text-align: center;
    }

    .product-tool-actions {
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .product-action-btn {
        min-height: 38px;
        padding: 8px 14px;
        border-radius: 8px;
        font-weight: 800;
        font-size: 13px;
    }

    .product-action-btn.btn-dark {
        background: #020617;
        border-color: #020617;
    }

    .product-table-wrap {
        overflow: hidden;
        background: #fff;
        border: 1px solid #e5e7eb;
        border-radius: 9px;
    }

    .product-table {
        margin: 0;
        --bs-table-hover-bg: #f8fafc;
    }

    .product-table thead th {
        height: 48px;
        padding: 0 16px;
        background: #fff;
        color: #020617;
        border-bottom: 1px solid #e5e7eb;
        font-size: 14px;
        font-weight: 800;
        line-height: 1;
        vertical-align: middle;
        white-space: nowrap;
    }

    .product-table tbody td {
        min-height: 64px;
        padding: 15px 16px;
        color: #0f172a;
        border-bottom: 1px solid #e5e7eb;
        font-size: 13px;
        vertical-align: middle;
    }

    .product-table tbody tr:last-child td {
        border-bottom: 0;
    }

    .product-check {
        width: 18px;
        height: 18px;
        cursor: pointer;
        border-color: #d1d5db;
        border-radius: 5px;
    }

    .product-sort-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 10px;
        padding: 0;
        color: inherit;
        background: transparent;
        border: 0;
        font: inherit;
        font-weight: 800;
    }

    .product-sort-icon {
        display: inline-flex;
        align-items: center;
        min-width: 20px;
        color: #111827;
        font-size: 15px;
        font-weight: 800;
        line-height: 1;
    }

    .product-sort-btn.is-active {
        padding: 8px 10px;
        border-radius: 10px;
        background: #f3f4f6;
    }

    .product-sort-btn.is-active .product-sort-icon {
        font-size: 16px;
    }

    .product-thumb {
        width: 56px;
        height: 56px;
        object-fit: cover;
        border-radius: 4px;
        border: 1px solid #e5e7eb;
        background: #f9fafb;
    }

    .product-thumb-placeholder {
        width: 56px;
        height: 56px;
        border-radius: 4px;
        border: 1px solid #e5e7eb;
        background: #f3f4f6;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #9ca3af;
        font-size: 16px;
    }

    .status-badge {
        border-radius: 999px;
        font-size: 11px;
        font-weight: 800;
        padding: 5px 9px;
    }

    .price-display {
        display: flex;
        flex-direction: column;
        gap: 2px;
    }

    .price-sale {
        font-weight: 800;
        color: #dc2626;
    }

    .price-original {
        font-size: 11px;
        color: #9ca3af;
        text-decoration: line-through;
    }

    .price-normal {
        font-weight: 800;
        color: #111827;
    }

    .product-more-btn {
        width: 34px;
        height: 34px;
        border: 0;
        border-radius: 10px;
        color: #111827;
        background: #f3f4f6;
        font-weight: 900;
    }

    .product-more-btn:hover {
        background: #e5e7eb;
    }

    .product-row-menu {
        min-width: 168px;
        padding: 8px;
        border: 1px solid #e5e7eb;
        border-radius: 9px;
        box-shadow: 0 16px 40px rgba(15, 23, 42, 0.14);
    }

    .product-row-menu .dropdown-item {
        display: flex;
        align-items: center;
        gap: 10px;
        min-height: 38px;
        border-radius: 7px;
        font-size: 14px;
        font-weight: 600;
    }

    @media (max-width: 767.98px) {
        .product-toolbar {
            align-items: stretch;
            flex-direction: column;
        }

        .product-toolbar-left,
        .product-tool-actions {
            flex-wrap: wrap;
            justify-content: flex-end;
        }

        .product-search {
            width: 100%;
        }
    }
</style>
