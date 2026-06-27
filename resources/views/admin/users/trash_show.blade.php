@extends('layouts.admin')

@section('title', 'Thùng rác '.$itemLabelLower)

@section('content')
    <main class="app-main container-fluid py-4">
        <div class="d-flex align-items-start justify-content-between gap-3 mb-4 flex-wrap">
            <div>
                <h1 class="h3 fw-bold mb-1" style="color: black;">Thùng rác {{ $itemLabelLower ?? 'tài khoản' }}</h1>
                <div class="text-muted" style="color: black">Danh sách {{ $itemLabelLower ?? 'tài khoản' }} đã bị xóa mềm.</div>
            </div>

            <a href="{{ route(($routePrefix ?? 'admin.users').'.list') }}" class="btn btn-light border fw-semibold">
                <i class="fa-solid fa-arrow-left me-1"></i> Quay lại
            </a>
        </div>

        @include('admin.users.trash_list')
    </main>
@endsection
