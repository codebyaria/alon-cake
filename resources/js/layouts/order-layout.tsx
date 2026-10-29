import { Head, Link } from '@inertiajs/react';
import type { ReactNode } from 'react';
import { localePath, useLocale, useT } from '@/lib/i18n';
import { cn } from '@/lib/utils';

const TOTAL_STEPS = 5;

type Props = {
    /** 1 … 5 */
    step: number;
    title: string;
    backHref: string;
    children: ReactNode;
    /** The sticky action area at the bottom (summary + primary button). */
    footer: ReactNode;
};

/**
 * One calm column, the same on phone and desktop (wireframe "Order 1–5"):
 * back, logo, step count, a thin progress line, content, and one clear action.
 */
export default function OrderLayout({
    step,
    title,
    backHref,
    children,
    footer,
}: Props) {
    const t = useT();
    const locale = useLocale();

    return (
        <div className="min-h-screen bg-cream text-cocoa">
            <Head title={title} />
            <div className="mx-auto flex min-h-screen max-w-lg flex-col">
                <header className="flex items-center justify-between px-6 pt-4 pb-3">
                    <Link
                        href={backHref}
                        aria-label={t('Kembali')}
                        className="-ml-3 flex size-11 items-center justify-center rounded-full focus-visible:ring-2 focus-visible:ring-ring focus-visible:outline-none"
                    >
                        <svg
                            aria-hidden="true"
                            width="20"
                            height="20"
                            viewBox="0 0 20 20"
                        >
                            <path
                                d="M12 4 L6 10 L12 16"
                                fill="none"
                                stroke="currentColor"
                                strokeWidth="1.5"
                                strokeLinecap="round"
                                strokeLinejoin="round"
                            />
                        </svg>
                    </Link>
                    <Link
                        href={localePath(locale, '/')}
                        className="font-display text-[26px] leading-none"
                    >
                        Alon
                    </Link>
                    <span className="w-11 text-right text-[13px] text-muted-foreground">
                        <span className="sr-only">{t('Langkah')} </span>
                        {step}/{TOTAL_STEPS}
                    </span>
                </header>

                <div
                    className="grid grid-cols-5 gap-1.5 px-6"
                    aria-hidden="true"
                >
                    {Array.from({ length: TOTAL_STEPS }, (_, i) => (
                        <div
                            key={i}
                            className={cn(
                                'h-0.5 origin-left transition-transform duration-800 ease-alon',
                                i < step ? 'bg-cocoa' : 'bg-border',
                            )}
                        />
                    ))}
                </div>

                <main className="flex flex-1 flex-col px-6 pt-8 pb-10">
                    {children}
                </main>

                <div className="sticky bottom-0 border-t border-border bg-bone px-6 pt-4 pb-6">
                    {footer}
                </div>
            </div>
        </div>
    );
}
