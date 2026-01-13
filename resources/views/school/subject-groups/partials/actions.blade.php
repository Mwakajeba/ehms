<div class="btn-group" role="group">
    <a href="{{ route('school.subject-groups.show', $group->hashid) }}" class="btn btn-sm btn-outline-info" title="View">
        <i class="bx bx-show"></i>
    </a>
    <a href="{{ route('school.subject-groups.edit', $group->hashid) }}" class="btn btn-sm btn-outline-warning" title="Edit">
        <i class="bx bx-edit"></i>
    </a>
    <form action="{{ route('school.subject-groups.destroy', $group->hashid) }}" method="POST" class="d-inline">
        @csrf
        @method('DELETE')
        <button type="button" class="btn btn-sm btn-danger delete-subject-group-btn" title="Delete"
                data-subject-group-name="{{ $group->name }}">
            <i class="bx bx-trash"></i>
        </button>
    </form>
</div>