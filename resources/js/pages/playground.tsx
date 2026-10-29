import { Head } from '@inertiajs/react';
import { useState } from 'react';
import type { ReactNode } from 'react';
import { DateCircle } from '@/components/date-circle';
import { PipingLine } from '@/components/piping-line';
import { duration, usePrefersReducedMotion } from '@/lib/motion';
import { cn } from '@/lib/utils';

const swatches = [
    { name: 'Krim', hex: '#F6F0E6', use: 'latar', className: 'bg-cream' },
    {
        name: 'Putih tulang',
        hex: '#FBF8F3',
        use: 'kartu',
        className: 'bg-bone',
    },
    {
        name: 'Cokelat tua',
        hex: '#4A2E22',
        use: 'teks & garis',
        className: 'bg-cocoa',
    },
    {
        name: 'Dusty pink',
        hex: '#C9938B',
        use: 'aksen, jarang',
        className: 'bg-rose',
    },
];

/** Pre-order rules from CLAUDE.md, applied to a two-week strip. */
const LEAD_DAYS = 3;
const DAYS_SHOWN = 14;

function upcomingDays() {
    const today = new Date();
    today.setHours(0, 0, 0, 0);

    return Array.from({ length: DAYS_SHOWN }, (_, i) => {
        const date = new Date(today);
        date.setDate(today.getDate() + i);
        const isSunday = date.getDay() === 0;

        return {
            key: date.toISOString().slice(0, 10),
            date,
            available: i >= LEAD_DAYS && !isSunday,
            note: isSunday ? 'libur' : i < LEAD_DAYS ? 'terlalu dekat' : null,
        };
    });
}

const weekday = new Intl.DateTimeFormat('id-ID', { weekday: 'short' });
const longDate = new Intl.DateTimeFormat('id-ID', {
    weekday: 'long',
    day: 'numeric',
    month: 'long',
});

function Section({ title, children }: { title: string; children: ReactNode }) {
    return (
        <section className="space-y-8">
            <h2 className="text-xs font-medium tracking-[0.2em] text-muted-foreground uppercase">
                {title}
            </h2>
            {children}
        </section>
    );
}

function ReplayButton({ onClick }: { onClick: () => void }) {
    return (
        <button
            type="button"
            onClick={onClick}
            className="rounded-full border border-cocoa/30 px-4 py-1.5 text-sm transition-colors hover:border-cocoa focus-visible:ring-2 focus-visible:ring-ring focus-visible:outline-none"
        >
            Ulangi
        </button>
    );
}

export default function Playground() {
    const reduced = usePrefersReducedMotion();
    const days = upcomingDays();
    const [selected, setSelected] = useState(
        () => days.find((d) => d.available)?.key ?? null,
    );
    const [redraw, setRedraw] = useState(0);
    const [revealRun, setRevealRun] = useState(0);
    const chosen = days.find((d) => d.key === selected);

    return (
        <>
            <Head title="Playground" />
            <main className="min-h-screen bg-cream text-cocoa">
                <div className="mx-auto max-w-4xl space-y-24 px-6 py-20 md:px-10 md:py-28">
                    <header className="space-y-6">
                        <p className="text-xs font-medium tracking-[0.2em] text-muted-foreground uppercase">
                            Playground
                        </p>
                        <h1 className="font-display text-5xl font-medium md:text-6xl">
                            Alon Cake
                        </h1>
                        <p className="max-w-md text-base leading-relaxed">
                            Momen penting layak ditunggu.
                        </p>
                        <PipingLine reveal className="max-w-xs" />
                    </header>

                    <Section title="Warna">
                        <div className="grid grid-cols-2 gap-6 md:grid-cols-4">
                            {swatches.map((s) => (
                                <div key={s.hex} className="space-y-3">
                                    <div
                                        className={cn(
                                            'aspect-square rounded-2xl ring-1 ring-cocoa/10',
                                            s.className,
                                        )}
                                    />
                                    <div className="text-sm">
                                        <p className="font-medium">{s.name}</p>
                                        <p className="text-muted-foreground">
                                            {s.hex} · {s.use}
                                        </p>
                                    </div>
                                </div>
                            ))}
                        </div>
                    </Section>

                    <Section title="Huruf">
                        <div className="space-y-6 rounded-3xl bg-bone p-8 md:p-12">
                            <p className="font-display text-4xl leading-tight">
                                Kue yang dibuat pelan-pelan
                            </p>
                            <p className="font-display text-2xl italic">
                                untuk hari yang kamu tunggu
                            </p>
                            <p className="max-w-prose leading-relaxed">
                                DM Sans untuk teks, label, tombol, dan harga.
                                Cormorant Garamond hanya untuk judul 24px ke
                                atas.
                            </p>
                            <p className="text-lg font-medium">Rp 385.000</p>
                        </div>
                    </Section>

                    <Section title="Garis piping">
                        <div className="space-y-10 rounded-3xl bg-bone p-8 md:p-12">
                            <PipingLine />
                            <PipingLine
                                variant="scallop"
                                wavelength={14}
                                amplitude={6}
                            />
                            <PipingLine
                                tone="rose"
                                wavelength={32}
                                amplitude={10}
                            />
                            <div className="flex items-center gap-6">
                                <PipingLine
                                    key={revealRun}
                                    reveal
                                    className="flex-1"
                                />
                                <ReplayButton
                                    onClick={() => setRevealRun((n) => n + 1)}
                                />
                            </div>
                        </div>
                    </Section>

                    <Section title="Pilih tanggal">
                        <div className="space-y-10 rounded-3xl bg-bone p-8 md:p-12">
                            <ul
                                className="grid grid-cols-7 gap-y-8"
                                aria-label="Tanggal ambil atau antar"
                            >
                                {days.map((d) => (
                                    <li
                                        key={d.key}
                                        className="flex flex-col items-center gap-3"
                                    >
                                        <span className="text-xs text-muted-foreground">
                                            {weekday.format(d.date)}
                                        </span>
                                        <button
                                            type="button"
                                            disabled={!d.available}
                                            aria-pressed={selected === d.key}
                                            aria-label={`${longDate.format(d.date)}${d.note ? `, ${d.note}` : ''}`}
                                            onClick={() => setSelected(d.key)}
                                            className="grid size-11 place-items-center rounded-full text-lg tabular-nums focus-visible:ring-2 focus-visible:ring-ring focus-visible:outline-none disabled:text-cocoa/30"
                                        >
                                            <DateCircle
                                                key={`${d.key}-${redraw}`}
                                                drawn={selected === d.key}
                                                className="size-full"
                                            >
                                                {d.date.getDate()}
                                            </DateCircle>
                                        </button>
                                    </li>
                                ))}
                            </ul>

                            <div className="flex flex-wrap items-end justify-between gap-6">
                                <p
                                    className="font-display text-3xl"
                                    aria-live="polite"
                                >
                                    {chosen
                                        ? longDate.format(chosen.date)
                                        : '—'}
                                </p>
                                <ReplayButton
                                    onClick={() => setRedraw((n) => n + 1)}
                                />
                            </div>
                            <p className="text-sm text-muted-foreground">
                                Pre-order minimal H-{LEAD_DAYS}. Minggu libur.
                            </p>
                        </div>
                    </Section>

                    <footer className="text-sm text-muted-foreground">
                        Gerak {duration.fast}–{duration.slow} ms, ease-out ·
                        prefers-reduced-motion:{' '}
                        {reduced ? 'aktif' : 'tidak aktif'}
                    </footer>
                </div>
            </main>
        </>
    );
}
