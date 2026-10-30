import { Link } from '@inertiajs/react';
import { localePath, useLocale, useT } from '@/lib/i18n';

export function SiteFooter() {
    const locale = useLocale();
    const t = useT();

    return (
        <footer className="bg-cocoa text-bone">
            <div className="mx-auto flex max-w-6xl flex-col items-center gap-8 px-6 pt-28 pb-16 text-center md:px-10 md:pt-32">
                <h2 className="max-w-2xl font-display text-4xl leading-tight md:text-[56px]">
                    {t('Hari yang penting, kue yang ditunggu.')}
                </h2>
                <Link
                    href={`${localePath(locale, '/')}#kalender`}
                    className="rounded-full border border-bone px-7 py-4 text-[15px] font-medium transition-colors duration-600 ease-alon hover:bg-bone hover:text-cocoa focus-visible:ring-2 focus-visible:ring-bone focus-visible:ring-offset-2 focus-visible:ring-offset-cocoa focus-visible:outline-none"
                >
                    {t('Pilih tanggalmu')}
                </Link>
                <p className="mt-12 text-[13px] text-bone/75">
                    {t(
                        'Concept project oleh Ari Zain · Demo tanpa transaksi nyata',
                    )}
                    <br />
                    {t('Foto dibuat dengan AI untuk keperluan demo.')}
                </p>
            </div>
        </footer>
    );
}
