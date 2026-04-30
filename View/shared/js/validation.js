/**
 * Form Validation Module
 * 
 * Provides real-time client-side validation for clubs and events forms.
 * Displays inline error messages and prevents invalid form submission.
 */

class FormValidator {
    constructor(formSelector, validationRules = {}) {
        this.form = document.querySelector(formSelector);
        this.validationRules = validationRules;
        this.errors = {};
        
        if (this.form) {
            this.init();
        }
    }

    /**
     * Initialize form validation listeners
     */
    init() {
        // Validate on input with debouncing
        this.form.querySelectorAll('input, textarea, select').forEach((field) => {
            field.addEventListener('blur', () => this.validateField(field));
            field.addEventListener('input', () => this.debounce(() => this.validateField(field), 300));
        });

        // Prevent form submission if there are errors
        this.form.addEventListener('submit', (event) => {
            if (!this.validateForm()) {
                event.preventDefault();
                return false;
            }
        });
    }

    /**
     * Validate a single field
     */
    validateField(field) {
        const fieldName = field.name;
        const fieldValue = field.value.trim();
        const rules = this.validationRules[fieldName] || {};

        const errorMessage = this.getErrorMessage(fieldName, fieldValue, rules);
        this.setFieldError(field, errorMessage);

        return errorMessage === null;
    }

    /**
     * Validate entire form
     */
    validateForm() {
        let isValid = true;

        this.form.querySelectorAll('input, textarea, select').forEach((field) => {
            if (!this.validateField(field)) {
                isValid = false;
            }
        });

        return isValid;
    }

    /**
     * Get validation error message for a field
     */
    getErrorMessage(fieldName, fieldValue, rules) {
        // Check required
        if (rules.required && fieldValue === '') {
            return `Le champ ${fieldName} est obligatoire.`;
        }

        // If not required and empty, skip other validations
        if (!rules.required && fieldValue === '') {
            return null;
        }

        // Check minlength
        if (rules.minlength && fieldValue.length < rules.minlength) {
            return `${fieldName} doit contenir au moins ${rules.minlength} caractères.`;
        }

        // Check maxlength
        if (rules.maxlength && fieldValue.length > rules.maxlength) {
            return `${fieldName} ne doit pas dépasser ${rules.maxlength} caractères.`;
        }

        // Check email format
        if (rules.type === 'email' && fieldValue !== '') {
            if (!this.isValidEmail(fieldValue)) {
                return `${fieldName} doit être une adresse email valide.`;
            }
        }

        // Check number constraints
        if (rules.type === 'number' && fieldValue !== '') {
            const numValue = parseInt(fieldValue, 10);
            if (isNaN(numValue)) {
                return `${fieldName} doit être un nombre.`;
            }
            if (rules.min !== undefined && numValue < rules.min) {
                return `${fieldName} doit être au moins ${rules.min}.`;
            }
            if (rules.max !== undefined && numValue > rules.max) {
                return `${fieldName} ne doit pas dépasser ${rules.max}.`;
            }
        }

        // Check datetime-local format
        if (rules.type === 'datetime-local' && fieldValue !== '') {
            if (!this.isValidDateTime(fieldValue)) {
                return `${fieldName} doit être une date/heure valide.`;
            }
        }

        return null;
    }

    /**
     * Check if email format is valid
     */
    isValidEmail(email) {
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return emailRegex.test(email);
    }

    /**
     * Check if datetime format is valid
     */
    isValidDateTime(dateTimeString) {
        // Accept datetime-local format or standard datetime
        const dateTimeRegex = /^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}$|^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$|^\d{4}-\d{2}-\d{2}$/;
        if (!dateTimeRegex.test(dateTimeString)) {
            return false;
        }

        // Try parsing with Date
        const date = new Date(dateTimeString);
        return !isNaN(date.getTime());
    }

    /**
     * Set or clear error for a field
     */
    setFieldError(field, errorMessage) {
        const errorContainer = field.parentElement.querySelector('.validation-error');

        if (errorMessage) {
            field.classList.add('is-invalid');
            if (errorContainer) {
                errorContainer.textContent = errorMessage;
                errorContainer.classList.remove('d-none');
            }
        } else {
            field.classList.remove('is-invalid');
            if (errorContainer) {
                errorContainer.textContent = '';
                errorContainer.classList.add('d-none');
            }
        }
    }

    /**
     * Debounce function for input events
     */
    debounce(func, delay) {
        clearTimeout(this.debounceTimer);
        this.debounceTimer = setTimeout(func, delay);
    }
}

/**
 * Initialize validators on page load
 */
document.addEventListener('DOMContentLoaded', function () {
    // Club form validation
    const clubForm = document.querySelector('form[id*="club"]');
    if (clubForm) {
        // Rules defined in the form or can be passed from PHP
        const rules = {
            nom: {
                required: true,
                minlength: 3,
                maxlength: 100,
            },
            email_contact: {
                required: false,
                type: 'email',
            },
            description: {
                required: false,
                maxlength: 500,
            },
        };
        new FormValidator('form[id*="club"]', rules);
    }

    // Event form validation
    const eventForm = document.querySelector('form[id*="event"]');
    if (eventForm) {
        const rules = {
            titre: {
                required: true,
                minlength: 5,
                maxlength: 150,
            },
            lieu: {
                required: true,
                minlength: 3,
                maxlength: 150,
            },
            date_debut: {
                required: true,
                type: 'datetime-local',
            },
            date_fin: {
                required: true,
                type: 'datetime-local',
            },
            description: {
                required: false,
                maxlength: 500,
            },
            capacite: {
                required: false,
                type: 'number',
                min: 1,
                max: 500,
            },
        };
        new FormValidator('form[id*="event"]', rules);
    }
});

/**
 * Helper: Format validation messages in French
 */
function getLocalizedFieldName(fieldName) {
    const translations = {
        nom: 'Nom',
        email_contact: 'Email de contact',
        description: 'Description',
        titre: 'Titre',
        lieu: 'Lieu',
        date_debut: 'Date de début',
        date_fin: 'Date de fin',
        capacite: 'Capacité',
        club_id: 'Club',
        actif: 'Actif',
        statut: 'Statut',
    };
    return translations[fieldName] || fieldName;
}
