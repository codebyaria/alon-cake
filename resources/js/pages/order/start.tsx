import { Head, Link } from '@inertiajs/react';
import { DateCircle } from '@/components/date-circle';
import SiteLayout from '@/layouts/site-layout';
import { localePath, useFormat, useLocale, useT } from '@/lib/i18n';
import type { CalendarDay } from '@/types/site';

type Props = {
    day: CalendarDay | null;
    firstBookable: string | null;
};

/**
 * Step 1 of the order flow, the chosen date circled. The next steps
 * (cake, details, card, pickup & payment) are not built yet, and the page says so.
 */
export default function OrderStart({ day, firstBookable }: Props) {
    const t = useT();
    const locale = useLocale();
    const format = useFormat();
    const home = localePath(locale, '/');

    return (
        <SiteLayout>
            <Head title={t('Pesan')} />
            <section className="mx-auto flex max-w-xl flex-col items-center gap-8 px-6 py-28 text-center md:py-36">
                {day ? (
                    <>
                        <p className="text-sm text-muted-foreground">
                            {/* "Rabu · November 2026", as in the confirmation wireframe. */}
                            {format.date(day.date, { weekday: 'long' })} ·{' '}
                            {format.date(day.date, {
                                month: 'long',
                                year: 'numeric',
                            })}
                        </p>
                        <DateCircle drawn={day.bookable} className="size-24">
                            <span className="font-display text-[64px] leading-none">
                                {format.date(day.date, { day: 'numeric' })}
                            </span>
                        </DateCircle>
                        <h1 className="font-display text-4xl leading-tight">
                            {day.bookable
                                ? t('Tanggal ini masih ada tempat.')
                                : t('Tanggal ini tidak bisa dipesan.')}
                        </h1>
                        <p className="text-base leading-relaxed text-[#5C4134]">
                            {day.bookable
                                ? t(
                                      'Langkah berikutnya, memilih kue, sedang kami siapkan.',
                                  )
                                : t(
                                      'Coba tanggal lain di kalender. Tanggal terdekat yang masih kosong: :date.',
                                      {
                                          date: firstBookable
                                              ? format.date(firstBookable, {
                                                    weekday: 'long',
                                                    day: 'numeric',
                                                    month: 'long',
                                                })
                                              : '—',
                                      },
                                  )}
                        </p>
                    </>
                ) : (
                    <h1 className="font-display text-4xl leading-tight">
                        {t('Belum ada tanggal yang kosong')}
                    </h1>
                )}
                <Link
                    href={`${home}#kalender`}
                    className="rounded-full border border-cocoa px-6 py-3 text-[15px] font-medium transition-colors duration-600 ease-alon hover:bg-cocoa hover:text-bone focus-visible:ring-2 focus-visible:ring-ring focus-visible:outline-none"
                >
                    {t('Kembali ke kalender')}
                </Link>
            </section>
        </SiteLayout>
    );
}
