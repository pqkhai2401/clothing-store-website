@if(isset($paginator) && $paginator instanceof \Illuminate\Contracts\Pagination\LengthAwarePaginator && $paginator->hasPages())
    <div class="d-flex justify-content-center mt-5">
        {{ $paginator->links() }}
    </div>
@endif
