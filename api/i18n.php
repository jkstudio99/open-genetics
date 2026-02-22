<?php

/**
 * 🧬 GET /api/i18n
 * 
 * Returns the i18n dictionary for the requested locale.
 * Locale is determined by X-Locale header or ?lang= query param.
 */

use OpenGenetics\Core\Response;

class I18n
{
    public static function get(array $body): void
    {
        Response::success([
            'locale'       => \OpenGenetics\I18n\I18n::getLocale(),
            'translations' => \OpenGenetics\I18n\I18n::all(),
            'available'    => \OpenGenetics\I18n\I18n::availableLocales(),
        ]);
    }
}
