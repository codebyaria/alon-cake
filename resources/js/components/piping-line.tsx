import { useId } from 'react';
import { useInView } from '@/hooks/use-in-view';
import { cn } from '@/lib/utils';

type Tone = 'cocoa' | 'rose';

type PipingLineProps = {
    /** `wave`: a soft continuous ripple. `scallop`: piped beads of icing. */
    variant?: 'wave' | 'scallop';
    /** Length of one wave, in px. */
    wavelength?: number;
    /** Height of the wave from crest to trough, in px. */
    amplitude?: number;
    strokeWidth?: number;
    tone?: Tone;
    /** Draw the line in from the left once, when it scrolls into view. */
    reveal?: boolean;
    className?: string;
};

const toneClass: Record<Tone, string> = {
    cocoa: 'text-cocoa',
    rose: 'text-rose',
};

/** One tile of the repeating path, from (0, mid) to (w, mid). */
function tilePath(variant: 'wave' | 'scallop', w: number, h: number): string {
    const mid = h / 2;
    const a = h / 2 - 1;

    if (variant === 'scallop') {
        // Bead: dip down and rise back up, like a row of piped dots.
        return `M0 ${mid - a / 2} Q${w / 2} ${mid + a * 1.5} ${w} ${mid - a / 2}`;
    }

    // Wave: one full sine-like period made of two quadratic halves.
    return `M0 ${mid} Q${w / 4} ${mid - a} ${w / 2} ${mid} T${w} ${mid}`;
}

/**
 * Alon Cake's signature element: a thin, wavy piping line.
 * Purely decorative, so it is hidden from assistive technology.
 */
export function PipingLine({
    variant = 'wave',
    wavelength = 24,
    amplitude = 8,
    strokeWidth = 1.25,
    tone = 'cocoa',
    reveal = false,
    className,
}: PipingLineProps) {
    const patternId = `piping-${useId().replace(/[^a-zA-Z0-9-]/g, '')}`;
    const [ref, inView] = useInView<HTMLDivElement>();
    const height = amplitude + strokeWidth * 2 + 2;

    const line = (
        <svg
            width="100%"
            height={height}
            aria-hidden="true"
            focusable="false"
            className="block"
        >
            <defs>
                <pattern
                    id={patternId}
                    width={wavelength}
                    height={height}
                    patternUnits="userSpaceOnUse"
                >
                    <path
                        d={tilePath(variant, wavelength, height)}
                        fill="none"
                        stroke="currentColor"
                        strokeWidth={strokeWidth}
                        strokeLinecap="round"
                    />
                </pattern>
            </defs>
            <rect width="100%" height={height} fill={`url(#${patternId})`} />
        </svg>
    );

    return (
        <div
            ref={ref}
            role="presentation"
            className={cn('w-full overflow-hidden', toneClass[tone], className)}
        >
            {reveal ? (
                // Hidden off to the side until in view, then wiped in (transform only).
                <div
                    className={cn(
                        'overflow-hidden',
                        inView ? 'animate-wipe-outer' : '-translate-x-full',
                    )}
                >
                    <div
                        className={
                            inView ? 'animate-wipe-inner' : 'translate-x-full'
                        }
                    >
                        {line}
                    </div>
                </div>
            ) : (
                line
            )}
        </div>
    );
}
