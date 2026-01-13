{{--
    Actions partial for exams DataTable
    @param object $exam The exam object
--}}

<div class="btn-group" role="group">
    <a href="{{ route('school.exams.show', $exam) }}"
       class="btn btn-sm btn-outline-info"
       title="View Details">
        <i class="bx bx-show"></i>
    </a>

    <a href="{{ route('school.exams.edit', $exam) }}"
       class="btn btn-sm btn-outline-warning"
       title="Edit Exam">
        <i class="bx bx-edit"></i>
    </a>

    <div class="dropdown">
        <button class="btn btn-sm btn-outline-secondary dropdown-toggle"
                type="button"
                data-bs-toggle="dropdown"
                aria-expanded="false"
                title="More Actions">
            <i class="bx bx-dots-vertical-rounded"></i>
        </button>
        <ul class="dropdown-menu">
    <a class="dropdown-item status-update-btn"
       href="#"
       data-id="{{ $exam->id }}"
       data-url="{{ route('school.exams.update-status', $exam) }}"
       data-status="scheduled">
        <i class="bx bx-calendar-check me-1"></i> Mark Scheduled
    </a>
    <li>
        <a class="dropdown-item status-update-btn"
           href="#"
           data-id="{{ $exam->id }}"
           data-url="{{ route('school.exams.update-status', $exam) }}"
           data-status="ongoing">
            <i class="bx bx-play me-1"></i> Mark Ongoing
        </a>
    </li>
    <li>
        <a class="dropdown-item status-update-btn"
           href="#"
           data-id="{{ $exam->id }}"
           data-url="{{ route('school.exams.update-status', $exam) }}"
           data-status="completed">
            <i class="bx bx-check me-1"></i> Mark Completed
        </a>
    </li>
            <li><hr class="dropdown-divider"></li>
            <li>
                <a class="dropdown-item text-danger"
                   href="#"
                   onclick="confirmDelete({{ $exam->id }}, '{{ addslashes($exam->exam_name) }}')">
                    <i class="bx bx-trash me-1"></i> Delete
                </a>
            </li>
        </ul>
    </div>
</div>

<script>
function confirmDelete(examId, examName) {
    if (confirm(`Are you sure you want to delete the exam "${examName}"? This action cannot be undone.`)) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = `/school/exams/${examId}`;

        const methodField = document.createElement('input');
        methodField.type = 'hidden';
        methodField.name = '_method';
        methodField.value = 'DELETE';

        const csrfField = document.createElement('input');
        csrfField.type = 'hidden';
        csrfField.name = '_token';
        csrfField.value = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

        form.appendChild(methodField);
        form.appendChild(csrfField);
        document.body.appendChild(form);
        form.submit();
    }
}
</script>