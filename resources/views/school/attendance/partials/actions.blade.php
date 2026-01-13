{{-- Attendance Actions Partial --}}
@php
    $session = $session ?? null;
    if (!$session) {
        echo '<!-- Session data not available -->';
        return;
    }
@endphp

<div class="btn-group" role="group">
    <a href="{{ route('school.attendance.show', $session->getRouteKey()) }}"
       class="btn btn-sm btn-outline-primary"
       title="View Attendance Session">
        <i class="bx bx-show"></i>
    </a>

    @if($session->status === 'active')
        <a href="{{ route('school.attendance.edit', $session->getRouteKey()) }}"
           class="btn btn-sm btn-outline-warning"
           title="Edit Session">
            <i class="bx bx-edit"></i>
        </a>
    @endif

    @if($session->status === 'active' || $session->status === 'completed')
        <a href="{{ route('school.attendance.show', $session->getRouteKey()) }}#mark-attendance"
           class="btn btn-sm btn-outline-success"
           title="Mark Attendance">
            <i class="bx bx-check-circle"></i>
        </a>
    @endif

    <button type="button"
            class="btn btn-sm btn-danger"
            onclick="confirmDelete('{{ $session->session_date->format('M d, Y') }} - {{ $session->class->name }} {{ $session->stream->name }}', '{{ route('school.attendance.destroy', $session->getRouteKey()) }}')"
            title="Delete Session">
        <i class="bx bx-trash"></i>
    </button>
</div>