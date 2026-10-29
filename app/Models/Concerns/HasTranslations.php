<?php

namespace App\Models\Concerns;

use Illuminate\Support\Facades\App;

/**
 * Read JSON columns shaped like {"id": "...", "en": "..."}.
 *
 * Indonesian is the default language (CLAUDE.md), so it is the fallback.
 */
trait HasTranslations
{
    public function translate(string $attribute, ?string $locale = null): string
    {
        $values = (array) $this->getAttribute($attribute);
        $locale ??= App::getLocale();

        return $values[$locale] ?? $values['id'] ?? (string) reset($values);
    }
}
