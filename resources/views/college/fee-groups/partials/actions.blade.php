<a href="{{ route('college.fee-groups.show', $feeGroup->hashid) }}" class="btn btn-info btn-sm" title="View">
    <i class="bx bx-show"></i>
</a>
<a href="{{ route('college.fee-groups.edit', $feeGroup->hashid) }}" class="btn btn-warning btn-sm" title="Edit">
    <i class="bx bx-edit"></i>
</a>
<form action="{{ route('college.fee-groups.destroy', $feeGroup->hashid) }}" method="POST" class="d-inline delete-form">
    @csrf
    @method('DELETE')
    <button type="submit" class="btn btn-danger btn-sm" title="Delete" data-name="{{ $feeGroup->name }}">
        <i class="bx bx-trash"></i>
    </button>
</form>