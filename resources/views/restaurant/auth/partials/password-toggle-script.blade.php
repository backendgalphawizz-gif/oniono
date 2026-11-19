<script>
    document.querySelectorAll('[data-password-toggle]').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var wrap = btn.closest('.auth-input-wrap');
            var input = wrap.querySelector('input[type="password"], input[type="text"].js-password-field');
            if (!input) {
                input = wrap.querySelector('input[name="password"], input[name="password_confirmation"]');
            }
            var icon = btn.querySelector('i');
            var visible = input.type === 'text';

            input.type = visible ? 'password' : 'text';
            icon.classList.toggle('bi-eye', visible);
            icon.classList.toggle('bi-eye-slash', !visible);
            btn.setAttribute('aria-label', visible ? 'Show password' : 'Hide password');
            btn.setAttribute('aria-pressed', visible ? 'false' : 'true');
        });
    });
</script>
