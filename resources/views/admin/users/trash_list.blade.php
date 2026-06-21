<x-notification />

<div class="js-ajax-table-container ajax-table-container">
    <div class="card shadow-sm border table-page-card">
        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center flex-wrap border-bottom">
            <div class="d-flex align-items-center">
                <div class="bg-danger-subtle rounded-2 p-1 me-2 d-flex align-items-center justify-content-center">
                    <i class="fa-solid fa-trash text-danger table-header-icon"></i>
                </div>
                <h6 class="fw-bold text-dark mb-0 fs-6 text-uppercase table-heading">
                    Thùng rác người dùng ({{ $data->total() }})
                </h6>
            </div>

            <div class="d-flex gap-2 align-items-center ms-auto flex-wrap">
                <a href="{{ route('admin.users.list') }}"
                    class="btn btn-outline-secondary shadow-sm fw-bold d-flex align-items-center">
                    <i class="fa-solid fa-arrow-left me-1"></i> Quay lại
                </a>
            </div>
        </div>

        <div class="card-body">
            <div class="table-responsive js-ajax-table-wrapper">
                <table class="table table-hover align-middle mb-0 data-table js-ajax-table">
                    <thead class="table-light">
                        <tr class="text-secondary text-uppercase table-head-row">
                            <th class="text-center py-3 ps-4 fw-bold border-bottom-0" width="10%">ID</th>
                            <th class="py-3 fw-bold border-bottom-0" width="20%">Username</th>
                            <th class="py-3 fw-bold border-bottom-0" width="25%">Email</th>
                            <th class="py-3 fw-bold border-bottom-0" width="15%">Vai trò</th>
                            <th class="py-3 fw-bold border-bottom-0" width="15%">Ngày xóa</th>
                            <th class="text-center py-3 fw-bold border-bottom-0" width="15%">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($data as $user)
                            <tr class="transition-base">
                                <td class="py-3 text-muted fw-bold text-center">{{ $user->id }}</td>
                                <td class="py-3 text-muted fw-bold">{{ $user->username }}</td>
                                <td class="py-3 text-muted fw-bold">{{ $user->email }}</td>
                                <td class="py-3 text-muted fw-bold">{{ $user->role?->name ?? 'Chưa có vai trò' }}</td>
                                <td class="py-3 text-muted fw-bold">
                                    {{ $user->deleted_at->format('d/m/Y H:i') }}
                                </td>
                                <td class="text-center">
                                    <div class="d-flex justify-content-center gap-2">
                                        <form method="POST" action="{{ route('admin.users.restore', $user->id) }}">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit"
                                                class="btn btn-sm btn-outline-success fw-bold d-flex align-items-center">
                                                <i class="fa-solid fa-rotate-left me-1"></i> Khôi phục
                                            </button>
                                        </form>

                                        <form method="POST" action="{{ route('admin.users.forceDelete', $user->id) }}"
                                            onsubmit="return confirm('Xóa vĩnh viễn người dùng này? Hành động này không thể hoàn tác.')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                class="btn btn-sm btn-outline-danger fw-bold d-flex align-items-center">
                                                <i class="fa-solid fa-trash me-1"></i> Xóa vĩnh viễn
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-5 border-bottom-0">
                                    <div class="d-flex flex-column align-items-center justify-content-center table-empty-state">
                                        <i class="fa-solid fa-inbox text-muted mb-3 table-empty-icon"></i>
                                        <h5 class="text-muted mb-2">Thùng rác trống</h5>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>

                @include('layouts.components.pagination', ['paginator' => $data])
            </div>
        </div>
    </div>
</div>
