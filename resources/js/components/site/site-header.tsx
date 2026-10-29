import { Link, usePage } from '@inertiajs/react';
import { PipingLink } from '@/components/site/piping-link';
import { localePath, switchLocalePath, useLocale, useT } from '@/lib/i18n';
import { cn } from '@/lib/utils';

export function SiteHeader() {
    const locale = useLocale();
    const t = useT();
    const { url } = usePage();
    const pathname = url.split(/[?#]/)[0];
    const home = localePath(locale, '/');

    const sections = [
        { href: `${home}#kue`, label: t('Kue') },
        { href: `${home}#proses`, label: t('Cara memesan') },
        { href: `${home}#cerita`, label: t('Cerita') },
    ];

    return (
        <header className="border-b border-border">
            <nav
                aria-label={t('Navigasi utama')}
                className="mx-auto flex max-w-6xl items-center justify-between gap-6 px-6 py-5 md:px-10 md:py-7"
            >
                <Link
                    href={home}
                    className="font-display text-[30px] leading-none"
                    aria-label="Alon Cake"
                >
                    Alon
                </Link>

                <div className="flex items-center gap-6 text-[15px] md:gap-10">
                    <ul className="hidden items-center gap-10 md:flex">
                        {sections.map((s) => (
                            <li key={s.href}>
                                <PipingLink href={s.href}>{s.label}</PipingLink>
                            </li>
                        ))}
                    </ul>

                    <div
                        className="flex items-center gap-1 text-sm"
                        aria-label={t('Bahasa')}
                    >
                        {(['id', 'en'] as const).map((l, i) => (
                            <span key={l} className="flex items-center gap-1">
                                {i > 0 && <span aria-hidden="true">/</span>}
                                <Link
                                    href={switchLocalePath(pathname, l)}
                                    hrefLang={l}
                                    lang={l}
                                    aria-current={
                                        locale === l ? 'true' : undefined
                                    }
                                    className={cn(
                                        'rounded-sm px-1 py-2 uppercase focus-visible:ring-2 focus-visible:ring-ring focus-visible:outline-none',
                                        locale === l
                                            ? 'font-medium'
                                            : 'text-muted-foreground',
                                    )}
                                >
                                    {l}
                                </Link>
                            </span>
                        ))}
                    </div>

                    <Link
                        href={localePath(locale, '/pesan')}
                        className="rounded-full border border-cocoa px-5 py-2.5 font-medium transition-colors duration-600 ease-alon hover:bg-cocoa hover:text-bone focus-visible:ring-2 focus-visible:ring-ring focus-visible:outline-none"
                    >
                        {t('Pesan')}
                    </Link>
                </div>
            </nav>
        </header>
    );
}
