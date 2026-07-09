@php
    $eventIcons = [
        'created'  => 'fa-solid fa-plus',
        'updated'  => 'fa-solid fa-pen',
        'deleted'  => 'fa-solid fa-trash-can',
        'restored' => 'fa-solid fa-rotate-left',
        'role_updated' => 'fa-solid fa-user-shield',
        'login'    => 'fa-solid fa-right-to-bracket',
        'logout'   => 'fa-solid fa-right-from-bracket',
    ];
@endphp

<div class="account-table-wrap">
    <div class="table-responsive">
        <table class="table table-hover account-table align-middle" id="activityLogTable">
            <thead>
                <tr>
                    <th style="width: 48px;" class="hk-cb-th">
                        <input type="checkbox" class="form-check-input hk-cb-all" id="logCheckAll">
                    </th>
                    <th style="width: 180px;">Thời gian</th>
                    <th>Người thực hiện</th>
                    <th style="width: 150px;">Hành động</th>
                    <th>Đối tượng</th>
                    <th style="width: 140px;">Địa chỉ IP</th>
                    <th class="text-end pe-4" style="width: 110px;">Chi tiết</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($data as $log)
                    <tr data-log-row="{{ $log->id }}">
                        <td class="hk-cb-td">
                            <input type="checkbox" class="form-check-input hk-cb-row" value="{{ $log->id }}">
                        </td>
                        <td>
                            <div class="log-time-main">{{ $log->created_at?->format('d/m/Y') }}</div>
                            <div class="log-time-sub">{{ $log->created_at?->format('H:i:s') }}</div>
                        </td>
                        <td>
                            <span class="fw-semibold">{{ $log->causer_name }}</span>
                        </td>
                        <td>
                            <span class="log-badge log-badge--{{ $log->event_tone }}">
                                <i class="{{ $eventIcons[$log->event] ?? 'fa-solid fa-circle-info' }}"></i>
                                {{ $log->event_label }}
                            </span>
                        </td>
                        <td>
                            <div class="log-subject-label">{{ $log->subject_label }}</div>
                            @if($desc = $log->subject_description)
                                <div class="log-subject-desc">{{ $desc }}</div>
                            @endif
                        </td>
                        <td>
                            <span class="log-ip">{{ $log->ip_address ?: '—' }}</span>
                        </td>
                        <td class="text-end pe-4">
                            <button type="button" class="log-detail-btn"
                                data-log-show-url="{{ route('admin.logs.show', $log->id) }}">
                                <i class="fa-regular fa-eye"></i> Xem
                            </button>
                        </td>
                    </tr>
                @empty
                    <tr data-empty-row>
                        <td colspan="7" class="text-center py-5">
                            <i class="fa-solid fa-inbox text-muted mb-3" style="font-size: 42px;"></i>
                            <div class="fw-semibold text-muted">Chưa có bản ghi nhật ký phù hợp</div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="bg-white border border-top-0 rounded-bottom px-3 py-2">
    @include('layouts.components.pagination', [
        'paginator'     => $data,
        'itemLabel'     => 'log',
        'bulkDeleteUrl' => route('admin.logs.bulkDelete'),
        'bulkStatusUrl' => null,
    ])
</div>
