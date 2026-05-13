<?php

/**
 * Helper Functions for UI Rendering
 * 
 * Provides reusable helper functions for rendering common UI components
 * like stat cards, alerts, form fields, etc.
 */

/**
 * Render a stat card for dashboard display
 * 
 * @param string $title Card title
 * @param int|string $value Stat value
 * @param string $color Bootstrap color class (primary, success, warning, danger, info, secondary)
 * @param string $icon Optional Font Awesome icon class
 * @param string $href Optional URL for clickable card
 * @return string HTML for stat card
 */
function renderStatCard(string $title, int|string $value, string $color = 'primary', string $icon = '', string $href = ''): string
{
    $classes = "stat-card stat-card-{$color}";
    $clickable = $href !== '' ? " stat-card-clickable" : '';
    $href_attr = $href !== '' ? " href=\"{$href}\"" : '';
    $tag = $href !== '' ? 'a' : 'div';

    $icon_html = '';
    if ($icon !== '') {
        $icon_html = "<div class=\"stat-card-icon-circle\"><i class=\"{$icon}\"></i></div>";
    } else {
        // Default icon placeholder if none provided
        $icon_html = "<div class=\"stat-card-icon-circle\"><i class=\"fa-solid fa-chart-simple\"></i></div>";
    }

    return <<<HTML
    <{$tag} class="{$classes}{$clickable}"{$href_attr}>
        {$icon_html}
        <div class="stat-card-content">
            <div class="stat-card-value">{$value}</div>
            <div class="stat-card-title">{$title}</div>
        </div>
    </{$tag}>
HTML;
}

/**
 * Render multiple stat cards in a grid
 * 
 * @param array $stats Array of stat data: [['title' => string, 'value' => int, 'color' => string, 'icon' => string]]
 * @return string HTML for stat cards grid
 */
function renderStatGrid(array $stats): string
{
    $cards = array_map(static function (array $stat): string {
        return renderStatCard(
            $stat['title'] ?? '',
            $stat['value'] ?? 0,
            $stat['color'] ?? 'primary',
            $stat['icon'] ?? '',
            $stat['href'] ?? ''
        );
    }, $stats);

    $cardsHtml = implode("\n", $cards);

    return <<<HTML
    <div class="stat-grid">
        {$cardsHtml}
    </div>
HTML;
}

/**
 * Render a success alert
 * 
 * @param string $message Alert message
 * @param bool $dismissible Whether alert can be dismissed
 * @return string HTML for alert
 */
function renderSuccessAlert(string $message, bool $dismissible = true): string
{
    if ($message === '') {
        return '';
    }

    $dismissBtn = $dismissible ? '<button type="button" class="btn-close" data-bs-dismiss="alert"></button>' : '';

    return <<<HTML
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        {$message}
        {$dismissBtn}
    </div>
HTML;
}

/**
 * Render an error alert
 * 
 * @param string $message Alert message
 * @param bool $dismissible Whether alert can be dismissed
 * @return string HTML for alert
 */
function renderErrorAlert(string $message, bool $dismissible = true): string
{
    if ($message === '') {
        return '';
    }

    $dismissBtn = $dismissible ? '<button type="button" class="btn-close" data-bs-dismiss="alert"></button>' : '';

    return <<<HTML
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        {$message}
        {$dismissBtn}
    </div>
HTML;
}

/**
 * Render an info alert
 * 
 * @param string $message Alert message
 * @param bool $dismissible Whether alert can be dismissed
 * @return string HTML for alert
 */
function renderInfoAlert(string $message, bool $dismissible = true): string
{
    if ($message === '') {
        return '';
    }

    $dismissBtn = $dismissible ? '<button type="button" class="btn-close" data-bs-dismiss="alert"></button>' : '';

    return <<<HTML
    <div class="alert alert-info alert-dismissible fade show" role="alert">
        {$message}
        {$dismissBtn}
    </div>
HTML;
}

/**
 * Render a form group with label, input, and error container
 * 
 * @param string $name Field name
 * @param string $label Field label
 * @param string $type Input type (text, email, number, datetime-local, textarea, select)
 * @param mixed $value Current value
 * @param array $options Additional options: placeholder, required, min, max, etc.
 * @param string $error Error message to display
 * @return string HTML for form group
 */
