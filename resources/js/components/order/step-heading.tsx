import type { ReactNode } from 'react';

export function StepHeading({
    title,
    children,
}: {
    title: string;
    children?: ReactNode;
}) {
    return (
        <div className="flex flex-col gap-2.5 pb-7">
            <h1 className="font-display text-4xl leading-[1.1]">{title}</h1>
            {children && (
                <p className="text-sm leading-relaxed text-[#5C4134]">
                    {children}
                </p>
            )}
        </div>
    );
}
