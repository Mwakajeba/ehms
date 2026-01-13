<div class="btn-group" role="group">
    <a href="{{ route('school.subjects.show', $subject->hashid) }}" class="btn btn-sm btn-outline-info" title="View">
        <i class="bx bx-show"></i>
    </a>
    <a href="{{ route('school.subjects.edit', $subject->hashid) }}" class="btn btn-sm btn-outline-warning" title="Edit">
        <i class="bx bx-edit"></i>
    </a>
    <form action="{{ route('school.subjects.destroy', $subject->hashid) }}" method="POST" class="d-inline">
        @csrf
        @method('DELETE')
        <button type="button" class="btn btn-sm {{ $subject->subjectGroups()->count() > 0 ? 'btn-secondary' : 'btn-danger' }} delete-subject-btn"
                title="{{ $subject->subjectGroups()->count() > 0 ? 'Cannot delete - assigned to subject group' : 'Delete' }}"
                {{ $subject->subjectGroups()->count() > 0 ? 'disabled' : '' }}
                data-subject-name="{{ $subject->name }}">
            <i class="bx bx-trash"></i>
        </button>
    </form>
</div>