function renderFormField(string $name, string $label, string $type = 'text', mixed $value = '', array $options = [], string $error = ''): string
{
    $required = $options['required'] ?? false ? ' required' : '';
    $placeholder = $options['placeholder'] ?? '';
    $placeholder_attr = $placeholder !== '' ? " placeholder=\"{$placeholder}\"" : '';
    $class = $error !== '' ? ' form-control is-invalid' : ' form-control';
    $error_html = '';

    if ($error !== '') {
        $error_html = "<small class=\"d-block mt-1 text-danger validation-error\">{$error}</small>";
    } else {
        $error_html = "<small class=\"d-block mt-1 text-danger validation-error d-none\"></small>";
    }

    $fieldHtml = '';

    if ($type === 'textarea') {
        $rows = $options['rows'] ?? 4;
        $fieldHtml = "<textarea class=\"{$class}\" name=\"{$name}\" id=\"{$name}\"{$required}{$placeholder_attr} rows=\"{$rows}\">" . htmlspecialchars((string) $value) . "</textarea>";
    } elseif ($type === 'select') {
        $optionsArray = $options['options'] ?? [];
        $optionsHtml = '';
        foreach ($optionsArray as $optionValue => $optionLabel) {
            $selected = ((string) $value === (string) $optionValue) ? ' selected' : '';
            $optionsHtml .= "<option value=\"{$optionValue}\"{$selected}>{$optionLabel}</option>";
        }
        $fieldHtml = "<select class=\"{$class}\" name=\"{$name}\" id=\"{$name}\"{$required}>{$optionsHtml}</select>";
    } else {
        $min_attr = isset($options['min']) ? " min=\"{$options['min']}\"" : '';
        $max_attr = isset($options['max']) ? " max=\"{$options['max']}\"" : '';
        $step_attr = isset($options['step']) ? ' step="' . htmlspecialchars((string) $options['step'], ENT_QUOTES, 'UTF-8') . '"' : '';
        $fieldHtml = "<input type=\"{$type}\" class=\"{$class}\" name=\"{$name}\" id=\"{$name}\" value=\"" . htmlspecialchars((string) $value) . "\"{$required}{$placeholder_attr}{$min_attr}{$max_attr}{$step_attr}>";
    }

    return <<<HTML
    <div class="form-group mb-3">
        <label for="{$name}" class="form-label">{$label}</label>
        {$fieldHtml}
        {$error_html}
    </div>
HTML;
}

/**
 * Render a checkbox form field
 * 
 * @param string $name Field name
 * @param string $label Field label
 * @param bool $checked Whether checkbox is checked
 * @param string $value Checkbox value
 * @return string HTML for checkbox
 */
function renderCheckboxField(string $name, string $label, bool $checked = false, string $value = 'on'): string
{
    $checked_attr = $checked ? ' checked' : '';

    return <<<HTML
    <div class="form-check mb-3">
        <input type="checkbox" class="form-check-input" name="{$name}" id="{$name}" value="{$value}"{$checked_attr}>
        <label class="form-check-label" for="{$name}">
            {$label}
        </label>
    </div>
HTML;
}

/**
 * Render a select field with options
 * 
 * @param string $name Field name
 * @param string $label Field label
 * @param array $options Options array: [value => label]
 * @param mixed $selected Currently selected value
 * @param bool $required Whether field is required
 * @param string $error Error message
 * @return string HTML for select field
 */
function renderSelectField(string $name, string $label, array $options, mixed $selected = '', bool $required = false, string $error = ''): string
{
    $required_attr = $required ? ' required' : '';
    $class = $error !== '' ? ' form-select is-invalid' : ' form-select';
    
    $optionsHtml = '<option value="">-- Sélectionner --</option>';
    foreach ($options as $value => $label_opt) {
        $selected_attr = ((string) $selected === (string) $value) ? ' selected' : '';
        $optionsHtml .= "<option value=\"{$value}\"{$selected_attr}>{$label_opt}</option>";
    }

    $error_html = '';
    if ($error !== '') {
        $error_html = "<small class=\"d-block mt-1 text-danger\">{$error}</small>";
    }

    return <<<HTML
    <div class="form-group mb-3">
        <label for="{$name}" class="form-label">{$label}</label>
        <select class="{$class}" name="{$name}" id="{$name}"{$required_attr}>
            {$optionsHtml}
        </select>
        {$error_html}
    </div>
HTML;
}

