import { Head, Link } from '@inertiajs/react';
import { DateCircle } from '@/components/date-circle';
import { PipingLine } from '@/components/piping-line';
import { localePath, useFormat, useLocale, useT } from '@/lib/i18n';

type Props = {
    order: {
        code: string;
        date: string;
        cake: string;
        size: string;
        flavor: string;
        cakeText: string | null;
        cardMessage: string | null;
        cardFrom: string | null;
        handover: string;
        total: number;
        paymentMethod: string;
    };
    timeline: { date: string; label: string }[];
    whatsAppPreview: string;
    calendarUrl: string;
};

/**
 * Wireframe "6 · Konfirmasi": the date circled once more, then calm reassurance.
 */
export default function OrderDone({
    order,
    timeline,
    whatsAppPreview,
    calendarUrl,
}: Props) {
    const t = useT();
    const locale = useLocale();
    const format = useFormat();

    return (
        <div className="min-h-screen bg-cream text-cocoa">
            <Head title={t('Pesanan tercatat')} />
            <div className="mx-auto flex max-w-lg flex-col px-6 pt-6 pb-16">
                <header className="flex justify-center pb-10">
                    <Link
                        href={localePath(locale, '/')}
                        className="font-display text-[26px] leading-none"
                    >
                        Alon
                    </Link>
                </header>

                <section
                    className="flex flex-col items-center gap-6 text-center"
                    aria-labelledby="done-title"
                >
                    <p className="text-sm text-muted-foreground">
                        {format.date(order.date, { weekday: 'long' })} ·{' '}
                        {format.date(order.date, { month: 'long' })}
                    </p>
                    <DateCircle drawn className="size-28">
                        <span className="font-display text-[72px] leading-none">
                            {format.date(order.date, { day: 'numeric' })}
                        </span>
                    </DateCircle>
                    <h1
                        id="done-title"
                        className="pt-4 font-display text-4xl leading-tight"
                    >
                        {t('Tanggalnya sudah kami lingkari.')}
                    </h1>
                    <p className="max-w-sm text-[15px] leading-relaxed text-[#5C4134]">
                        {t(
                            'Kami kabari lewat WhatsApp di setiap tahap. Kamu cukup menunggu.',
                        )}
                    </p>
                </section>

                <ol
                    className="mt-12 flex flex-col border-t border-border"
                    aria-label={t('Linimasa kue')}
                >
                    {timeline.map((row, i) => (
                        <li
                            key={row.date + row.label}
                            className="flex items-baseline gap-5 border-b border-border py-4"
                        >
                            <span className="w-16 shrink-0 text-[13px] text-muted-foreground tabular-nums">
                                {format.date(row.date, {
                                    weekday: 'short',
                                    day: 'numeric',
                                })}
                            </span>
                            <span className={i === 0 ? 'font-medium' : ''}>
                                {row.label}
                            </span>
                        </li>
                    ))}
                </ol>

                <section
                    className="mt-12 flex flex-col gap-4 rounded-sm bg-bone p-6"
                    aria-labelledby="ringkasan"
                >
                    <div className="flex items-baseline justify-between gap-4">
                        <h2 id="ringkasan" className="text-[13px] font-medium">
                            {t('Pesananmu')}
                        </h2>
                        <span className="text-[13px] text-muted-foreground tabular-nums">
                            {order.code}
                        </span>
                    </div>
                    <p className="font-display text-2xl leading-tight">
                        {order.cake}
                    </p>
                    <p className="text-sm text-[#5C4134]">
                        {order.size} · {order.flavor} · {order.handover}
                    </p>
                    {order.cakeText && (
                        <p className="font-display text-2xl italic">
                            “{order.cakeText}”
                        </p>
                    )}
                    {order.cardMessage && (
                        <p className="text-sm leading-relaxed text-[#5C4134]">
                            {t('Kartu')}: {order.cardMessage}
                            {order.cardFrom && ` — ${order.cardFrom}`}
                        </p>
                    )}
                    <PipingLine
                        wavelength={16}
                        amplitude={5}
                        className="my-1"
                    />
                    <p className="flex justify-between text-sm">
                        <span>
                            {t('Dibayar')} · {order.paymentMethod}
                        </span>
                        <span className="font-medium">
                            {format.rupiah(order.total)}
                        </span>
                    </p>
                </section>

                <section
                    className="mt-10 flex flex-col gap-3"
                    aria-labelledby="wa-title"
                >
                    <h2 id="wa-title" className="text-[13px] font-medium">
                        {t('Pratinjau pesan WhatsApp')}
                    </h2>
                    <div className="max-w-[90%] rounded-2xl rounded-tl-sm bg-[#E7F0DF] px-4 py-3 text-sm leading-relaxed whitespace-pre-line text-[#2D3A27]">
                        {whatsAppPreview}
                    </div>
                    <p className="text-xs text-muted-foreground">
                        {t(
                            'Demo: pesan ini hanya pratinjau dan tidak dikirim.',
                        )}
                    </p>
                </section>

                <div className="mt-12 flex flex-col gap-3">
                    <a
                        href={calendarUrl}
                        className="flex h-[54px] items-center justify-center rounded-full bg-cocoa text-[15px] font-medium text-bone transition-opacity duration-600 ease-alon hover:opacity-90 focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 focus-visible:ring-offset-cream focus-visible:outline-none"
                    >
                        {t('Simpan ke kalender')}
                    </a>
                    <Link
                        href={localePath(locale, '/')}
                        className="flex h-[54px] items-center justify-center rounded-full border border-cocoa text-[15px] font-medium focus-visible:ring-2 focus-visible:ring-ring focus-visible:outline-none"
                    >
                        {t('Kembali ke beranda')}
                    </Link>
                </div>
            </div>
        </div>
    );
}
