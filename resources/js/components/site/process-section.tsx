import { useEffect, useRef, useState } from 'react';
import { CakeLayers } from '@/components/site/cake-layers';
import { Reveal } from '@/components/site/reveal';
import { useT } from '@/lib/i18n';
import { cn } from '@/lib/utils';

/**
 * "Tiga hari untuk satu kue": scrolling is time passing from H-3 to Hari H,
 * and the cake builds up with it (Brand Foundation > Interaksi khas).
 */
export function ProcessSection() {
    const t = useT();
    const [active, setActive] = useState(0);
    const stepRefs = useRef<(HTMLLIElement | null)[]>([]);

    const steps = [
        {
            day: 'H-3',
            text: t('Pesananmu kami catat dan tanggalnya kami lingkari.'),
        },
        { day: 'H-2', text: t('Sponge dipanggang dan didinginkan semalam.') },
        {
            day: 'H-1',
            text: t('Krim, lapisan, dan tulisan dihias dengan tangan.'),
        },
        {
            day: t('Hari H'),
            text: t('Kue siap diambil atau diantar, bersama kartu pesanmu.'),
        },
    ];

    useEffect(() => {
        // A step becomes current as it crosses the middle of the screen.
        const observer = new IntersectionObserver(
            (entries) => {
                for (const entry of entries) {
                    if (entry.isIntersecting) {
                        setActive(
                            Number((entry.target as HTMLElement).dataset.step),
                        );
                    }
                }
            },
            { rootMargin: '-45% 0px -45% 0px' },
        );

        stepRefs.current.forEach((node) => node && observer.observe(node));

        return () => observer.disconnect();
    }, []);

    return (
        <section
            id="proses"
            aria-labelledby="proses-judul"
            className="scroll-mt-8"
        >
            <div className="mx-auto max-w-6xl px-6 py-28 md:px-10 md:py-32">
                <Reveal className="flex flex-col justify-between gap-6 md:flex-row md:items-end">
                    <h2
                        id="proses-judul"
                        className="font-display text-4xl md:text-5xl"
                    >
                        {t('Tiga hari untuk satu kue')}
                    </h2>
                    <p className="max-w-sm text-[15px] leading-relaxed text-muted-foreground">
                        {t(
                            'Karena itu kami menerima pesanan paling cepat tiga hari sebelumnya.',
                        )}
                    </p>
                </Reveal>

                <div className="mt-12 grid gap-10 md:mt-16 md:grid-cols-2 md:gap-16">
                    {/* Sticky on every screen size so the cake grows while the steps scroll past. */}
                    <div className="sticky top-0 z-10 -mx-6 bg-cream px-6 py-4 md:top-24 md:mx-0 md:self-start md:bg-transparent md:p-0">
                        <CakeLayers
                            stage={active}
                            className="mx-auto h-44 w-auto md:h-auto md:w-full md:max-w-md"
                        />
                    </div>

                    <ol className="flex flex-col">
                        {steps.map((step, i) => (
                            <li
                                key={step.day}
                                ref={(node) => {
                                    stepRefs.current[i] = node;
                                }}
                                data-step={i}
                                aria-current={active === i ? 'step' : undefined}
                                className={cn(
                                    'flex min-h-[45vh] flex-col justify-center gap-4 border-t border-cocoa/80 py-10 transition-opacity duration-600 ease-alon md:min-h-[55vh]',
                                    active === i ? 'opacity-100' : 'opacity-40',
                                )}
                            >
                                <span className="font-display text-[32px] leading-none">
                                    {step.day}
                                </span>
                                <p className="max-w-sm text-base leading-relaxed text-[#5C4134] md:text-lg">
                                    {step.text}
                                </p>
                            </li>
                        ))}
                    </ol>
                </div>
            </div>
        </section>
    );
}