/**
 * Render a form group section header (visual grouping)
 * 
 * @param string $title Section title
 * @return string HTML for section header
 */
function renderFormSection(string $title): string
{
    return <<<HTML
    <div class="form-section-header mt-4 mb-3">
        <h5 class="text-secondary">{$title}</h5>
        <hr>
    </div>
HTML;
}

/**
 * Format a date for display (French format)
 * 
 * @param string|int|null $date Date string or timestamp
 * @param string $format Date format
 * @return string Formatted date
 */
function formatDate(string|int|null $date, string $format = 'd/m/Y H:i'): string
{
    if ($date === null || $date === '') {
        return '-';
    }

    if (is_string($date)) {
        $timestamp = strtotime($date);
    } else {
        $timestamp = (int) $date;
    }

    if ($timestamp === false) {
        return '-';
    }

    return date($format, $timestamp);
}

/**
 * Get status badge HTML (Bootstrap color mapping)
 * 
 * @param string $status Status value
 * @param array $statusMapping Status to color mapping
 * @return string HTML badge
 */
function renderStatusBadge(string $status, array $statusMapping = []): string
{
    $defaultMapping = [
        'en_attente' => 'warning',
        'approuve' => 'success',
        'rejete' => 'danger',
        'planifie' => 'secondary',
        'ouvert' => 'success',
        'complet' => 'warning',
        'termine' => 'dark',
        'annule' => 'danger',
    ];

    $mapping = array_merge($defaultMapping, $statusMapping);
    $color = $mapping[$status] ?? 'secondary';
    $label = ucfirst($status);

    return "<span class=\"badge bg-{$color}\">{$label}</span>";
}

/**
 * Render action buttons group (for tables)
 * 
 * @param array $actions Actions array: [['url' => string, 'label' => string, 'class' => string, 'confirm' => bool]]
 * @return string HTML for button group
 */
function renderActionButtons(array $actions): string
{
    $buttons = array_map(static function (array $action): string {
        $url = $action['url'] ?? '#';
        $label = $action['label'] ?? 'Action';
        $class = $action['class'] ?? 'btn-sm btn-primary';
        $confirm = $action['confirm'] ?? false;
        $confirm_attr = $confirm ? ' onclick="return confirm(\'Êtes-vous sûr ?\');"' : '';

        return "<a href=\"{$url}\" class=\"btn {$class}\"{$confirm_attr}>{$label}</a>";
    }, $actions);

    return '<div class="btn-group btn-group-sm" role="group">' . implode(' ', $buttons) . '</div>';
}

/**
 * Public URL for a stored profile photo path, or null.
 * Supports Model/uploads/profile_pics/ (current) and View/shared/assets/profile-pics/ (legacy DB rows).
 *
 * @param object $controller Controller instance exposing url(string $path)
 */
function profile_photo_public_url(string $photoProfil, object $controller): ?string
{
    $p = trim($photoProfil);
    $allowed = [
        'Model/uploads/profile_pics/',
        'View/shared/assets/profile-pics/',
    ];
    $ok = false;
    foreach ($allowed as $prefix) {
        if ($p !== '' && str_starts_with($p, $prefix)) {
            $ok = true;
            break;
        }
    }
    if (!$ok) {
        return null;
    }
    if (!method_exists($controller, 'url')) {
        return null;
    }

    return $controller->url('/' . $p);
}

/**
 * Single-letter avatar fallback (UTF-8 when mbstring is available).
 */
function profile_avatar_initial(string $prenom, string $nom): string
{
    $p = trim($prenom);
    $n = trim($nom);
    $source = $p !== '' ? $p : ($n !== '' ? $n : 'U');
    if (function_exists('mb_substr')) {
        $ch = mb_substr($source, 0, 1, 'UTF-8');

        return function_exists('mb_strtoupper') ? mb_strtoupper($ch, 'UTF-8') : strtoupper($ch);
    }

    return strtoupper(substr($source, 0, 1));
}
