@php
    $old = (array) $log->old_values;
    $new = (array) $log->new_values;
    $isAuth = in_array($log->event, ['login', 'logout'], true);
    $keys = array_values(array_unique(array_merge(array_keys($old), array_keys($new))));

    $stringify = function ($value) {
        if (is_null($value)) return '∅';
        if (is_bool($value)) return $value ? 'true' : 'false';
        if (is_array($value)) return json_encode($value, JSON_UNESCAPED_UNICODE);
        return (string) $value;
    };
@endphp

<div class="hklog-meta">
    <div class="hklog-meta__item">
        <span class="hklog-meta__label">Người thực hiện</span>
        <span class="hklog-meta__value">{{ $log->causer_name }}</span>
    </div>
    <div class="hklog-meta__item">
        <span class="hklog-meta__label">Email</span>
        <span class="hklog-meta__value">{{ $log->user?->email ?? '—' }}</span>
    </div>
    <div class="hklog-meta__item">
        <span class="hklog-meta__label">Thời gian</span>
        <span class="hklog-meta__value">{{ $log->created_at?->format('d/m/Y H:i:s') ?? '—' }}</span>
    </div>
    <div class="hklog-meta__item">
        <span class="hklog-meta__label">Hành động</span>
        <span class="hklog-meta__value">
            <span class="log-badge log-badge--{{ $log->event_tone }}">{{ $log->event_label }}</span>
        </span>
    </div>
    <div class="hklog-meta__item">
        <span class="hklog-meta__label">Đối tượng</span>
        <span class="hklog-meta__value">{{ $log->subject_label }}{{ $log->subject_description ? ' · ' . $log->subject_description : '' }}</span>
    </div>
    <div class="hklog-meta__item">
        <span class="hklog-meta__label">Địa chỉ IP</span>
        <span class="hklog-meta__value">{{ $log->ip_address ?: '—' }}</span>
    </div>
    <div class="hklog-meta__item" style="grid-column: 1 / -1;">
        <span class="hklog-meta__label">Đường dẫn</span>
        <span class="hklog-meta__value">{{ $log->url ?: '—' }}</span>
    </div>
    <div class="hklog-meta__item" style="grid-column: 1 / -1;">
        <span class="hklog-meta__label">Trình duyệt</span>
        <span class="hklog-meta__value">{{ $log->user_agent ?: '—' }}</span>
    </div>
</div>

@if($isAuth)
    <div class="hklog-empty">
        {{ $new['message'] ?? 'Sự kiện xác thực tài khoản.' }}
    </div>
@elseif(empty($keys))
    <div class="hklog-empty">Không có thay đổi chi tiết được ghi lại.</div>
@else
    <table class="hklog-diff">
        <thead>
            <tr>
                <th style="width: 30%;">Trường</th>
                @if($log->event === 'created')
                    <th>Giá trị</th>
                @elseif($log->event === 'deleted')
                    <th>Giá trị (đã xóa)</th>
                @else
                    <th>Giá trị cũ</th>
                    <th>Giá trị mới</th>
                @endif
            </tr>
        </thead>
        <tbody>
            @foreach($keys as $key)
                <tr>
                    <td class="k">{{ $key }}</td>
                    @if($log->event === 'created')
                        <td class="new">{{ $stringify($new[$key] ?? null) }}</td>
                    @elseif($log->event === 'deleted')
                        <td class="old">{{ $stringify($old[$key] ?? null) }}</td>
                    @else
                        <td class="old">{{ $stringify($old[$key] ?? null) }}</td>
                        <td class="new">{{ $stringify($new[$key] ?? null) }}</td>
                    @endif
                </tr>
            @endforeach
        </tbody>
    </table>
@endif
