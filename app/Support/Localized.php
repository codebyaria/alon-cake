<?php

namespace App\Support;

use Illuminate\Support\Facades\App;

/**
 * Route helpers that keep the visitor in their language (/ or /en).
 */
final class Localized
{
    /**
     * @param  array<string, mixed>  $parameters
     */
    public static function route(string $name, array $parameters = []): string
    {
        return route(App::getLocale() === 'en' ? "en.{$name}" : $name, $parameters);
    }
}
