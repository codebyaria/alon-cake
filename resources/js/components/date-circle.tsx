import { useId, useLayoutEffect, useRef } from 'react';
import type { ReactNode } from 'react';
import {
    cubicBezier,
    duration,
    easing,
    easingPoints,
    usePrefersReducedMotion,
} from '@/lib/motion';
import { cn } from '@/lib/utils';

type Tone = 'cocoa' | 'rose';

type DateCircleProps = {
    children: ReactNode;
    /** When true, the hand-drawn circle is drawn around the children. */
    drawn: boolean;
    tone?: Tone;
    className?: string;
};

/*
 * Geometry (viewBox 0 0 120 120, centre 60,60).
 *
 * The loop is drawn clockwise from 12 o'clock. It spirals outward a little
 * and overshoots the start by TAIL degrees, like a quick circle made by hand.
 */
const C = 60;
const TAIL = 40;
const TAIL_REVEAL = 45;
const TOTAL = 360 + TAIL_REVEAL;

function radiusAt(deg: number): [number, number] {
    const t = (deg * Math.PI) / 180;
    const wobble =
        1 + 0.035 * Math.sin(2 * t + 0.6) + 0.02 * Math.sin(3 * t + 1.9);
    const spiral = 0.96 + (0.08 * deg) / (360 + TAIL);

    return [50 * wobble * spiral, 42 * wobble * spiral];
}

function pointAt(deg: number): [number, number] {
    const t = (deg * Math.PI) / 180;
    const [rx, ry] = radiusAt(deg);

    return [C + rx * Math.sin(t), C - ry * Math.cos(t)];
}

/** Smooth path through sampled points (Catmull-Rom → cubic Bézier). */
function pathBetween(from: number, to: number, step = 10): string {
    const pts: [number, number][] = [];
    for (let d = from - step; d <= to + step; d += step) pts.push(pointAt(d));

    const f = (n: number) => n.toFixed(2);
    let d = `M${f(pts[1][0])} ${f(pts[1][1])}`;

    for (let i = 1; i < pts.length - 2; i++) {
        const [p0, p1, p2, p3] = [pts[i - 1], pts[i], pts[i + 1], pts[i + 2]];
        const c1 = [p1[0] + (p2[0] - p0[0]) / 6, p1[1] + (p2[1] - p0[1]) / 6];
        const c2 = [p2[0] - (p3[0] - p1[0]) / 6, p2[1] - (p3[1] - p1[1]) / 6];
        d += ` C${f(c1[0])} ${f(c1[1])} ${f(c2[0])} ${f(c2[1])} ${f(p2[0])} ${f(p2[1])}`;
    }

    return d;
}

const LOOP_PATH = pathBetween(0, 360);
const TAIL_PATH = pathBetween(360, 360 + TAIL);

/**
 * The drawing is revealed by masks that only rotate (transform), never by
 * stroke-dashoffset, per CLAUDE.md. Three half-planes rotate one after another
 * on a single eased timeline:
 *   0–180°   right half of the loop
 *   180–360° left half of the loop
 *   360°+    the overshooting tail (masked separately so it does not appear
 *            together with the start of the loop)
 */
const PHASES = [
    { from: 0, span: 180 },
    { from: 180, span: 180 },
    { from: 360, span: TAIL_REVEAL },
] as const;

const SAMPLES = 24;

function phaseKeyframes(from: number, span: number): Keyframe[] {
    const ease = cubicBezier(easingPoints.outSoft);

    return Array.from({ length: SAMPLES + 1 }, (_, i) => {
        const offset = i / SAMPLES;
        const angle = Math.min(span, Math.max(0, TOTAL * ease(offset) - from));

        return { offset, transform: `rotate(${angle}deg)` };
    });
}

const rotatorStyle = (deg: number) => ({
    transformBox: 'view-box' as const,
    transformOrigin: `${C}px ${C}px`,
    transform: `rotate(${deg}deg)`,
});

const toneClass: Record<Tone, string> = {
    cocoa: 'text-cocoa',
    rose: 'text-rose',
};

