@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show border-0 rounded-0 fs-7 tracking-wider text-uppercase" role="alert">
        <i class="bi bi-check-circle-fill me-2"></i> {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

@if(session('error') || session('danger'))
    <div class="alert alert-danger alert-dismissible fade show border-0 rounded-0 fs-7 tracking-wider text-uppercase" role="alert">
        <i class="bi bi-exclamation-triangle-fill me-2"></i> {{ session('error') ?? session('danger') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

@if(session('warning'))
    <div class="alert alert-warning alert-dismissible fade show border-0 rounded-0 fs-7 tracking-wider text-uppercase" role="alert">
        <i class="bi bi-exclamation-circle-fill me-2"></i> {{ session('warning') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

@if(session('info'))
    <div class="alert alert-info alert-dismissible fade show border-0 rounded-0 fs-7 tracking-wider text-uppercase" role="alert">
        <i class="bi bi-info-circle-fill me-2"></i> {{ session('info') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

@if ($errors->any())
    <div class="alert alert-danger alert-dismissible fade show border-0 rounded-0 fs-7 tracking-wider" role="alert">
        <div class="font-semibold text-uppercase mb-2"><i class="bi bi-exclamation-triangle-fill me-2"></i> Please correct the following errors:</div>
        <ul class="mb-0 ps-3">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif
