{{-- Exam Type Actions --}}
<div class="btn-group" role="group">
    <a href="{{ route('school.exam-types.show', $examType) }}"
       class="btn btn-sm btn-outline-info"
       title="View Details">
        <i class="bx bx-show"></i>
    </a>
    <a href="{{ route('school.exam-types.edit', $examType) }}"
       class="btn btn-sm btn-outline-warning"
       title="Edit">
        <i class="bx bx-edit"></i>
    </a>
    <button type="button"
            class="btn btn-sm btn-outline-{{ $examType->is_active ? 'danger' : 'success' }} status-toggle"
            data-id="{{ $examType->id }}"
            data-url="{{ route('school.exam-types.toggle-status', $examType) }}"
            title="{{ $examType->is_active ? 'Deactivate' : 'Activate' }}">
        <i class="bx bx-{{ $examType->is_active ? 'x' : 'check' }}"></i>
    </button>
    <button type="button"
            class="btn btn-sm btn-outline-danger delete-btn"
            data-id="{{ $examType->id }}"
            data-url="{{ route('school.exam-types.destroy', $examType) }}"
            title="Delete">
        <i class="bx bx-trash"></i>
    </button>
</div>