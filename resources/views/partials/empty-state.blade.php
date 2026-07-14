@php
    $esIcon = $icon ?? 'bi-bag-x';
    $esTitle = $title ?? 'No items found';
    $esMessage = $message ?? 'There are currently no items in this list.';
    $esBtnText = $button_text ?? 'Return to Shop';
    $esBtnUrl = $button_url ?? url('/san-pham');
@endphp

<div class="empty-state-container text-center py-5 my-5">
    <div class="empty-state-icon mb-4">
        <i class="bi {{ $esIcon }} text-muted opacity-50" style="font-size: 4rem;"></i>
    </div>
    <h3 class="empty-state-title text-uppercase font-semibold tracking-wider mb-3 fs-5">{{ $esTitle }}</h3>
    <p class="empty-state-message text-muted max-w-md mx-auto mb-4" style="max-width: 450px;">{{ $esMessage }}</p>
    
    @if(isset($show_button) ? $show_button : true)
        <a href="{{ $esBtnUrl }}" class="btn btn-black text-uppercase font-semibold fs-7 py-3 px-5">
            {{ $esBtnText }}
        </a>
    @endif
</div>
