<div class="btn-group" role="group">
    <a href="{{ route('school.exam-schedules.show', $schedule->hashid) }}" 
       class="btn btn-sm btn-info" 
       data-bs-toggle="tooltip" 
       title="View Details">
        <i class="bx bx-show"></i>
    </a>
    <a href="{{ route('school.exam-schedules.edit', $schedule->hashid) }}" 
       class="btn btn-sm btn-warning" 
       data-bs-toggle="tooltip" 
       title="Edit">
        <i class="bx bx-edit"></i>
    </a>
    @if($schedule->status !== 'published' && $schedule->status !== 'completed' && $schedule->status !== 'cancelled')
    <button type="button" 
            class="btn btn-sm btn-success publish-btn" 
            data-schedule-id="{{ $schedule->hashid }}"
            data-bs-toggle="tooltip" 
            title="Publish to Parents">
        <i class="bx bx-send"></i>
    </button>
    @endif
    <form action="{{ route('school.exam-schedules.destroy', $schedule->hashid) }}" 
          method="POST" 
          class="d-inline"
          onsubmit="return confirm('Are you sure you want to delete this exam schedule?');">
        @csrf
        @method('DELETE')
        <button type="submit" 
                class="btn btn-sm btn-danger" 
                data-bs-toggle="tooltip" 
                title="Delete">
            <i class="bx bx-trash"></i>
        </button>
    </form>
</div>

