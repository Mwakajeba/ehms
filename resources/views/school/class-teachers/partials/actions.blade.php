<div class="btn-group" role="group">
    <a href="{{ route('school.class-teachers.show', $classTeacher) }}" class="btn btn-sm btn-outline-primary" title="View">
        <i class="bx bx-show"></i>
    </a>
    <a href="{{ route('school.class-teachers.edit', $classTeacher) }}" class="btn btn-sm btn-outline-secondary" title="Edit">
        <i class="bx bx-edit"></i>
    </a>
    <form action="{{ route('school.class-teachers.toggle-status', $classTeacher) }}" method="POST" class="d-inline">
        @csrf
        @method('PATCH')
        <button type="submit" class="btn btn-sm btn-outline-warning toggle-status-btn" title="{{ $classTeacher->is_active ? 'Deactivate' : 'Activate' }}"
                data-name="{{ $classTeacher->employee->full_name }} ({{ $classTeacher->classe->name }})"
                data-action="{{ $classTeacher->is_active ? 'deactivate' : 'activate' }}">
            <i class="bx bx-{{ $classTeacher->is_active ? 'x' : 'check' }}"></i>
        </button>
    </form>
    <form action="{{ route('school.class-teachers.destroy', $classTeacher) }}" method="POST" class="d-inline">
        @csrf
        @method('DELETE')
        <button type="submit" class="btn btn-sm btn-outline-danger delete-btn" title="Delete" data-name="{{ $classTeacher->employee->full_name }} ({{ $classTeacher->classe->name }})">
            <i class="bx bx-trash"></i>
        </button>
    </form>
</div>