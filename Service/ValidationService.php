<?php

/**
 * ValidationService - Centralized validation logic for clubs and events
 * 
 * Provides server-side validation for input data across the application.
 * All validation rules are defined here to maintain DRY principle.
 */

declare(strict_types=1);

class ValidationService
{
    // ===== CLUB VALIDATION RULES =====
    
    private const CLUB_NAME_MIN = 3;
    private const CLUB_NAME_MAX = 100;
    private const CLUB_DESCRIPTION_MAX = 500;
    private const CLUB_EMAIL_REGEX = '/^[^\s@]+@[^\s@]+\.[^\s@]+$/';
    
    // ===== EVENT VALIDATION RULES =====
    
    private const EVENT_TITLE_MIN = 5;
    private const EVENT_TITLE_MAX = 150;
    private const EVENT_DESCRIPTION_MAX = 500;
    private const EVENT_LOCATION_MIN = 3;
    private const EVENT_LOCATION_MAX = 150;
    private const EVENT_CAPACITY_MIN = 1;
    private const EVENT_CAPACITY_MAX = 500;
    
    /**
     * Validate club input data
     * 
     * @param array $data The club data to validate
     * @return array ['valid' => bool, 'errors' => [field => message]]
     */
    public static function validateClubInput(array $data): array
    {
        $errors = [];
        
        // Validate name
        $nom = trim((string) ($data['nom'] ?? ''));
        if ($nom === '') {
            $errors['nom'] = 'Le nom du club est obligatoire.';
        } elseif (strlen($nom) < self::CLUB_NAME_MIN) {
            $errors['nom'] = "Le nom doit contenir au moins " . self::CLUB_NAME_MIN . " caractères.";
        } elseif (strlen($nom) > self::CLUB_NAME_MAX) {
            $errors['nom'] = "Le nom ne doit pas dépasser " . self::CLUB_NAME_MAX . " caractères.";
        }
        
        // Validate email (optional but must be valid if provided)
        $email = trim((string) ($data['email_contact'] ?? ''));
        if ($email !== '' && !self::isValidEmail($email)) {
            $errors['email_contact'] = 'Le format de l\'email est invalide.';
        }
        
        // Validate description (optional but has max length)
        $description = trim((string) ($data['description'] ?? ''));
        if ($description !== '' && strlen($description) > self::CLUB_DESCRIPTION_MAX) {
            $errors['description'] = "La description ne doit pas dépasser " . self::CLUB_DESCRIPTION_MAX . " caractères.";
        }
        
        return [
            'valid' => empty($errors),
            'errors' => $errors,
        ];
    }
    
    /**
     * Validate event input data
     * 
     * @param array $data The event data to validate
     * @param array|null $allowedStatuses Array of allowed status values (optional)
     * @return array ['valid' => bool, 'errors' => [field => message]]
     */
    public static function validateEventInput(array $data, ?array $allowedStatuses = null): array
    {
        $errors = [];
        
        // Validate title
        $titre = trim((string) ($data['titre'] ?? ''));
        if ($titre === '') {
            $errors['titre'] = 'Le titre est obligatoire.';
        } elseif (strlen($titre) < self::EVENT_TITLE_MIN) {
            $errors['titre'] = "Le titre doit contenir au moins " . self::EVENT_TITLE_MIN . " caractères.";
        } elseif (strlen($titre) > self::EVENT_TITLE_MAX) {
            $errors['titre'] = "Le titre ne doit pas dépasser " . self::EVENT_TITLE_MAX . " caractères.";
        }
        
        // Validate location
        $lieu = trim((string) ($data['lieu'] ?? ''));
        if ($lieu === '') {
            $errors['lieu'] = 'Le lieu est obligatoire.';
        } elseif (strlen($lieu) < self::EVENT_LOCATION_MIN) {
            $errors['lieu'] = "Le lieu doit contenir au moins " . self::EVENT_LOCATION_MIN . " caractères.";
        } elseif (strlen($lieu) > self::EVENT_LOCATION_MAX) {
            $errors['lieu'] = "Le lieu ne doit pas dépasser " . self::EVENT_LOCATION_MAX . " caractères.";
        }
        
        // Validate dates
        $dateDebut = trim((string) ($data['date_debut'] ?? ''));
        $dateFin = trim((string) ($data['date_fin'] ?? ''));
        
        if ($dateDebut === '') {
            $errors['date_debut'] = 'La date de début est obligatoire.';
        } elseif (!self::isValidDateTime($dateDebut)) {
            $errors['date_debut'] = 'Format de date invalide.';
        }
        
        if ($dateFin === '') {
            $errors['date_fin'] = 'La date de fin est obligatoire.';
        } elseif (!self::isValidDateTime($dateFin)) {
            $errors['date_fin'] = 'Format de date invalide.';
        }
        
        // Validate date range (if both dates are valid)
        if (empty($errors['date_debut']) && empty($errors['date_fin'])) {
            $beginTime = strtotime((string) $dateDebut);
            $endTime = strtotime((string) $dateFin);
            
            if ($beginTime === false || $endTime === false) {
                $errors['date_fin'] = 'Impossible de valider la plage horaire.';
            } elseif ($endTime < $beginTime) {
                $errors['date_fin'] = 'La date de fin doit être postérieure ou égale à la date de début.';
            }
        }
        
        // Validate description (optional but has max length)
        $description = trim((string) ($data['description'] ?? ''));
        if ($description !== '' && strlen($description) > self::EVENT_DESCRIPTION_MAX) {
            $errors['description'] = "La description ne doit pas dépasser " . self::EVENT_DESCRIPTION_MAX . " caractères.";
        }
        
        // Validate capacity (optional but must be positive)
        $capacite = (string) ($data['capacite'] ?? '');
        if ($capacite !== '') {
            $capaciteInt = (int) $capacite;
            if ($capaciteInt < self::EVENT_CAPACITY_MIN) {
                $errors['capacite'] = "La capacité doit être au moins " . self::EVENT_CAPACITY_MIN . ".";
            } elseif ($capaciteInt > self::EVENT_CAPACITY_MAX) {
                $errors['capacite'] = "La capacité ne doit pas dépasser " . self::EVENT_CAPACITY_MAX . ".";
            }
        }
        
        // Validate status (if statuses provided)
        if ($allowedStatuses !== null) {
            $statut = trim((string) ($data['statut'] ?? ''));
            if ($statut !== '' && !in_array($statut, $allowedStatuses, true)) {
                $errors['statut'] = 'Statut d\'événement invalide.';
            }
        }
        
        return [
            'valid' => empty($errors),
            'errors' => $errors,
        ];
    }
    
