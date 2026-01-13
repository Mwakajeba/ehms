<div class="btn-group" role="group">
    <a href="{{ route('school.students.show', $student) }}" class="btn btn-sm btn-outline-info" title="View">
        <i class="bx bx-show"></i>
    </a>
    <a href="{{ route('school.students.edit', $student) }}" class="btn btn-sm btn-outline-warning" title="Edit">
        <i class="bx bx-edit"></i>
    </a>
    <a href="{{ route('school.students.assign-parents', $student) }}" class="btn btn-sm btn-outline-success" title="Assign Parents">
        <i class="bx bx-user-plus"></i>
    </a>
    <button type="button" class="btn btn-sm btn-danger" title="Delete"
            onclick="confirmDelete('{{ $student->first_name }} {{ $student->last_name }}', '{{ route('school.students.destroy', $student) }}')">
        <i class="bx bx-trash"></i>
    </button>
</div>