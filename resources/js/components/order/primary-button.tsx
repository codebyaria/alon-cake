import type { ButtonHTMLAttributes } from 'react';
import { cn } from '@/lib/utils';

export function PrimaryButton({
    className,
    ...props
}: ButtonHTMLAttributes<HTMLButtonElement>) {
    return (
        <button
            {...props}
            className={cn(
                'flex h-[54px] w-full items-center justify-center rounded-full bg-cocoa text-[15px] font-medium text-bone transition-opacity duration-600 ease-alon hover:opacity-90 focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 focus-visible:ring-offset-bone focus-visible:outline-none disabled:opacity-40',
                className,
            )}
        />
    );
}
