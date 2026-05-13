<?php

declare(strict_types=1);

/**
 * User-generated files live under Model/uploads/ (gitignored), not project root uploads/
 * or View/ assets.
 */
final class AppUploads
{
    public static function root(): string
    {
        $dir = __DIR__ . '/uploads';
        if (!is_dir($dir)) {
            @mkdir($dir, 0775, true);
        }

        return $dir;
    }

    /**
     * Absolute path to a subdirectory (created). $name must be a single path segment.
     */
    public static function sub(string $name): string
    {
        $name = trim(str_replace(['..', '/', '\\'], '', $name));
        if ($name === '') {
            return self::root();
        }

        $dir = self::root() . '/' . $name;
        if (!is_dir($dir)) {
            @mkdir($dir, 0775, true);
        }

        return $dir;
    }
}
