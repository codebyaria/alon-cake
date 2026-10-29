import type { CSSProperties, ReactNode } from 'react';
import { duration, easing } from '@/lib/motion';
import { cn } from '@/lib/utils';

/**
 * The process illustration: a cake that builds up layer by layer.
 * `stage` follows the process steps: 0 = H-3 … 3 = Hari H.
 * Only transform and opacity move.
 */

const STROKE = 'currentColor';

function Layer({
    show,
    order = 0,
    children,
}: {
    show: boolean;
    /** Stagger within one stage, so layers settle one after another. */
    order?: number;
    children: ReactNode;
}) {
    const style: CSSProperties = {
        opacity: show ? 1 : 0,
        transform: show ? 'translateY(0)' : 'translateY(-18px)',
        transition: `opacity ${duration.base}ms ${easing.out}, transform ${duration.base}ms ${easing.out}`,
        transitionDelay: show ? `${order * 160}ms` : '0ms',
    };

    return <g style={style}>{children}</g>;
}

export function CakeLayers({
    stage,
    className,
}: {
    stage: number;
    className?: string;
}) {
    return (
        <svg
            viewBox="0 0 320 290"
            aria-hidden="true"
            focusable="false"
            className={cn('text-cocoa', className)}
            fill="none"
            stroke={STROKE}
            strokeWidth={1.5}
            strokeLinecap="round"
            strokeLinejoin="round"
        >
            {/* H-3: the plate is set out, the date is circled. */}
            <Layer show={stage >= 0}>
                <ellipse cx="160" cy="262" rx="132" ry="14" />
                <path
                    d="M40 262 Q 40 272 60 276 L 260 276 Q 280 272 280 262"
                    opacity="0.5"
                />
            </Layer>

            {/* H-2: sponges baked, filled with cream. */}
            <Layer show={stage >= 1}>
                <rect
                    x="54"
                    y="196"
                    width="212"
                    height="60"
                    rx="6"
                    fill="#FBF8F3"
                />
            </Layer>
            <Layer show={stage >= 1} order={1}>
                <rect
                    x="54"
                    y="184"
                    width="212"
                    height="12"
                    rx="4"
                    fill="#EFE6D8"
                />
            </Layer>
            <Layer show={stage >= 1} order={2}>
                <rect
                    x="54"
                    y="128"
                    width="212"
                    height="56"
                    rx="6"
                    fill="#FBF8F3"
                />
            </Layer>

            {/* H-1: cream coat, piping, handwriting. */}
            <Layer show={stage >= 2}>
                <path
                    d="M50 132 Q 50 116 70 114 L 250 114 Q 270 116 270 132"
                    fill="#FBF8F3"
                />
            </Layer>
            <Layer show={stage >= 2} order={1}>
                <path
                    d="M54 126 q 7 -8 14 0 t 14 0 t 14 0 t 14 0 t 14 0 t 14 0 t 14 0 t 14 0 t 14 0 t 14 0 t 14 0 t 14 0 t 14 0 t 14 0 t 14 0 t 14 0"
                    strokeWidth={1.2}
                />
                <path
                    d="M54 250 q 7 -7 14 0 t 14 0 t 14 0 t 14 0 t 14 0 t 14 0 t 14 0 t 14 0 t 14 0 t 14 0 t 14 0 t 14 0 t 14 0 t 14 0 t 14 0 t 14 0"
                    strokeWidth={1.2}
                />
            </Layer>
            <Layer show={stage >= 2} order={2}>
                {/* A loose, handwritten scribble standing in for the message. */}
                <path
                    d="M100 160 c 6 -10 10 -10 12 0 c 2 8 6 8 10 -2 c 3 -8 7 -6 8 2 c 1 6 6 6 10 -2 c 4 -8 8 -4 9 2 c 2 6 7 4 10 -4 M168 162 c 4 -6 8 -6 10 0 c 2 6 6 4 8 -2 c 2 -6 6 -6 8 0 c 2 6 6 6 10 -2 c 3 -6 8 -4 9 2"
                    strokeWidth={1.1}
                />
            </Layer>

            {/* Hari H: a single candle. The only touch of accent colour. */}
            <Layer show={stage >= 3}>
                <rect
                    x="154"
                    y="70"
                    width="12"
                    height="44"
                    rx="2"
                    fill="#FBF8F3"
                />
            </Layer>
            <Layer show={stage >= 3} order={1}>
                <path
                    d="M160 44 C 168 54 168 62 160 66 C 152 62 152 54 160 44 Z"
                    fill="#C9938B"
                    stroke="#C9938B"
                />
            </Layer>
        </svg>
    );
}
