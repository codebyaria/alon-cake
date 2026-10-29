import { Link } from '@inertiajs/react';
import { useState } from 'react';
import { DateCircle } from '@/components/date-circle';
import { localePath, useFormat, useLocale, useT } from '@/lib/i18n';
import { cn } from '@/lib/utils';
import type { CalendarDay } from '@/types/site';

type Props = {
    days: CalendarDay[];
    note: string | null;
};

/**
 * The centre of the page: honest capacity per day, full days shown (not hidden),
 * and the signature moment, a handwritten circle drawn around the chosen date.
 */
export function AvailabilityCalendar({ days, note }: Props) {
    const t = useT();
    const locale = useLocale();
    const format = useFormat();
    const [selected, setSelected] = useState(
        () => days.find((d) => d.bookable)?.date ?? null,
    );
    const chosen = days.find((d) => d.date === selected) ?? null;

    const statusLabel = (day: CalendarDay) => {
        switch (day.status) {
            case 'available':
                return day.remaining === 1
                    ? t('1 tempat lagi')
                    : t(':count tempat', { count: day.remaining });
            case 'full':
                return t('Penuh');
            case 'closed':
                return t('Dapur tutup');
            case 'day_off':
                return t('Libur');
            case 'too_soon':
                return t('Terlalu dekat');
            default:
                return t('Belum dibuka');
        }
    };

    const first = days[0];
    const last = days[days.length - 1];

    return (
        <div className="flex flex-col gap-8">
            <p className="text-sm text-muted-foreground">
                {format.date(first.date, { day: 'numeric', month: 'long' })} –{' '}
                {format.date(last.date, {
                    day: 'numeric',
                    month: 'long',
                    year: 'numeric',
                })}
            </p>

            <ul
                className="grid grid-cols-7 gap-1.5 md:gap-3"
                aria-label={t('Tanggal yang bisa dipesan')}
            >
                {days.map((day) => {
                    const isSelected = day.date === selected;
                    const label = statusLabel(day);

                    return (
                        <li key={day.date}>
                            <button
                                type="button"
                                disabled={!day.bookable}
                                aria-pressed={isSelected}
                                aria-label={`${format.date(day.date, { weekday: 'long', day: 'numeric', month: 'long' })}, ${label}`}
                                onClick={() => setSelected(day.date)}
                                className={cn(
                                    'flex w-full flex-col items-center gap-1.5 rounded-sm border py-3 transition-colors duration-600 ease-alon focus-visible:ring-2 focus-visible:ring-ring focus-visible:outline-none md:items-start md:gap-2 md:px-4 md:py-5',
                                    day.bookable
                                        ? 'border-cocoa/70 hover:bg-cream'
                                        : 'cursor-default border-border text-[#7D6759]',
                                    isSelected && 'border-cocoa bg-cream',
                                )}
                            >
                                <span className="text-xs md:text-[13px]">
                                    {format.date(day.date, {
                                        weekday: 'short',
                                    })}
                                </span>
                                <DateCircle
                                    key={`${day.date}-${isSelected}`}
                                    drawn={isSelected}
                                    className="size-8 md:size-10"
                                >
                                    <span
                                        className={cn(
                                            'text-[15px] tabular-nums md:font-display md:text-[30px] md:leading-none',
                                            day.status === 'full' &&
                                                'line-through',
                                        )}
                                    >
                                        {format.date(day.date, {
                                            day: 'numeric',
                                        })}
                                    </span>
                                </DateCircle>
                                <span className="hidden text-xs md:block">
                                    {label}
                                </span>
                            </button>
                        </li>
                    );
                })}
            </ul>

            {note && (
                <p className="font-display text-2xl italic" aria-live="polite">
                    {note}
                </p>
            )}

            <div className="flex flex-col gap-5 border-t border-border pt-8 md:flex-row md:items-end md:justify-between">
                <div className="flex flex-col gap-1" aria-live="polite">
                    <span className="font-display text-[28px] leading-tight">
                        {chosen
                            ? format.date(chosen.date, {
                                  weekday: 'long',
                                  day: 'numeric',
                                  month: 'long',
                              })
                            : t('Belum ada tanggal yang kosong')}
                    </span>
                    {chosen && (
                        <span className="text-sm text-muted-foreground">
                            {statusLabel(chosen)}
                        </span>
                    )}
                </div>

                {chosen && (
                    <Link
                        href={`${localePath(locale, '/pesan')}?tanggal=${chosen.date}`}
                        className="inline-flex h-14 items-center justify-center rounded-full bg-cocoa px-8 text-[15px] font-medium text-bone transition-opacity duration-600 ease-alon hover:opacity-90 focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 focus-visible:ring-offset-bone focus-visible:outline-none"
                    >
                        {t('Lanjut pilih kue')}
                    </Link>
                )}
            </div>

            <p className="text-[13px] text-muted-foreground">
                {t('Minggu libur. Pesanan paling cepat tiga hari sebelumnya.')}
            </p>
        </div>
    );
}
