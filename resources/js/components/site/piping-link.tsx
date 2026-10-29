import { Link } from '@inertiajs/react';
import type { ComponentProps } from 'react';
import { cn } from '@/lib/utils';

/**
 * A text link whose underline is a small piping wave, shown on hover/focus
 * (style tile: "garis bawah tautan saat hover").
 */
export function PipingLink({
    className,
    children,
    ...props
}: ComponentProps<typeof Link>) {
    return (
        <Link
            {...props}
            className={cn(
                'group inline-flex flex-col gap-1 rounded-sm focus-visible:ring-2 focus-visible:ring-ring focus-visible:outline-none',
                className,
            )}
        >
            {children}
            <svg
                aria-hidden="true"
                viewBox="0 0 110 8"
                preserveAspectRatio="none"
                className="h-1.5 w-full opacity-0 transition-opacity duration-600 ease-alon group-hover:opacity-100 group-focus-visible:opacity-100"
            >
                <path
                    d="M0 4 Q 5 0 10 4 T 20 4 T 30 4 T 40 4 T 50 4 T 60 4 T 70 4 T 80 4 T 90 4 T 100 4 T 110 4"
                    fill="none"
                    stroke="currentColor"
                    strokeWidth="1.2"
                    vectorEffect="non-scaling-stroke"
                />
            </svg>
        </Link>
    );
}
