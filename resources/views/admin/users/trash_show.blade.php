@extends('layouts.admin')

@section('title', 'Thùng rác người dùng')

@section('content')
    <main class="app-main container">
        <div class="app-content-header">
            <div class="container-fluid">
                <div class="row my-3">
                    <div class="col-sm-12 px-0">
                        <div class="d-flex align-items-center justify-content-between">
                            <h3 class="mb-0 fw-bold text-uppercase">Thùng rác người dùng</h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="app-content">
            @include('admin.users.trash_list')
        </div>
    </main>
@endsection
