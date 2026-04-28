@extends('layouts.app')
@section('title', "Danh sách ảnh bệnh nhân")
@section('content')
    <main class="app-main container">
        <div class="app-content-header">
            <div class="container-fluid">
                <div class="row my-3">
                    <div class="col-sm-12 px-0">
                        <div class="d-flex align-items-center justify-content-between">
                            <div class="d-flex align-items-center">
                                <h3 class="mb-0 fw-bold text-uppercase">
                                    Danh sách ảnh bệnh nhân
                                </h3>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="app-content">
            @include('images.list')
        </div>
    </main>
@endsection
