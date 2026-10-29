import { usePage } from '@inertiajs/react';

/**
 * Tiny i18n for the public site.
 *
 * Copy is written in Indonesian (the default language) and used as the key;
 * English lives in lang/en.json, shared by Laravel's __() and this hook.
 * Placeholders use Laravel's `:name` syntax.
 */

export type Locale = 'id' | 'en';

export const intlLocale: Record<Locale, string> = {
    id: 'id-ID',
    en: 'en-GB',
};

function replace(text: string, values?: Record<string, string | number>) {
    if (!values) return text;

    return Object.entries(values).reduce(
        (out, [name, value]) => out.replaceAll(`:${name}`, String(value)),
        text,
    );
}

export function useLocale(): Locale {
    return usePage().props.locale;
}

export function useT() {
    const { translations } = usePage().props;

    return (key: string, values?: Record<string, string | number>) =>
        replace(translations[key] ?? key, values);
}

/** Prefix a path for the current language: `/pesan` → `/en/pesan`. */
export function localePath(locale: Locale, path: string): string {
    const clean = path.startsWith('/') ? path : `/${path}`;

    if (locale === 'id') return clean;

    return clean === '/' ? '/en' : `/en${clean}`;
}

/** The same page in the other language, for the ID / EN switch. */
export function switchLocalePath(pathname: string, to: Locale): string {
    const withoutPrefix = pathname.replace(/^\/en(?=\/|$)/, '') || '/';

    return localePath(to, withoutPrefix);
}

/** Formatters bound to the current language. */
export function useFormat() {
    const locale = useLocale();
    const tag = intlLocale[locale];

    return {
        rupiah: (amount: number) =>
            new Intl.NumberFormat(tag, {
                style: 'currency',
                currency: 'IDR',
                maximumFractionDigits: 0,
            }).format(amount),
        date: (iso: string, options: Intl.DateTimeFormatOptions) =>
            new Intl.DateTimeFormat(tag, options).format(parseDate(iso)),
    };
}

/** Parse a `YYYY-MM-DD` string as a local date (not UTC midnight). */
export function parseDate(iso: string): Date {
    const [y, m, d] = iso.split('-').map(Number);

    return new Date(y, m - 1, d);
}
