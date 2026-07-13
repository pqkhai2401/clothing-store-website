@php
    $rAuthor = is_object($review) ? $review->user->username : ($review['author'] ?? 'Anonymous');
    $rAvatar = is_object($review) ? ($review->user->avatar_display_url ?? null) : ($review['avatar'] ?? null);
    $rRating = is_object($review) ? $review->rating : ($review['rating'] ?? 5);
    $rComment = is_object($review) ? $review->comment : ($review['comment'] ?? '');
    $rDate = is_object($review) ? $review->created_at->format('M d, Y') : ($review['date'] ?? date('M d, Y'));
    
    // Fallback avatar using user initials if avatar is not set
    if (!$rAvatar) {
        $rAvatar = 'https://api.dicebear.com/7.x/initials/svg?seed=' . urlencode($rAuthor);
    }
@endphp

<div class="review-card">
    <div>
        <!-- Star Ratings -->
        <div class="review-stars">
            @for($i = 1; $i <= 5; $i++)
                @if($i <= $rRating)
                    <i class="bi bi-star-fill text-warning"></i>
                @else
                    <i class="bi bi-star text-muted opacity-50"></i>
                @endif
            @endfor
        </div>
        
        <p class="review-text">
            "{{ $rComment }}"
        </p>
    </div>
    
    <div class="d-flex align-items-center mt-4 pt-3 border-top border-light">
        <img src="{{ $rAvatar }}" alt="{{ $rAuthor }}" class="rounded-circle me-3" style="width: 40px; height: 40px; object-fit: cover;">
        <div>
            <h5 class="review-author mb-0">{{ $rAuthor }}</h5>
            <small class="text-muted" style="font-size: 11px;">{{ $rDate }}</small>
        </div>
    </div>
</div>
