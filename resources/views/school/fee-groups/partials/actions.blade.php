@if($feeGroup)
<a href="{{ route('school.fee-groups.show', $feeGroup->hashid) }}" class="btn btn-info btn-sm" title="View">
    <i class="bx bx-show"></i>
</a>
<a href="{{ route('school.fee-groups.edit', $feeGroup->hashid) }}" class="btn btn-warning btn-sm" title="Edit">
    <i class="bx bx-edit"></i>
</a>
<form action="{{ route('school.fee-groups.destroy', $feeGroup->hashid) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this fee group? This action cannot be undone.')">
    @csrf
    @method('DELETE')
    <button type="submit" class="btn btn-danger btn-sm" title="Delete">
        <i class="bx bx-trash"></i>
    </button>
</form>
@endif