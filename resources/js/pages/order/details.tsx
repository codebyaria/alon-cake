import { useForm } from '@inertiajs/react';
import type { FormEvent } from 'react';
import { Choice } from '@/components/order/choice';
import { FieldError } from '@/components/order/field-error';
import { PrimaryButton } from '@/components/order/primary-button';
import { PipingLine } from '@/components/piping-line';
import OrderLayout from '@/layouts/order-layout';
import { localePath, useFormat, useLocale, useT } from '@/lib/i18n';
import type { OrderSummary } from '@/types/site';

type Props = {
    summary: OrderSummary;
    sizes: { id: number; label: string; servings: string; price: number }[];
    flavors: { id: number; name: string }[];
    values: {
        size_id: number | null;
        flavor_id: number | null;
        cake_text: string;
    };
    cakeTextMax: number;
};

const sectionLabel = 'text-[13px] font-medium';

export default function OrderDetails({
    summary,
    sizes,
    flavors,
    values,
    cakeTextMax,
}: Props) {
    const t = useT();
    const locale = useLocale();
    const format = useFormat();
    const form = useForm({
        size_id: values.size_id ?? sizes[1]?.id ?? sizes[0]?.id ?? null,
        flavor_id: values.flavor_id ?? flavors[0]?.id ?? null,
        cake_text: values.cake_text,
    });
    const size = sizes.find((s) => s.id === form.data.size_id);
    const flavor = flavors.find((f) => f.id === form.data.flavor_id);

    const submit = (event: FormEvent) => {
        event.preventDefault();
        form.post(localePath(locale, '/pesan/detail'));
    };

    return (
        <OrderLayout
            step={3}
            title={t('Ukuran, rasa, tulisan')}
            backHref={localePath(locale, '/pesan/kue')}
            footer={
                <form onSubmit={submit} className="flex flex-col gap-3.5">
                    <div
                        className="flex items-baseline justify-between gap-4 text-sm"
                        aria-live="polite"
                    >
                        <span>
                            {size?.label} · {flavor?.name}
                        </span>
                        <span className="font-medium">
                            {size && format.rupiah(size.price)}
                        </span>
                    </div>
                    <PrimaryButton type="submit" disabled={form.processing}>
                        {t('Lanjut ke kartu pesan')}
                    </PrimaryButton>
                </form>
            }
        >
            {/* Preview: the writing as it would sit on the cream top. */}
            <figure className="mb-8 flex flex-col items-center gap-3">
                <div className="flex aspect-[2/1] w-full flex-col items-center justify-center gap-4 rounded-sm bg-bone px-6">
                    <span className="text-center font-display text-[28px] leading-tight break-words italic">
                        {form.data.cake_text.trim() || t('tanpa tulisan')}
                    </span>
                    <PipingLine
                        variant="scallop"
                        wavelength={14}
                        amplitude={6}
                        className="max-w-[200px]"
                    />
                </div>
                <figcaption className="text-xs text-muted-foreground">
                    {summary.cake} · {t('Pratinjau tulisan di atas kue')}
                </figcaption>
            </figure>

            <div className="flex flex-col gap-8">
                <fieldset className="flex flex-col gap-3">
                    <legend className={`${sectionLabel} pb-3`}>
                        {t('Ukuran')}
                    </legend>
                    <div className="grid grid-cols-3 gap-2.5">
                        {sizes.map((s) => (
                            <Choice
                                key={s.id}
                                name="size_id"
                                value={s.id}
                                checked={form.data.size_id === s.id}
                                onChange={() => form.setData('size_id', s.id)}
                                className="flex-col items-center gap-1 px-2 py-3.5 text-center"
                            >
                                <span className="text-[15px] font-medium">
                                    {s.label}
                                </span>
                                <span className="text-xs text-muted-foreground">
                                    {s.servings}
                                </span>
                            </Choice>
                        ))}
                    </div>
                    <FieldError message={form.errors.size_id} />
                </fieldset>

                <fieldset className="flex flex-col gap-3">
                    <legend className={`${sectionLabel} pb-3`}>
                        {t('Rasa')}
                    </legend>
                    <div className="flex flex-wrap gap-2.5">
                        {flavors.map((f) => (
                            <Choice
                                key={f.id}
                                name="flavor_id"
                                value={f.id}
                                checked={form.data.flavor_id === f.id}
                                onChange={() => form.setData('flavor_id', f.id)}
                                className="rounded-full px-5 py-2.5 text-sm"
                            >
                                {f.name}
                            </Choice>
                        ))}
                    </div>
                    <FieldError message={form.errors.flavor_id} />
                </fieldset>

                <div className="flex flex-col gap-2">
                    <label htmlFor="cake_text" className={sectionLabel}>
                        {t('Tulisan di kue')}
                    </label>
                    <input
                        id="cake_text"
                        type="text"
                        value={form.data.cake_text}
                        maxLength={cakeTextMax}
                        onChange={(e) =>
                            form.setData('cake_text', e.target.value)
                        }
                        placeholder="happy birthday, Nara"
                        aria-describedby="cake_text_hint cake_text_error"
                        className="h-12 rounded-sm border border-border bg-bone px-4 text-[15px] placeholder:text-[#9C8577] focus-visible:border-cocoa focus-visible:ring-2 focus-visible:ring-ring focus-visible:outline-none"
                    />
                    <p
                        id="cake_text_hint"
                        className="flex justify-between text-xs text-muted-foreground"
                    >
                        <span>
                            {t(
                                'Ditulis tangan dengan cokelat. Boleh dikosongkan.',
                            )}
                        </span>
                        <span aria-hidden="true" className="tabular-nums">
                            {form.data.cake_text.length}/{cakeTextMax}
                        </span>
                    </p>
                    <FieldError
                        id="cake_text_error"
                        message={form.errors.cake_text}
                    />
                </div>
            </div>
        </OrderLayout>
    );
}
