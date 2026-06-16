<div class="row g-4">
    @forelse($reviews as $review)
        <div class="col-md-4">
            @include('partials.review-card', ['review' => $review])
        </div>
    @empty
        <div class="col-12 text-center py-5">
            <i class="bi bi-chat-left-text fs-2 text-muted mb-3 d-block"></i>
            <p class="text-muted">No reviews yet for this product. Be the first to share your thoughts!</p>
        </div>
    @endforelse
</div>
