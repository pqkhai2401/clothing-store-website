<x-notification />

@php
    $showRoleColumn = in_array(($type ?? 'all'), ['all', 'staff'], true);
    $emptyColspan = $showRoleColumn ? 6 : 5;
@endphp

<div class="card border shadow-sm">
    <div class="card-header bg-white py-3">
        <h2 class="h5 fw-bold mb-0">Thùng rác {{ $itemLabelLower ?? 'tài khoản' }} ({{ $data->total() }})</h2>
    </div>

    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr class="text-uppercase text-muted small">
                        <th class="text-center ps-4">ID</th>
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
                        @if($showRoleColumn)
                            @php
                                $roleLabel = match ($user->role?->name) {
                                    'admin' => 'Quản trị viên',
                                    'staff' => 'Nhân viên',
                                    'customer' => 'Khách hàng',
                                    default => 'Chưa có vai trò',
                                };
                            @endphp
                        @endif
                        <tr>
                            <td class="text-center ps-4 fw-semibold text-muted">{{ $user->id }}</td>
                            <td>
                                <div class="fw-bold">{{ $user->display_name ?: $user->username }}</div>
                                <div class="text-muted small">{{ $user->username }}</div>
                            </td>
                            <td>{{ $user->email }}</td>
                            @if($showRoleColumn)
                                <td><span class="badge text-bg-light border">{{ $roleLabel }}</span></td>
                            @endif
                            <td>{{ optional($user->deleted_at)->format('d/m/Y H:i') }}</td>
                            <td class="text-end pe-4">
                                <div class="d-inline-flex gap-2">
                                    <form method="POST" action="{{ route(($routePrefix ?? 'admin.users').'.restore', $user->id) }}">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="btn btn-sm btn-outline-success fw-semibold">
                                            <i class="fa-solid fa-rotate-left me-1"></i> Khôi phục
                                        </button>
                                    </form>

                                    <form method="POST" action="{{ route(($routePrefix ?? 'admin.users').'.forceDelete', $user->id) }}"
                                        onsubmit="return confirm('Xóa vĩnh viễn {{ $itemLabelLower ?? 'tài khoản' }} này? Hành động này không thể hoàn tác.')">
                                        @csrf
                                        @method('DELETE')
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
                                <i class="fa-solid fa-inbox text-muted mb-3" style="font-size: 42px;"></i>
                                <div class="fw-semibold text-muted">Thùng rác trống</div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="card-footer bg-white">
        @include('layouts.components.pagination', ['paginator' => $data])
    </div>
</div>