    // ===== HELPER VALIDATION METHODS =====
    
    /**
     * Check if email format is valid
     */
    public static function isValidEmail(string $email): bool
    {
        return (bool) preg_match(self::CLUB_EMAIL_REGEX, $email);
    }
    
    /**
     * Check if datetime string is valid
     */
    public static function isValidDateTime(string $dateTime): bool
    {
        // Try standard datetime format
        $date = \DateTimeImmutable::createFromFormat('Y-m-d\TH:i', $dateTime);
        if ($date instanceof \DateTimeImmutable) {
            return true;
        }
        
        // Try ISO datetime format
        $date = \DateTimeImmutable::createFromFormat('Y-m-d H:i:s', $dateTime);
        if ($date instanceof \DateTimeImmutable) {
            return true;
        }
        
        // Try basic date format
        $date = \DateTimeImmutable::createFromFormat('Y-m-d', $dateTime);
        if ($date instanceof \DateTimeImmutable) {
            return true;
        }
        
        // Try strtotime as fallback
        return strtotime($dateTime) !== false;
    }
    
    /**
     * Sanitize string input: trim and truncate
     */
    public static function sanitizeString(string $input, int $maxLength = 255): string
    {
        $trimmed = trim($input);
        return substr($trimmed, 0, $maxLength);
    }
    
    /**
     * Sanitize integer input with min/max bounds
     */
    public static function sanitizeInt(int $input, int $min = PHP_INT_MIN, int $max = PHP_INT_MAX): int
    {
        return max($min, min($max, $input));
    }
    
    /**
     * Convert datetime-local input format to database format
     */
    public static function normalizeDateTimeInput(string $value): ?string
    {
        $trimmed = trim($value);
        if ($trimmed === '') {
            return null;
        }
        
        // Try datetime-local format (HTML5 input type)
        $date = \DateTimeImmutable::createFromFormat('Y-m-d\TH:i', $trimmed);
        if ($date instanceof \DateTimeImmutable) {
            return $date->format('Y-m-d H:i:s');
        }
        
        // Try ISO datetime format
        $date = \DateTimeImmutable::createFromFormat('Y-m-d H:i:s', $trimmed);
        if ($date instanceof \DateTimeImmutable) {
            return $trimmed;
        }
        
        // Fallback to strtotime
        $fallback = strtotime($trimmed);
        if ($fallback === false) {
            return null;
        }
        
        return date('Y-m-d H:i:s', $fallback);
    }
    
    /**
     * Get validation rules for client-side use (JSON)
     */
    public static function getClubValidationRules(): array
    {
        return [
            'nom' => [
                'required' => true,
                'minlength' => self::CLUB_NAME_MIN,
                'maxlength' => self::CLUB_NAME_MAX,
            ],
            'email_contact' => [
                'required' => false,
                'type' => 'email',
            ],
            'description' => [
                'required' => false,
                'maxlength' => self::CLUB_DESCRIPTION_MAX,
            ],
        ];
    }
    
    /**
     * Get validation rules for events (JSON)
     */
    public static function getEventValidationRules(): array
    {
        return [
            'titre' => [
                'required' => true,
                'minlength' => self::EVENT_TITLE_MIN,
                'maxlength' => self::EVENT_TITLE_MAX,
            ],
            'lieu' => [
                'required' => true,
                'minlength' => self::EVENT_LOCATION_MIN,
                'maxlength' => self::EVENT_LOCATION_MAX,
            ],
            'date_debut' => [
                'required' => true,
                'type' => 'datetime-local',
            ],
            'date_fin' => [
                'required' => true,
                'type' => 'datetime-local',
            ],
            'description' => [
                'required' => false,
                'maxlength' => self::EVENT_DESCRIPTION_MAX,
            ],
            'capacite' => [
                'required' => false,
                'type' => 'number',
                'min' => self::EVENT_CAPACITY_MIN,
                'max' => self::EVENT_CAPACITY_MAX,
            ],
        ];
    }
}
