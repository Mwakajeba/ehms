{{-- Action buttons for DataTable --}}
<a href="{{ route('college.departments.show', $id) }}" class="btn btn-sm btn-outline-info" title="View Details">
    <i class="bx bx-show"></i>
</a>
<a href="{{ route('college.departments.edit', $id) }}" class="btn btn-sm btn-outline-warning" title="Edit">
    <i class="bx bx-edit"></i>
</a>
<button type="button" class="btn btn-sm btn-danger" title="Delete"
        onclick="confirmDelete({{ $id }}, '{{ $name }}')">
    <i class="bx bx-trash"></i>
</button>