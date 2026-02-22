<?php

declare(strict_types=1);

namespace OpenGenetics\I18n;

/**
 * 🧬 OpenGenetics — i18n Engine
 * 
 * Multi-language support via JSON dictionaries.
 * Supports Thai (th) and English (en) with instant switching
 * via X-Locale header — no page reload required.
 */
final class I18n
{
    private static string $locale = 'en';
    private static array $translations = [];
    private static string $langDir = '';

    /**
     * Initialize the i18n engine.
     *
     * @param string $langDir Absolute path to the locales/ directory
     * @param string $defaultLocale Default locale code
     */
    public static function init(string $langDir, string $defaultLocale = 'en'): void
    {
        self::$langDir = rtrim($langDir, '/');
        self::$locale  = $defaultLocale;

        // Detect locale from X-Locale header or query param
        $requested = $_SERVER['HTTP_X_LOCALE']
            ?? $_GET['lang']
            ?? $defaultLocale;

        self::setLocale($requested);
    }

    /**
     * Set the active locale and load its dictionary.
     */
    public static function setLocale(string $locale): void
    {
        $allowed = ['en', 'th'];

        if (!in_array($locale, $allowed, true)) {
            $locale = 'en';
        }

        self::$locale = $locale;
        self::loadDictionary($locale);
    }

    /**
     * Get the current locale.
     */
    public static function getLocale(): string
    {
        return self::$locale;
    }

    /**
     * Translate a key using dot notation.
     * 
     * Example: I18n::t('auth.login') → "เข้าสู่ระบบ"
     */
    public static function t(string $key, array $params = []): string
    {
        $value = self::$translations;

        foreach (explode('.', $key) as $segment) {
            if (!is_array($value) || !array_key_exists($segment, $value)) {
                return $key; // Return key itself if not found
            }
            $value = $value[$segment];
        }

        if (!is_string($value)) {
            return $key;
        }

        // Replace placeholders like :name
        foreach ($params as $param => $replacement) {
            $value = str_replace(":{$param}", (string) $replacement, $value);
        }

        return $value;
    }

    /**
     * Get all translations for the current locale (for SDK/frontend).
     */
    public static function all(): array
    {
        return self::$translations;
    }

    /**
     * Get available locales with labels.
     */
    public static function availableLocales(): array
    {
        return [
            ['code' => 'en', 'name' => 'English', 'flag' => '🇺🇸'],
            ['code' => 'th', 'name' => 'ไทย',     'flag' => '🇹🇭'],
        ];
    }

    /**
     * Load a JSON dictionary file.
     */
    private static function loadDictionary(string $locale): void
    {
        $file = self::$langDir . "/{$locale}.json";

        if (!file_exists($file)) {
            self::$translations = [];
            return;
        }

        $content = file_get_contents($file);
        $decoded = json_decode($content, true);

        self::$translations = is_array($decoded) ? $decoded : [];
    }
}
