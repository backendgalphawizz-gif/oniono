<script>
    (function () {
        var csrf = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        var phoneInput = document.getElementById('phone');
        var otpInput = document.getElementById('otp');
        var statusEl = document.getElementById('otp-status');
        var form = document.getElementById('restaurant-register-step1-form');
        var verified = @json($phoneVerified ?? false);

        function postJson(url, body) {
            return fetch(url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrf,
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify(body)
            }).then(function (r) { return r.json().then(function (data) { return { ok: r.ok, data: data }; }); });
        }

        function setStatus(message, isError) {
            statusEl.textContent = message || '';
            statusEl.className = 'small mb-0 mt-2' + (isError ? ' text-danger' : ' text-success');
        }

        if (verified) {
            setStatus('Mobile number verified.', false);
        }

        phoneInput.addEventListener('input', function () {
            verified = false;
            setStatus('', false);
        });

        document.getElementById('btn-send-otp').addEventListener('click', function () {
            setStatus('Sending…', false);
            verified = false;
            postJson('{{ route('restaurant.register.otp.send') }}', { phone: phoneInput.value })
                .then(function (res) {
                    if (res.data.debug_code) {
                        otpInput.value = res.data.debug_code;
                    }
                    setStatus(res.data.message || (res.ok ? 'OTP sent.' : 'Could not send OTP.'), !res.ok);
                })
                .catch(function () { setStatus('Network error. Try again.', true); });
        });

        document.getElementById('btn-verify-otp').addEventListener('click', function () {
            setStatus('Verifying…', false);
            postJson('{{ route('restaurant.register.otp.verify') }}', { phone: phoneInput.value, otp: otpInput.value })
                .then(function (res) {
                    verified = res.ok;
                    setStatus(res.data.message || (res.ok ? 'Verified.' : 'Verification failed.'), !res.ok);
                })
                .catch(function () { setStatus('Network error. Try again.', true); });
        });

        if (form) {
            form.addEventListener('submit', function (e) {
                if (!verified) {
                    e.preventDefault();
                    setStatus('Verify your mobile number with OTP before continuing.', true);
                    phoneInput.focus();
                }
            });
        }
    })();
</script>
