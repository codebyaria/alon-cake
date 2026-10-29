import { Head } from '@inertiajs/react';
import { AvailabilityCalendar } from '@/components/site/availability-calendar';
import { PhotoFrame } from '@/components/site/photo-frame';
import { ProcessSection } from '@/components/site/process-section';
import { Reveal } from '@/components/site/reveal';
import { PipingLine } from '@/components/piping-line';
import SiteLayout from '@/layouts/site-layout';
import { useFormat, useT } from '@/lib/i18n';
import type { CalendarDay, MenuCake } from '@/types/site';

type HomeProps = {
    kitchenToday: string;
    leadDays: number;
    cakes: MenuCake[];
    calendar: { days: CalendarDay[]; note: string | null };
    deliveryFee: number;
};

const eyebrow = 'text-[13px] tracking-[0.14em] uppercase text-muted-foreground';

export default function Home({
    kitchenToday,
    cakes,
    calendar,
    deliveryFee,
}: HomeProps) {
    const t = useT();
    const format = useFormat();

    const faq = [
        {
            q: t('Kenapa harus pesan tiga hari sebelumnya?'),
            a: t(
                'Sponge perlu dipanggang dan didinginkan semalam, lalu dihias dengan tangan sehari sebelumnya. Tiga hari adalah waktu yang dibutuhkan satu kue, bukan aturan yang kami buat-buat.',
            ),
        },
        {
            q: t('Bisa diantar ke mana saja?'),
            a: t(
                'Kami antar di dalam kota dengan ongkos tetap :fee. Di luar itu, kue bisa diambil sendiri.',
                {
                    fee: format.rupiah(deliveryFee),
                },
            ),
        },
        {
            q: t('Bisa request desain atau tulisan sendiri?'),
            a: t(
                'Gaya kue kami tetap: krim polos dan garis tipis. Yang bisa kamu atur adalah ukuran, rasa, tulisan di kue (sampai 24 huruf), dan kartu pesan.',
            ),
        },
        {
            q: t('Bagaimana cara pembayarannya?'),
            a: t(
                'QRIS atau transfer bank. Pesanan tercatat setelah pembayaran. Di demo ini pembayaran hanya disimulasikan.',
            ),
        },
    ];

    return (
        <SiteLayout>
            <Head title={t('Kue ulang tahun, dibuat pelan-pelan')} />

            {/* 01 · Hero */}
            <section className="mx-auto grid max-w-6xl items-center gap-12 px-6 pt-16 pb-24 md:grid-cols-2 md:gap-16 md:px-10 md:pt-24 md:pb-32">
                <div className="flex flex-col gap-7">
                    <p className="flex items-start gap-3 text-sm leading-relaxed text-muted-foreground">
                        <span
                            aria-hidden="true"
                            className="mt-[7px] size-1.5 shrink-0 rounded-full bg-rose"
                        />
                        {kitchenToday}
                    </p>
                    <h1 className="font-display text-[44px] leading-[1.06] md:text-[64px] lg:text-[72px]">
                        {t(
                            'Kue ini mulai kami buat tiga hari sebelum ulang tahunnya.',
                        )}
                    </h1>
                    <p className="max-w-md text-[17px] leading-relaxed text-[#5C4134]">
                        {t(
                            'Cake ulang tahun yang dibuat satu per satu, untuk satu orang dan satu hari.',
                        )}
                    </p>
                    <a
                        href="#kalender"
                        className="inline-flex h-14 items-center self-start rounded-full bg-cocoa px-7 text-[15px] font-medium text-bone transition-opacity duration-600 ease-alon hover:opacity-90 focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 focus-visible:ring-offset-cream focus-visible:outline-none"
                    >
                        {t('Lihat tanggal yang tersedia')}
                    </a>
                </div>
                <PhotoFrame
                    alt={t(
                        'Kue krim dengan garis piping cokelat, di bawah cahaya pagi',
                    )}
                    placeholder={t(
                        'Foto diam: satu kue krim, garis piping cokelat, cahaya samping pagi, latar linen',
                    )}
                    className="aspect-[4/5] w-full md:aspect-auto md:h-[560px]"
                />
            </section>

            {/* 02 · Kenapa Alon ada */}
            <section
                id="cerita"
                aria-labelledby="cerita-judul"
                className="scroll-mt-8 bg-bone"
            >
                <Reveal className="mx-auto flex max-w-6xl flex-col items-center gap-7 px-6 py-28 text-center md:px-10 md:py-32">
                    <h2
                        id="cerita-judul"
                        className="max-w-3xl font-display text-4xl leading-[1.12] md:text-[52px]"
                    >
                        {t(
                            'Perayaan sekarang serba instan. Kami percaya momen penting layak ditunggu.',
                        )}
                    </h2>
                    <p className="max-w-xl text-base leading-relaxed text-[#5C4134]">
                        {t(
                            'Tidak ada kue di etalase. Semua dibuat setelah kamu memesan, dengan bahan asli dan dihias tangan.',
                        )}
                    </p>
                    <PipingLine reveal className="mt-4 max-w-[220px]" />
                </Reveal>
            </section>

            {/* 03 · Proses */}
            <ProcessSection />

            {/* 04 · Kue andalan */}
            <section
                id="kue"
                aria-labelledby="kue-judul"
                className="scroll-mt-8 bg-bone"
            >
                <div className="mx-auto max-w-6xl px-6 py-28 md:px-10 md:py-32">
                    <Reveal className="flex flex-col justify-between gap-6 md:flex-row md:items-end">
                        <h2
                            id="kue-judul"
                            className="font-display text-4xl md:text-5xl"
                        >
                            {t('Tiga kue, dibuat dengan sabar')}
                        </h2>
                        <p className="max-w-sm text-[15px] leading-relaxed text-muted-foreground">
                            {t(
                                'Sedikit pilihan, semuanya dihias tangan. Harga ditulis terbuka.',
                            )}
                        </p>
                    </Reveal>

                    <ul className="mt-14 grid gap-14 md:grid-cols-3 md:gap-6">
                        {cakes.map((cake) => (
                            <li key={cake.slug}>
                                <Reveal
                                    as="article"
                                    className="flex flex-col gap-4"
                                >
                                    <PhotoFrame
                                        src={cake.photoUrl}
                                        alt={cake.name}
                                        placeholder={t('Foto kue')}
                                        className="aspect-[4/5] w-full"
                                    />
                                    <h3 className="font-display text-[28px] leading-tight">
                                        {cake.name}
                                    </h3>
                                    <p className="text-[15px] leading-relaxed text-[#5C4134]">
                                        {cake.description}
                                    </p>
                                    <p className="text-sm">
                                        {cake.flavorNote} ·{' '}
                                        {t('mulai :price', {
                                            price: format.rupiah(
                                                cake.priceFrom,
                                            ),
                                        })}
                                    </p>
                                </Reveal>
                            </li>
                        ))}
                    </ul>
                </div>
            </section>

            {/* 05 · Kartu pesan */}
            <section aria-labelledby="kartu-judul">
                <div className="mx-auto grid max-w-6xl items-center gap-12 px-6 py-28 md:grid-cols-2 md:gap-16 md:px-10 md:py-32">
                    <PhotoFrame
                        alt={t('Kartu kecil bertulisan tangan di samping kue')}
                        placeholder={t(
                            'Foto: kartu kecil bertulisan tangan di samping kue',
                        )}
                        className="order-last aspect-[4/3] w-full md:order-first md:aspect-auto md:h-[440px]"
                    />
                    <Reveal className="flex flex-col gap-6">
                        <h2
                            id="kartu-judul"
                            className="font-display text-4xl leading-[1.1] md:text-5xl"
                        >
                            {t('Setiap kue datang dengan pesan darimu.')}
                        </h2>
                        <p className="max-w-md text-base leading-relaxed text-[#5C4134]">
                            {t(
                                'Tulis pesan saat memesan. Kami tuliskan di kartu kecil yang ikut bersama kuenya.',
                            )}
                        </p>
                    </Reveal>
                </div>
            </section>

            {/* 06 · Kalender */}
            <section
                id="kalender"
                aria-labelledby="kalender-judul"
                className="scroll-mt-8 bg-bone"
            >
                <div className="mx-auto flex max-w-6xl flex-col gap-12 px-6 py-28 md:px-10 md:py-32">
                    <Reveal className="flex flex-col justify-between gap-6 md:flex-row md:items-end">
                        <h2
                            id="kalender-judul"
                            className="font-display text-4xl md:text-5xl"
                        >
                            {t('Pilih tanggalnya dulu')}
                        </h2>
                        <p className="max-w-sm text-[15px] leading-relaxed text-muted-foreground">
                            {t(
                                'Setiap hari kami hanya membuat beberapa kue. Kapasitasnya kami tulis apa adanya.',
                            )}
                        </p>
                    </Reveal>
                    <AvailabilityCalendar
                        days={calendar.days}
                        note={calendar.note}
                    />
                </div>
            </section>

            {/* 07 · Pertanyaan */}
            <section aria-labelledby="faq-judul">
                <div className="mx-auto grid max-w-6xl gap-10 px-6 py-28 md:grid-cols-2 md:gap-16 md:px-10 md:py-32">
                    <div className="flex flex-col gap-4">
                        <p className={eyebrow}>{t('Pertanyaan')}</p>
                        <h2
                            id="faq-judul"
                            className="font-display text-4xl md:text-5xl"
                        >
                            {t('Sebelum memesan')}
                        </h2>
                    </div>
                    <div className="flex flex-col">
                        {faq.map((item) => (
                            <details
                                key={item.q}
                                className="group border-b border-border"
                            >
                                <summary className="flex cursor-pointer list-none items-center justify-between gap-6 rounded-sm py-6 text-base focus-visible:ring-2 focus-visible:ring-ring focus-visible:outline-none [&::-webkit-details-marker]:hidden">
                                    {item.q}
                                    <span
                                        aria-hidden="true"
                                        className="text-xl leading-none transition-transform duration-600 ease-alon group-open:rotate-45"
                                    >
                                        +
                                    </span>
                                </summary>
                                <p className="max-w-prose pb-6 text-[15px] leading-relaxed text-[#5C4134]">
                                    {item.a}
                                </p>
                            </details>
                        ))}
                    </div>
                </div>
            </section>
        </SiteLayout>
    );
}
