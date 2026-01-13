@php
    $showUrl = route('college.document-categories.show', $category->id);
    $editUrl = route('college.document-categories.edit', $category->id);
    $deleteUrl = route('college.document-categories.destroy', $category->id);
@endphp

<div class="btn-group" role="group">
    <a href="{{ $showUrl }}" class="btn btn-sm btn-info" title="View Details">
        <i class="bx bx-show"></i>
    </a>
    <a href="{{ $editUrl }}" class="btn btn-sm btn-warning" title="Edit Category">
        <i class="bx bx-edit"></i>
    </a>
    <button type="button" class="btn btn-sm btn-danger delete-btn"
            data-name="{{ $category->name }}"
            data-url="{{ $deleteUrl }}"
            title="Delete Category">
        <i class="bx bx-trash"></i>
    </button>
</div>