<div class="btn-group" role="group">
    <a href="{{ route('school.streams.show', $stream) }}" class="btn btn-sm btn-outline-info" title="View">
        <i class="bx bx-show"></i>
    </a>
    <a href="{{ route('school.streams.edit', $stream) }}" class="btn btn-sm btn-outline-warning" title="Edit">
        <i class="bx bx-edit"></i>
    </a>
    <button type="button" class="btn btn-sm btn-danger delete-stream-btn" title="Delete"
            data-stream-id="{{ $stream->getRouteKey() }}"
            data-stream-name="{{ $stream->name }}"
            data-classes-count="{{ $stream->classes_count ?? 0 }}">
        <i class="bx bx-trash"></i>
    </button>
</div>