<script>
    document.querySelectorAll('.table-password-toggle').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var field = btn.closest('.table-password-field');
            if (!field) {
                return;
            }

            var valueEl = field.querySelector('.table-password-value');
            var icon = btn.querySelector('i');
            var masked = valueEl.getAttribute('data-masked') || '••••••••';
            var plain = btn.getAttribute('data-password') || '';
            var revealed = valueEl.getAttribute('data-revealed') === '1';

            if (revealed) {
                valueEl.textContent = masked;
                valueEl.setAttribute('data-revealed', '0');
                icon.classList.add('bi-eye');
                icon.classList.remove('bi-eye-slash');
                btn.setAttribute('aria-label', 'Show password');
                btn.setAttribute('aria-pressed', 'false');
            } else {
                valueEl.textContent = plain;
                valueEl.setAttribute('data-revealed', '1');
                icon.classList.remove('bi-eye');
                icon.classList.add('bi-eye-slash');
                btn.setAttribute('aria-label', 'Hide password');
                btn.setAttribute('aria-pressed', 'true');
            }
        });
    });
</script>
