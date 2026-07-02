<script>
(function () {
    function formatTzPhoneValue(raw) {
        let digits = String(raw || '').replace(/\D/g, '');
        if (digits === '') {
            return '';
        }
        if (digits.startsWith('0')) {
            digits = '255' + digits.slice(1);
        } else if (!digits.startsWith('255')) {
            digits = '255' + digits;
        }
        return digits.slice(0, 12);
    }

    function attachTzPhoneFormatter(input) {
        if (!input || input.dataset.tzPhoneBound === '1') {
            return;
        }
        input.dataset.tzPhoneBound = '1';
        input.setAttribute('inputmode', 'numeric');
        input.setAttribute('autocomplete', 'tel');
        input.setAttribute('maxlength', '12');

        const applyFormat = function () {
            const formatted = formatTzPhoneValue(input.value);
            if (input.value !== formatted) {
                input.value = formatted;
            }
        };

        input.addEventListener('input', applyFormat);
        input.addEventListener('blur', applyFormat);
        input.addEventListener('paste', function () {
            setTimeout(applyFormat, 0);
        });

        if (input.value) {
            applyFormat();
        }
    }

    document.querySelectorAll('.tz-phone-input').forEach(attachTzPhoneFormatter);
})();
</script>
