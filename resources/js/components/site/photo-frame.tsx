import { cn } from '@/lib/utils';

type PhotoFrameProps = {
    src?: string | null;
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
    alt,
    placeholder,
    className,
}: PhotoFrameProps) {
    if (src) {
        return (
            <img
                src={src}
                alt={alt}
                loading="lazy"
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
