<section class="container py-5">
    <div class="row justify-content-center">
        <div class="col-12 col-md-8 col-lg-6">
            <div class="us-card p-4 p-md-4">
                <h1 class="h4 mb-2">Verification OTP</h1>
                <p class="text-muted small mb-3">
                    Saisissez le code a 6 chiffres envoye a
                    <strong><?= htmlspecialchars((string) ($email ?? ''), ENT_QUOTES, 'UTF-8') ?></strong>.
                </p>

                <?php if (!empty($error)): ?>
                    <div class="alert alert-danger py-2 small mb-3" role="alert">
                        <?= htmlspecialchars((string) $error, ENT_QUOTES, 'UTF-8') ?>
                    </div>
                <?php endif; ?>

                <form method="post" action="<?= $this->url('/auth/verify/' . (string) ($requestToken ?? '')) ?>" id="otpForm">
                    <input type="hidden" id="otp" name="otp" value="">
                    <div class="d-flex justify-content-between gap-2 mb-3" id="otpInputs">
                        <?php for ($i = 0; $i < 6; $i++): ?>
                            <input
                                type="text"
                                class="form-control text-center otp-input"
                                inputmode="numeric"
                                pattern="[0-9]*"
                                maxlength="1"
                                autocomplete="one-time-code"
                                aria-label="Chiffre OTP <?= $i + 1 ?>"
                                required
                            >
                        <?php endfor; ?>
                    </div>
                    <button type="submit" class="btn btn-primary w-100">Verifier le code</button>
                </form>

                <form method="post" action="<?= $this->url('/auth/forgot') ?>" class="mt-3">
                    <input type="hidden" name="email" value="<?= htmlspecialchars((string) ($email ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                    <button type="submit" class="btn btn-outline-secondary w-100">Renvoyer le code</button>
                </form>
            </div>
        </div>
    </div>
</section>

<script>
document.addEventListener('DOMContentLoaded', function () {
    var form = document.getElementById('otpForm');
    var wrapper = document.getElementById('otpInputs');
    if (!form || !wrapper) {
        return;
    }

    var inputs = Array.prototype.slice.call(wrapper.querySelectorAll('.otp-input'));
    var hidden = document.getElementById('otp');

    function collectOtp() {
        return inputs.map(function (input) {
            return String(input.value || '').replace(/\D/g, '').slice(0, 1);
        }).join('');
    }

    inputs.forEach(function (input, index) {
        input.addEventListener('input', function () {
            input.value = String(input.value || '').replace(/\D/g, '').slice(0, 1);
            if (input.value !== '' && index < inputs.length - 1) {
                inputs[index + 1].focus();
            }
        });

        input.addEventListener('keydown', function (event) {
            if (event.key === 'Backspace' && input.value === '' && index > 0) {
                inputs[index - 1].focus();
            }
        });

        input.addEventListener('paste', function (event) {
            var pasted = (event.clipboardData || window.clipboardData).getData('text') || '';
            var digits = pasted.replace(/\D/g, '').slice(0, 6).split('');
            if (digits.length === 0) {
                return;
            }

            event.preventDefault();
            digits.forEach(function (digit, digitIndex) {
                if (inputs[digitIndex]) {
                    inputs[digitIndex].value = digit;
                }
            });

            var focusIndex = Math.min(digits.length, inputs.length - 1);
            inputs[focusIndex].focus();
        });
    });

    form.addEventListener('submit', function () {
        hidden.value = collectOtp();
    });
});
</script>
