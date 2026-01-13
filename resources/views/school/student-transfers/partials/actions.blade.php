<div class="btn-group" role="group">
    <a href="{{ route('school.student-transfers.show', $transfer->getRouteKey()) }}"
       class="btn btn-sm btn-outline-primary"
       title="View Transfer Details">
        <i class="bx bx-show"></i>
    </a>
    <a href="{{ route('school.student-transfers.print', $transfer->getRouteKey()) }}"
       class="btn btn-sm btn-outline-info"
       title="Print Transfer Record"
       target="_blank">
        <i class="bx bx-printer"></i>
    </a>
    <a href="{{ route('school.student-transfers.pdf', $transfer->getRouteKey()) }}"
       class="btn btn-sm btn-outline-success"
       title="Download PDF"
       target="_blank">
        <i class="bx bx-download"></i>
    </a>
    <a href="{{ route('school.student-transfers.pdf-preview', $transfer->getRouteKey()) }}"
       class="btn btn-sm btn-outline-secondary"
       title="Preview PDF"
       target="_blank">
        <i class="bx bx-file"></i>
    </a>
    <a href="{{ route('school.student-transfers.edit', $transfer->getRouteKey()) }}"
       class="btn btn-sm btn-outline-warning"
       title="Edit Transfer">
        <i class="bx bx-edit"></i>
    </a>
    <form action="{{ route('school.student-transfers.destroy', $transfer->getRouteKey()) }}"
          method="POST"
          class="d-inline"
          onsubmit="return confirm('Are you sure you want to delete this transfer record? This action cannot be undone.')">
        @csrf
        @method('DELETE')
        <button type="submit"
                class="btn btn-sm btn-danger"
                title="Delete Transfer">
            <i class="bx bx-trash"></i>
        </button>
    </form>
</div>