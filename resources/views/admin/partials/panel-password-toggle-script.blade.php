<script>
    document.querySelectorAll('.panel-password-wrap [data-password-toggle]').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var wrap = btn.closest('.panel-password-wrap');
            var input = wrap && wrap.querySelector('input');
            if (!input) {
                return;
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
