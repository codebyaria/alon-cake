import type { ElementType, ReactNode } from 'react';
import { useInView } from '@/hooks/use-in-view';
import { cn } from '@/lib/utils';

type RevealProps = {
    as?: ElementType;
    children: ReactNode;
    className?: string;
};

/**
 * Rises gently into place the first time it scrolls into view
 * (opacity + transform, 800 ms ease-out; instant with reduced motion).
 */
export function Reveal({ as: Tag = 'div', children, className }: RevealProps) {
    const [ref, inView] = useInView<HTMLElement>();

    return (
        <Tag
            ref={ref}
            className={cn(inView ? 'animate-rise' : 'opacity-0', className)}
        >
            {children}
        </Tag>
    );
}
