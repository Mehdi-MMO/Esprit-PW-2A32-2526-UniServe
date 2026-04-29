document.addEventListener('DOMContentLoaded', function () {
    var html = document.documentElement;
    var rawDomains = (html && html.dataset && html.dataset.institutionalEmailDomains)
        ? html.dataset.institutionalEmailDomains
        : '';

    var allowedDomains = rawDomains
        .split(',')
        .map(function (domain) {
            return domain.trim().toLowerCase();
        })
        .filter(function (domain) {
            return domain !== '';
        });

    if (allowedDomains.length === 0) {
        allowedDomains = ['universite.tld'];
    }

    function isInstitutionalEmail(email) {
        var normalized = String(email || '').trim().toLowerCase();
        var emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

        if (!emailRegex.test(normalized)) {
            return false;
        }

        var domain = normalized.split('@').pop();
        if (!domain) {
            return false;
        }

        return allowedDomains.some(function (allowedDomain) {
            return domain === allowedDomain || domain.endsWith('.' + allowedDomain);
        });
    }

    function setFieldError(field, message) {
        field.setCustomValidity(message || '');
    }

    function clearFieldError(field) {
        field.setCustomValidity('');
    }

    function requiredLabel(field) {
        var customLabel = field.getAttribute('data-required-label');
        if (customLabel) {
            return customLabel;
        }
        return 'Ce champ';
    }

    function validateField(field, form) {
        clearFieldError(field);

        if (field.hasAttribute('required') && String(field.value || '').trim() === '') {
            setFieldError(field, requiredLabel(field) + ' est obligatoire.');
            return false;
        }

        if (field.dataset && field.dataset.validateEmail === 'institutional' && String(field.value || '').trim() !== '') {
            var normalizedEmail = String(field.value || '').trim().toLowerCase();
            field.value = normalizedEmail;

            if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(normalizedEmail)) {
                setFieldError(field, 'Format email invalide.');
                return false;
            }

            if (!isInstitutionalEmail(normalizedEmail)) {
                setFieldError(field, 'Utilisez une adresse email institutionnelle.');
                return false;
            }
        }

        if (field.dataset && field.dataset.validatePasswordMin) {
            var min = Number(field.dataset.validatePasswordMin) || 0;
            var value = String(field.value || '');
            var isRequired = field.hasAttribute('required');
            if ((isRequired || value !== '') && value.length < min) {
                var label = field.getAttribute('data-password-label') || 'Le mot de passe';
                setFieldError(field, label + ' doit contenir au moins ' + min + ' caractères.');
                return false;
            }
        }

        if (field.dataset && field.dataset.validatePasswordConfirm) {
            var target = form.querySelector(field.dataset.validatePasswordConfirm);
            var confirmValue = String(field.value || '');
            if (target) {
                var targetValue = String(target.value || '');
                var shouldValidate = confirmValue !== '' || targetValue !== '' || field.hasAttribute('required');
                if (shouldValidate && confirmValue !== targetValue) {
                    setFieldError(field, 'Le nouveau mot de passe et sa confirmation ne correspondent pas.');
                    return false;
                }
            }
        }

        return field.checkValidity();
    }

    function validateForm(form) {
        var fields = form.querySelectorAll('input, select, textarea');
        var isValid = true;

        fields.forEach(function (field) {
            if (!validateField(field, form)) {
                isValid = false;
            }
        });

        form.classList.add('was-validated');
        return isValid;
    }

    var forms = document.querySelectorAll('[data-validate-account-form="1"]');
    forms.forEach(function (form) {
        form.setAttribute('novalidate', 'novalidate');

        form.querySelectorAll('input, select, textarea').forEach(function (field) {
            field.addEventListener('input', function () {
                validateField(field, form);
            });
            field.addEventListener('change', function () {
                validateField(field, form);
            });
        });

        form.addEventListener('submit', function (event) {
            if (!validateForm(form)) {
                event.preventDefault();
                event.stopPropagation();
            }
        });
    });
});
