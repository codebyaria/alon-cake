import { useSyncExternalStore } from 'react';

/**
 * Motion tokens for Alon Cake (see CLAUDE.md > Interaksi).
 *
 * - Durations stay within 600–1000 ms: unhurried, never sluggish.
 * - Ease-out only: things arrive and settle, they never bounce.
 * - Animate transform/opacity only.
 * - Respect prefers-reduced-motion: show the end state, skip the journey.
 *
 * Mirrored as CSS tokens in resources/css/app.css (--ease-alon, --animate-*).
 */

export const duration = {
    /** Small state changes: fades, hovers that matter. */
    fast: 600,
    /** Default for entrances and reveals. */
    base: 800,
    /** Signature moments: the date circle, cake layers. */
    slow: 1000,
} as const;

export type Duration = keyof typeof duration;

type BezierPoints = readonly [number, number, number, number];

export const easingPoints = {
    /** Main ease-out (easeOutQuint-like): a soft, confident landing. */
    out: [0.22, 1, 0.36, 1],
    /** Gentler ease-out for long or large movement. */
    outSoft: [0.33, 1, 0.68, 1],
} as const satisfies Record<string, BezierPoints>;

export type Easing = keyof typeof easingPoints;

const toCss = ([x1, y1, x2, y2]: BezierPoints) =>
    `cubic-bezier(${x1}, ${y1}, ${x2}, ${y2})`;

/** CSS easing strings, ready for `transition` / WAAPI `easing`. */
export const easing: Record<Easing, string> = {
    out: toCss(easingPoints.out),
    outSoft: toCss(easingPoints.outSoft),
};

/**
 * Build a `transition` value limited to compositor-friendly properties.
 *
 * @example transition(['opacity', 'transform'], 'fast')
 */
export function transition(
    properties: ReadonlyArray<'transform' | 'opacity'> = [
        'transform',
        'opacity',
    ],
    speed: Duration = 'base',
    ease: Easing = 'out',
): string {
    return properties
        .map((p) => `${p} ${duration[speed]}ms ${easing[ease]}`)
        .join(', ');
}

/**
 * Evaluate a CSS cubic-bezier in JS, for animations that are sampled by
 * hand (e.g. several elements sharing one eased timeline).
 */
export function cubicBezier(
    [x1, y1, x2, y2]: BezierPoints = easingPoints.out,
): (t: number) => number {
    const bezier = (t: number, a: number, b: number) =>
        3 * a * (1 - t) ** 2 * t + 3 * b * (1 - t) * t ** 2 + t ** 3;
    const slope = (t: number, a: number, b: number) =>
        3 * a * (1 - t) ** 2 + 6 * (b - a) * (1 - t) * t + 3 * (1 - b) * t ** 2;

    return (x: number) => {
        if (x <= 0) return 0;
        if (x >= 1) return 1;

        // Newton–Raphson for t where bezierX(t) = x, then read Y.
        let t = x;
        for (let i = 0; i < 8; i++) {
            const dx = bezier(t, x1, x2) - x;
            const d = slope(t, x1, x2);
            if (Math.abs(dx) < 1e-5 || d === 0) break;
            t = Math.min(1, Math.max(0, t - dx / d));
        }

        return bezier(t, y1, y2);
    };
}

export const REDUCED_MOTION_QUERY = '(prefers-reduced-motion: reduce)';

/** One-off check, safe outside the browser (returns false). */
export function prefersReducedMotion(): boolean {
    return (
        typeof window !== 'undefined' &&
        window.matchMedia(REDUCED_MOTION_QUERY).matches
    );
}

function subscribe(callback: () => void) {
    const query = window.matchMedia(REDUCED_MOTION_QUERY);
    query.addEventListener('change', callback);

    return () => query.removeEventListener('change', callback);
}

/** Live `prefers-reduced-motion` value that updates when the OS setting changes. */
export function usePrefersReducedMotion(): boolean {
    return useSyncExternalStore(subscribe, prefersReducedMotion, () => false);
}

/** Duration in ms, collapsed to 0 when the user prefers reduced motion. */
export function motionDuration(speed: Duration, reduced: boolean): number {
    return reduced ? 0 : duration[speed];
}
