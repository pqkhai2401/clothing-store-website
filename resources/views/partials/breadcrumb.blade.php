@php
    // Expects $items as array of ['name' => 'Label', 'url' => 'URL', 'active' => bool]
@endphp

@if(isset($items) && is_array($items))
    <div class="breadcrumb-section bg-light py-3">
        <div class="container-fluid px-lg-5">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    @foreach($items as $item)
                        @if(isset($item['active']) && $item['active'])
                            <li class="breadcrumb-item active text-uppercase tracking-wider font-semibold" aria-current="page" style="font-size: 11px;">
                                {{ $item['name'] }}
                            </li>
                        @else
                            <li class="breadcrumb-item text-uppercase tracking-wider" style="font-size: 11px;">
                                <a href="{{ $item['url'] ?? '#' }}" class="text-muted text-decoration-none">{{ $item['name'] }}</a>
                            </li>
                        @endif
                    @endforeach
                </ol>
            </nav>
        </div>
    </div>
@endif
