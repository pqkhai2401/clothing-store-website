@extends('layouts.admin')

@section('title', 'Thùng rác ' . ($itemLabelLower ?? 'tài khoản'))

@section('css')
    <style>
        /* ── Search ────────────────────────────────────────── */
        .user-trash-search {
            min-height: 38px;
            border: 1px solid #D8E0EA;
            border-radius: 10px;
            font-size: 13px;
            color: #0F172A;
            background: #ffffff;
            width: 320px;
            box-shadow: none;
        }

        .user-trash-search:focus {
            border-color: #16A34A;
            box-shadow: 0 0 0 3px rgba(22, 163, 74, 0.12);
            color: #0F172A;
        }

        .user-trash-search::placeholder {
            color: #94A3B8;
        }

        /* ── Table wrapper ─────────────────────────────────── */
        .user-trash-table-wrap {
            overflow: hidden;
            background: #ffffff;
            border: 1px solid #E2E8F0;
            border-radius: 12px;
        }

        /* ── Table ─────────────────────────────────────────── */
        .user-trash-table thead th {
            color: #334155;
            font-weight: 800;
            font-size: 13px;
            padding: 13px 16px;
            border-bottom: 1px solid #E2E8F0;
            background: #F8FAFC;
            white-space: nowrap;
        }

        .user-trash-table tbody td {
            padding: 14px 16px;
            font-size: 13px;
            color: #0F172A;
            border-bottom: 1px solid #E2E8F0;
            vertical-align: middle;
        }

        .user-trash-table tbody tr:last-child td {
            border-bottom: 0;
        }

        .user-trash-table tbody tr:hover td {
            background: #F8FAFC;
        }

        /* ── Muted helpers ─────────────────────────────────── */
        .user-trash-id   { color: #64748B; font-weight: 600; }
        .user-trash-date { color: #475569; }
        .user-trash-email { color: #475569; }

        /* ── Role badge ────────────────────────────────────── */
        .role-badge {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 999px;
            font-size: 12px;
            font-weight: 600;
            padding: 5px 13px;
            min-height: 28px;
            border: 1.5px solid #CBD5E1;
            color: #475569;
            background: #F8FAFC;
            line-height: 1;
            white-space: nowrap;
        }

        .role-badge[data-role="admin"] {
            background: #EFF6FF;
            border-color: #BFDBFE;
            color: #2563EB;
        }

        /* ── Dark mode ─────────────────────────────────────── */
        [data-theme="dark"] .user-trash-search {
            background: #101C33 !important;
            border-color: #2A3B59 !important;
            color: #E2E8F0 !important;
        }
        [data-theme="dark"] .user-trash-search:focus {
            border-color: #3B82F6 !important;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.15) !important;
        }
        [data-theme="dark"] .user-trash-search::placeholder { color: #64748B !important; }
        [data-theme="dark"] .user-trash-table-wrap {
            background: #0F1B31 !important;
            border-color: #22324D !important;
        }
        [data-theme="dark"] .user-trash-table thead th {
            background: #0C1830 !important;
            color: #94A3B8 !important;
            border-color: #22324D !important;
        }
        [data-theme="dark"] .user-trash-table tbody td {
            color: #E2E8F0 !important;
            border-color: #22324D !important;
        }
        [data-theme="dark"] .user-trash-table tbody tr:hover td {
            background: #1A3050 !important;
        }
        [data-theme="dark"] .user-trash-id   { color: #94A3B8 !important; }
        [data-theme="dark"] .user-trash-date  { color: #94A3B8 !important; }
        [data-theme="dark"] .user-trash-email { color: #CBD5E1 !important; }
        [data-theme="dark"] .role-badge {
            background: rgba(148,163,184,0.14) !important;
            border-color: rgba(148,163,184,0.32) !important;
            color: #CBD5E1 !important;
        }
        [data-theme="dark"] .role-badge[data-role="admin"] {
            background: rgba(59,130,246,0.16) !important;
            border-color: rgba(96,165,250,0.45) !important;
            color: #BFDBFE !important;
        }
    </style>
@endsection

@section('content')
    @php
        $showRoleColumn = in_array(($type ?? 'all'), ['all', 'staff'], true);
        $emptyColspan   = $showRoleColumn ? 6 : 5;
        $trashRoute     = ($routePrefix ?? 'admin.users') . '.trash';
    @endphp

    <main class="app-main container-fluid py-4">
        <x-notification />

        {{-- Page header --}}
        <div class="d-flex align-items-start justify-content-between gap-3 mb-3 flex-wrap">
            <div>
                <h1 class="h3 fw-bold mb-1" style="color: #020617;">Thùng rác {{ $itemLabelLower ?? 'tài khoản' }}</h1>
                <p class="mb-0" style="color: #64748B; font-size: 14px;">Danh sách {{ $itemLabelLower ?? 'tài khoản' }} đã bị xóa mềm và có thể khôi phục.</p>
            </div>
            <a href="{{ route(($routePrefix ?? 'admin.users') . '.list') }}"
               class="btn btn-outline-secondary fw-semibold" style="border-radius: 10px; font-size: 13px;">
                <i class="fa-solid fa-arrow-left me-1"></i> Quay lại
            </a>
        </div>

        {{-- Search bar --}}
        <form method="GET" action="{{ route($trashRoute) }}" class="mb-3 d-flex align-items-center gap-2">
            <input type="search" name="keyword" value="{{ $keyword ?? '' }}"
                class="form-control user-trash-search"
                placeholder="Tìm kiếm theo tên hoặc email..."
                autocomplete="off">
            @if(!empty($keyword))
                <a href="{{ route($trashRoute) }}" class="btn btn-outline-secondary btn-sm fw-semibold" style="border-radius: 8px;">
                    <i class="fa-solid fa-xmark me-1"></i> Xóa lọc
                </a>
            @endif
        </form>

        {{-- Table --}}
        <div class="user-trash-table-wrap">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0 user-trash-table">
                    <thead>
                        <tr>
                            <th class="ps-4" style="width: 70px;">ID</th>
                            <th>Tài khoản</th>
                            <th>Email</th>
                            @if($showRoleColumn)
                                <th>Vai trò</th>
                            @endif
                            <th>Ngày xóa</th>
                            <th class="text-end pe-4">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($data as $user)
                            @php
                                $roleName  = $user->roles->first()?->name;
                                $roleLabel = match ($roleName) {
                                    'admin'    => 'Quản trị viên',
                                    'staff'    => 'Nhân viên',
                                    'customer' => 'Khách hàng',
                                    default    => 'Chưa có vai trò',
                                };
                            @endphp
                            <tr>
                                <td class="ps-4 user-trash-id">{{ $user->id }}</td>
                                <td>
                                    <div class="fw-bold">{{ $user->username }}</div>
                                </td>
                                <td class="user-trash-email">{{ $user->email }}</td>
                                @if($showRoleColumn)
                                    <td>
                                        <span class="role-badge" data-role="{{ $roleName ?? '' }}">{{ $roleLabel }}</span>
                                    </td>
                                @endif
                                <td class="user-trash-date">{{ optional($user->deleted_at)->format('d/m/Y H:i') }}</td>
                                <td class="text-end pe-4">
                                    <div class="d-inline-flex gap-2">
                                        <form method="POST" action="{{ route(($routePrefix ?? 'admin.users') . '.restore', $user->id) }}">
                                            @csrf @method('PATCH')
                                            <button type="submit" class="btn btn-sm btn-outline-success fw-semibold">
                                                <i class="fa-solid fa-rotate-left me-1"></i> Khôi phục
                                            </button>
                                        </form>
                                        <form method="POST"
                                            action="{{ route(($routePrefix ?? 'admin.users') . '.forceDelete', $user->id) }}"
                                            onsubmit="return confirm('Xóa vĩnh viễn {{ $itemLabelLower ?? 'tài khoản' }} này? Hành động này không thể hoàn tác.')">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger fw-semibold">
                                                <i class="fa-solid fa-trash me-1"></i> Xóa vĩnh viễn
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="{{ $emptyColspan }}" class="text-center py-5">
                                    <i class="fa-solid fa-inbox text-muted mb-3" style="font-size: 42px; display: block;"></i>
                                    <div class="fw-semibold text-muted">Thùng rác trống</div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="border-top px-4 py-2" style="background: var(--hk-bg-card, #fff); border-color: #E2E8F0 !important;">
                @include('layouts.components.pagination', [
                    'paginator'  => $data,
                    'itemLabel'  => $itemLabelLower ?? 'tài khoản',
                ])
            </div>
        </div>
    </main>
@endsection

@push('scripts')
    @include('layouts.components.confirm.delete')
@endpush
