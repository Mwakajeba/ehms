<div class="btn-group" role="group">
    <a href="{{ route('school.classes.show', $class) }}" class="btn btn-sm btn-outline-info" title="View">
        <i class="bx bx-show"></i>
    </a>
    <a href="{{ route('school.classes.edit', $class) }}" class="btn btn-sm btn-outline-warning" title="Edit">
        <i class="bx bx-edit"></i>
    </a>
    <button type="button" class="btn btn-sm btn-danger" title="Delete" onclick="confirmDelete({{ $class->id }}, '{{ $class->name }}', {{ $class->enrollments_count }}, {{ $class->sections_count }})">
        <i class="bx bx-trash"></i>
    </button>
</div>