import { useEffect, useRef, useState } from 'react';

/**
 * True once the element has scrolled into view (and stays true).
 * Used to start reveals when a section appears, not on page load.
 */
export function useInView<T extends Element>(rootMargin = '0px 0px -15% 0px') {
    const ref = useRef<T>(null);
    const [inView, setInView] = useState(false);

    useEffect(() => {
        const node = ref.current;
        if (!node || inView) return;

        const observer = new IntersectionObserver(
            ([entry]) => {
                if (entry.isIntersecting) {
                    setInView(true);
                    observer.disconnect();
                }
            },
            { rootMargin },
        );

        observer.observe(node);

        return () => observer.disconnect();
    }, [inView, rootMargin]);

    return [ref, inView] as const;
}
