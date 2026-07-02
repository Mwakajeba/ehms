<div class="modal fade" id="patientSmsModal" tabindex="-1" aria-labelledby="patientSmsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="patientSmsModalLabel">
                    <i class="bx bx-message-rounded-dots me-2"></i>Send SMS to Patients
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="patientSmsForm">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Send type</label>
                        <div class="d-flex gap-3">
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="send_mode" id="sms_mode_single" value="single" checked>
                                <label class="form-check-label" for="sms_mode_single">Single SMS</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="send_mode" id="sms_mode_bulk" value="bulk">
                                <label class="form-check-label" for="sms_mode_bulk">Bulk SMS</label>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3" id="singlePatientWrapper">
                        <label for="patient_sms_patient_id" class="form-label fw-bold">Patient</label>
                        <select id="patient_sms_patient_id" name="patient_id" class="form-select" style="width: 100%;"></select>
                        <div class="form-text">Search by MRN, name, phone, or email.</div>
                    </div>

                    <div class="alert alert-info d-none" id="bulkSmsInfo">
                        <i class="bx bx-info-circle me-1"></i>
                        Bulk SMS will be sent to all active patients with a phone number in the current branch
                        (<strong id="bulkPatientCount">{{ number_format($stats['patients']['with_phone'] ?? 0) }}</strong> patients).
                    </div>

                    <div class="mb-3">
                        <label for="patient_sms_message" class="form-label fw-bold">Message</label>
                        <textarea class="form-control" id="patient_sms_message" name="message" rows="5" maxlength="918" required placeholder="Type your message here..."></textarea>
                        <div class="d-flex justify-content-between align-items-center mt-2 flex-wrap gap-2">
                            <div>
                                <span id="smsCharCount" class="fw-bold">0</span> / 160 characters
                                <span class="text-muted">(current part)</span>
                            </div>
                            <div>
                                <span class="badge bg-secondary" id="smsPartBadge">1 SMS part</span>
                            </div>
                        </div>
                        <div class="form-text" id="smsSegmentHint">Standard SMS: 160 characters per part. Longer messages use multiple parts.</div>
                        <div class="text-warning small mt-1 d-none" id="smsOver160Warning">
                            <i class="bx bx-error-circle"></i> Message exceeds 160 characters and will be billed as multiple SMS parts.
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary" id="patientSmsSubmitBtn">
                        <i class="bx bx-send me-1"></i>Send SMS
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const modal = document.getElementById('patientSmsModal');
    if (!modal) return;

    const form = document.getElementById('patientSmsForm');
    const messageEl = document.getElementById('patient_sms_message');
    const charCountEl = document.getElementById('smsCharCount');
    const partBadgeEl = document.getElementById('smsPartBadge');
    const over160El = document.getElementById('smsOver160Warning');
    const singleWrapper = document.getElementById('singlePatientWrapper');
    const bulkInfo = document.getElementById('bulkSmsInfo');
    const patientSelect = $('#patient_sms_patient_id');
    const submitBtn = document.getElementById('patientSmsSubmitBtn');

    function smsParts(length) {
        return Math.max(1, Math.ceil(length / 160));
    }

    function charsInCurrentPart(length) {
        if (length === 0) return 0;
        const remainder = length % 160;
        return remainder === 0 ? 160 : remainder;
    }

    function updateSmsCounter() {
        const length = messageEl.value.length;
        const parts = smsParts(length);
        const currentPartChars = charsInCurrentPart(length);

        charCountEl.textContent = currentPartChars;
        partBadgeEl.textContent = parts + (parts === 1 ? ' SMS part' : ' SMS parts');
        partBadgeEl.className = 'badge ' + (parts > 1 ? 'bg-warning text-dark' : 'bg-secondary');

        if (length > 160) {
            over160El.classList.remove('d-none');
            charCountEl.classList.add('text-warning');
        } else {
            over160El.classList.add('d-none');
            charCountEl.classList.remove('text-warning');
        }

        if (length > 450) {
            charCountEl.classList.add('text-danger');
        } else if (length <= 160) {
            charCountEl.classList.remove('text-danger');
        }
    }

    function toggleSendMode() {
        const isSingle = document.getElementById('sms_mode_single').checked;
        singleWrapper.classList.toggle('d-none', !isSingle);
        bulkInfo.classList.toggle('d-none', isSingle);
        patientSelect.prop('required', isSingle);
    }

    document.querySelectorAll('input[name="send_mode"]').forEach(function (radio) {
        radio.addEventListener('change', toggleSendMode);
    });

    messageEl.addEventListener('input', updateSmsCounter);

    patientSelect.select2({
        theme: 'bootstrap-5',
        dropdownParent: $('#patientSmsModal'),
        placeholder: 'Search and select a patient...',
        allowClear: true,
        minimumInputLength: 1,
        ajax: {
            url: '{{ route('hospital.sms.patients-search') }}',
            dataType: 'json',
            delay: 250,
            data: function (params) {
                return { q: params.term };
            },
            processResults: function (data) {
                return data;
            },
            cache: true
        }
    });

    modal.addEventListener('shown.bs.modal', function () {
        toggleSendMode();
        updateSmsCounter();
    });

    modal.addEventListener('hidden.bs.modal', function () {
        form.reset();
        patientSelect.val(null).trigger('change');
        updateSmsCounter();
        toggleSendMode();
    });

    form.addEventListener('submit', function (e) {
        e.preventDefault();

        const isSingle = document.getElementById('sms_mode_single').checked;
        if (isSingle && !patientSelect.val()) {
            Swal.fire({ icon: 'warning', title: 'Select a patient', text: 'Please choose a patient for single SMS.' });
            return;
        }

        const formData = new FormData(form);
        const originalHtml = submitBtn.innerHTML;
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="bx bx-loader-alt bx-spin me-1"></i>Sending...';

        fetch('{{ route('hospital.sms.send') }}', {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            },
        })
        .then(response => response.json().then(data => ({ ok: response.ok, data })))
        .then(({ ok, data }) => {
            if (ok && data.success) {
                Swal.fire({
                    icon: 'success',
                    title: 'SMS Sent',
                    text: data.message,
                    timer: 3000,
                    showConfirmButton: true,
                });
                bootstrap.Modal.getInstance(modal).hide();
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Could not send SMS',
                    text: data.message || 'An error occurred.',
                });
            }
        })
        .catch(() => {
            Swal.fire({
                icon: 'error',
                title: 'Connection error',
                text: 'Failed to send SMS. Please try again.',
            });
        })
        .finally(() => {
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalHtml;
        });
    });

    toggleSendMode();
    updateSmsCounter();
});
</script>
@endpush