/**
 * Alon Cake's signature moment: a handwritten circle drawn around the chosen
 * date. Decorative only, so the parent must expose the selection itself
 * (e.g. `aria-pressed` or `aria-selected`). Change `key` to redraw.
 *
 * The circle is sized from this wrapper, so give it the size of the date
 * cell (e.g. `className="size-full"` inside a fixed-size button).
 */
export function DateCircle({
    children,
    drawn,
    tone = 'cocoa',
    className,
}: DateCircleProps) {
    const reduced = usePrefersReducedMotion();
    const rotators = useRef<(SVGGElement | null)[]>([]);
    const id = useId().replace(/[^a-zA-Z0-9-]/g, '');

    // Layout effect: start before paint so the final state never flashes.
    useLayoutEffect(() => {
        if (!drawn || reduced) return;

        const animations = PHASES.map(({ from, span }, i) =>
            rotators.current[i]?.animate(phaseKeyframes(from, span), {
                duration: duration.slow,
                easing: 'linear', // already eased via the sampled keyframes
                fill: 'backwards',
            }),
        );

        return () => animations.forEach((a) => a?.cancel());
    }, [drawn, reduced]);

    const halfPlane = (side: 'left' | 'right') => (
        <rect
            x={side === 'left' ? -40 : C}
            y={-40}
            width={100}
            height={200}
            fill="white"
        />
    );

    return (
        <span
            className={cn('relative inline-grid place-items-center', className)}
        >
            {children}
            <svg
                viewBox="0 0 120 120"
                aria-hidden="true"
                focusable="false"
                className={cn(
                    'pointer-events-none absolute top-1/2 left-1/2 size-[135%] -translate-x-1/2 -translate-y-1/2 -rotate-6 overflow-visible',
                    toneClass[tone],
                )}
                style={{
                    opacity: drawn ? 1 : 0,
                    transition: drawn
                        ? 'none'
                        : `opacity ${duration.fast}ms ${easing.out}`,
                }}
            >
                <defs>
                    <clipPath id={`${id}-right`}>{halfPlane('right')}</clipPath>
                    <clipPath id={`${id}-left`}>{halfPlane('left')}</clipPath>

                    <mask
                        id={`${id}-loop`}
                        maskUnits="userSpaceOnUse"
                        x={-40}
                        y={-40}
                        width={200}
                        height={200}
                    >
                        <g clipPath={`url(#${id}-right)`}>
                            <g
                                ref={(el) => {
                                    rotators.current[0] = el;
                                }}
                                style={rotatorStyle(180)}
                            >
                                {halfPlane('left')}
                            </g>
                        </g>
                        <g clipPath={`url(#${id}-left)`}>
                            <g
                                ref={(el) => {
                                    rotators.current[1] = el;
                                }}
                                style={rotatorStyle(180)}
                            >
                                {halfPlane('right')}
                            </g>
                        </g>
                    </mask>

                    <mask
                        id={`${id}-tail`}
                        maskUnits="userSpaceOnUse"
                        x={-40}
                        y={-40}
                        width={200}
                        height={200}
                    >
                        <g clipPath={`url(#${id}-right)`}>
                            <g
                                ref={(el) => {
                                    rotators.current[2] = el;
                                }}
                                style={rotatorStyle(TAIL_REVEAL)}
                            >
                                {halfPlane('left')}
                            </g>
                        </g>
                    </mask>
                </defs>

                <g
                    fill="none"
                    stroke="currentColor"
                    strokeWidth={1.5}
                    strokeLinecap="round"
                    strokeLinejoin="round"
                    vectorEffect="non-scaling-stroke"
                >
                    <path
                        d={LOOP_PATH}
                        mask={`url(#${id}-loop)`}
                        vectorEffect="non-scaling-stroke"
                    />
                    <path
                        d={TAIL_PATH}
                        mask={`url(#${id}-tail)`}
                        vectorEffect="non-scaling-stroke"
                    />
                </g>
            </svg>
        </span>
    );
}
