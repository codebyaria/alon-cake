import { cn } from '@/lib/utils';

type PhotoFrameProps = {
    src?: string | null;
    /** Responsive candidates, e.g. "/images/hero-600.webp 600w, /images/hero-1100.webp 1100w". */
    srcSet?: string;
    sizes?: string;
    /** Above-the-fold photo: load eagerly and ask for it first (LCP). */
    priority?: boolean;
    alt: string;
    /** What the still photo should show, displayed until a real photo is uploaded. */
    placeholder: string;
    className?: string;
};

/**
 * Still photos only (CLAUDE.md: no video). Until real photography exists, the
 * frame shows an honest description of the intended shot instead of stock art.
 */
export function PhotoFrame({
    src,
    srcSet,
    sizes,
    priority = false,
    alt,
    placeholder,
    className,
}: PhotoFrameProps) {
    if (src) {
        return (
            <img
                src={src}
                srcSet={srcSet}
                sizes={sizes}
                alt={alt}
                loading={priority ? 'eager' : 'lazy'}
                fetchPriority={priority ? 'high' : 'auto'}
                decoding="async"
                className={cn('rounded-sm object-cover', className)}
            />
        );
    }

    return (
        <div
            role="img"
            aria-label={alt}
            className={cn(
                'flex items-center justify-center rounded-sm bg-[#E9E1D3] p-8 text-center text-sm leading-relaxed text-muted-foreground',
                className,
            )}
        >
            <span aria-hidden="true">[{placeholder}]</span>
        </div>
    );
}
