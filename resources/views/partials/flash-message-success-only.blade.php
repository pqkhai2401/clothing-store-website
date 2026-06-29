@if(session('success'))
    <div class="alert alert-success alert-dismissible alert-auto-dismiss fade show border-0 rounded-0 fs-7 tracking-wider text-uppercase" role="alert">
        <i class="bi bi-check-circle-fill me-2"></i> {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        document.querySelectorAll('.alert-auto-dismiss').forEach(function(alert) {
            setTimeout(function() {
                var bsAlert = bootstrap.Alert.getOrCreateInstance(alert);
                bsAlert.close();
            }, 3000);
        });
    });
</script>
@endpush
