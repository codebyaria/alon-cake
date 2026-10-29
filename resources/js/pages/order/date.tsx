import { Link, useForm } from '@inertiajs/react';
import type { FormEvent } from 'react';
import { DateCircle } from '@/components/date-circle';
import { FieldError } from '@/components/order/field-error';
import { PrimaryButton } from '@/components/order/primary-button';
import { StepHeading } from '@/components/order/step-heading';
import OrderLayout from '@/layouts/order-layout';
import { localePath, parseDate, useFormat, useLocale, useT } from '@/lib/i18n';
import { cn } from '@/lib/utils';
import type { CalendarDay } from '@/types/site';

type Props = {
    month: string;
    days: CalendarDay[];
    prevMonth: string | null;
    nextMonth: string | null;
    selected: string | null;
    earliest: string;
    leadDays: number;
};

export default function OrderDate({
    month,
    days,
    prevMonth,
    nextMonth,
    selected,
    earliest,
}: Props) {
    const t = useT();
    const locale = useLocale();
    const format = useFormat();
    const base = localePath(locale, '/pesan');
    const form = useForm({ date: selected ?? '' });
    const chosen = days.find((d) => d.date === form.data.date);

    // Monday-first grid, like the wireframe.
    const leadingBlanks = (parseDate(days[0].date).getDay() + 6) % 7;
    const weekdays = Array.from({ length: 7 }, (_, i) =>
        format.date(`2026-11-0${2 + i}`, { weekday: 'short' }),
    );

    const submit = (event: FormEvent) => {
        event.preventDefault();
        form.post(base);
    };

    return (
        <OrderLayout
            step={1}
            title={t('Pilih tanggal')}
            backHref={`${localePath(locale, '/')}#kalender`}
            footer={
                <form onSubmit={submit} className="flex flex-col gap-3.5">
                    <div
                        className="flex items-baseline justify-between gap-4"
                        aria-live="polite"
                    >
                        <span className="font-display text-2xl">
                            {form.data.date
                                ? format.date(form.data.date, {
                                      weekday: 'long',
                                      day: 'numeric',
                                      month: 'long',
                                  })
                                : t('Belum memilih tanggal')}
                        </span>
                        {chosen && (
                            <span className="shrink-0 text-[13px] text-muted-foreground">
                                {t(':count tempat tersisa', {
                                    count: chosen.remaining,
                                })}
                            </span>
                        )}
                    </div>
                    <PrimaryButton
                        type="submit"
                        disabled={!form.data.date || form.processing}
                    >
                        {t('Lanjut pilih kue')}
                    </PrimaryButton>
                </form>
            }
        >
            <StepHeading title={t('Untuk hari apa?')}>
                {t('Setiap kue butuh tiga hari. Tanggal paling cepat: :date.', {
                    date: format.date(earliest, {
                        weekday: 'long',
                        day: 'numeric',
                        month: 'long',
                    }),
                })}
            </StepHeading>

            <FieldError message={form.errors.date} />

            <div className="flex flex-col gap-3 pt-2">
                <div className="flex items-center justify-between">
                    <h2 className="font-display text-2xl">
                        {format.date(`${month}-01`, {
                            month: 'long',
                            year: 'numeric',
                        })}
                    </h2>
                    <div className="flex gap-1">
                        <MonthLink
                            href={prevMonth && `${base}?bulan=${prevMonth}`}
                            label={t('Bulan sebelumnya')}
                            direction="prev"
                        />
                        <MonthLink
                            href={nextMonth && `${base}?bulan=${nextMonth}`}
                            label={t('Bulan berikutnya')}
                            direction="next"
                        />
                    </div>
                </div>

                <div
                    className="grid grid-cols-7 text-center text-xs text-muted-foreground"
                    aria-hidden="true"
                >
                    {weekdays.map((d) => (
                        <span key={d}>{d}</span>
                    ))}
                </div>

                <ul
                    className="grid grid-cols-7 gap-y-1"
                    aria-label={t('Tanggal yang bisa dipesan')}
                >
                    {Array.from({ length: leadingBlanks }, (_, i) => (
                        <li key={`blank-${i}`} aria-hidden="true" />
                    ))}
                    {days.map((day) => {
                        const isSelected = day.date === form.data.date;

                        return (
                            <li
                                key={day.date}
                                className="flex h-12 items-center justify-center"
                            >
                                <button
                                    type="button"
                                    disabled={!day.bookable}
                                    aria-pressed={isSelected}
                                    aria-label={`${format.date(day.date, { weekday: 'long', day: 'numeric', month: 'long' })}, ${dayLabel(day, t)}`}
                                    onClick={() =>
                                        form.setData('date', day.date)
                                    }
                                    className={cn(
                                        'flex size-11 items-center justify-center rounded-full text-[15px] tabular-nums focus-visible:ring-2 focus-visible:ring-ring focus-visible:outline-none',
                                        day.bookable
                                            ? 'hover:bg-bone'
                                            : 'cursor-default text-[#7D6759]',
                                        day.status === 'full' && 'line-through',
                                        isSelected && 'font-medium',
                                    )}
                                >
                                    <DateCircle
                                        key={`${day.date}-${isSelected}`}
                                        drawn={isSelected}
                                        className="size-full"
                                    >
                                        {format.date(day.date, {
                                            day: 'numeric',
                                        })}
                                    </DateCircle>
                                </button>
                            </li>
                        );
                    })}
                </ul>

                <p className="flex gap-4 pt-1 text-xs text-muted-foreground">
                    <span>
                        <span className="line-through">12</span> {t('penuh')}
                    </span>
                    <span>{t('Minggu libur')}</span>
                </p>
            </div>
        </OrderLayout>
    );
}

function dayLabel(day: CalendarDay, t: ReturnType<typeof useT>) {
    switch (day.status) {
        case 'available':
            return t(':count tempat tersisa', { count: day.remaining });
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
}

function MonthLink({
    href,
    label,
    direction,
}: {
    href: string | null;
    label: string;
    direction: 'prev' | 'next';
}) {
    const arrow = (
        <svg aria-hidden="true" width="18" height="18" viewBox="0 0 20 20">
            <path
                d={
                    direction === 'prev'
                        ? 'M12 4 L6 10 L12 16'
                        : 'M8 4 L14 10 L8 16'
                }
                fill="none"
                stroke="currentColor"
                strokeWidth="1.5"
                strokeLinecap="round"
                strokeLinejoin="round"
            />
        </svg>
    );

    if (!href) {
        return (
            <span
                aria-hidden="true"
                className="flex size-11 items-center justify-center text-border"
            >
                {arrow}
            </span>
        );
    }

    return (
        <Link
            href={href}
            aria-label={label}
            preserveScroll
            preserveState
            className="flex size-11 items-center justify-center rounded-full hover:bg-bone focus-visible:ring-2 focus-visible:ring-ring focus-visible:outline-none"
        >
            {arrow}
        </Link>
    );
}
