import type { ReactNode } from 'react';
import { cn } from '@/lib/utils';

type ChoiceProps = {
    name: string;
    value: string | number;
    checked: boolean;
    onChange: () => void;
    children: ReactNode;
    className?: string;
};

/**
 * A native radio styled as a card: keyboard and screen readers get a real
 * radio group, sighted users get the bordered card from the wireframe.
 */
export function Choice({
    name,
    value,
    checked,
    onChange,
    children,
    className,
}: ChoiceProps) {
    return (
        <label
            className={cn(
                'relative flex cursor-pointer rounded-sm border transition-colors duration-600 ease-alon has-[:focus-visible]:ring-2 has-[:focus-visible]:ring-ring',
                checked
                    ? 'border-cocoa bg-bone'
                    : 'border-border hover:border-cocoa/50',
                className,
            )}
        >
            <input
                type="radio"
                name={name}
                value={value}
                checked={checked}
                onChange={onChange}
                className="sr-only"
            />
            {children}
        </label>
    );
}
