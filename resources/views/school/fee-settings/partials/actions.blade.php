@if($feeSetting)
<a href="{{ route('school.fee-settings.show', $feeSetting->hashid) }}" class="btn btn-info btn-sm" title="View">
    <i class="bx bx-show"></i>
</a>
<a href="{{ route('school.fee-settings.edit', $feeSetting->hashid) }}" class="btn btn-warning btn-sm" title="Edit">
    <i class="bx bx-edit"></i>
</a>
<form action="{{ route('school.fee-settings.destroy', $feeSetting->hashid) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this fee setting? This action cannot be undone.')">
    @csrf
    @method('DELETE')
    <button type="submit" class="btn btn-danger btn-sm" title="Delete">
        <i class="bx bx-trash"></i>
    </button>
</form>
@endif