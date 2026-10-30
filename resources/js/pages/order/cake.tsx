import { useForm } from '@inertiajs/react';
import type { FormEvent } from 'react';
import { Choice } from '@/components/order/choice';
import { useDateLine } from '@/components/order/date-line';
import { FieldError } from '@/components/order/field-error';
import { PrimaryButton } from '@/components/order/primary-button';
import { StepHeading } from '@/components/order/step-heading';
import { PhotoFrame } from '@/components/site/photo-frame';
import OrderLayout from '@/layouts/order-layout';
import { localePath, useFormat, useLocale, useT } from '@/lib/i18n';
import { cn } from '@/lib/utils';
import type { OrderSummary } from '@/types/site';

type Cake = {
    id: number;
    name: string;
    description: string;
    decorNote: string;
    priceFrom: number;
    photoUrl: string | null;
};

type Props = { summary: OrderSummary; cakes: Cake[]; selected: number | null };

export default function OrderCake({ summary, cakes, selected }: Props) {
    const t = useT();
    const locale = useLocale();
    const format = useFormat();
    const dateLine = useDateLine(summary.date);
    const form = useForm({ cake_id: selected ?? cakes[0]?.id ?? null });

    const submit = (event: FormEvent) => {
        event.preventDefault();
        form.post(localePath(locale, '/pesan/kue'));
    };

    return (
        <OrderLayout
            step={2}
            title={t('Pilih kue')}
            backHref={`${localePath(locale, '/pesan')}?tanggal=${summary.date}`}
            footer={
                <form onSubmit={submit}>
                    <PrimaryButton
                        type="submit"
                        disabled={!form.data.cake_id || form.processing}
                    >
                        {t('Atur ukuran dan tulisan')}
                    </PrimaryButton>
                </form>
            }
        >
            <StepHeading title={t('Kue yang mana?')}>
                {dateLine} {t('Hanya tiga pilihan, semuanya dihias tangan.')}
            </StepHeading>

            <fieldset className="flex flex-col gap-3.5">
                <legend className="sr-only">{t('Kue')}</legend>
                {cakes.map((cake) => (
                    <Choice
                        key={cake.id}
                        name="cake_id"
                        value={cake.id}
                        checked={form.data.cake_id === cake.id}
                        onChange={() => form.setData('cake_id', cake.id)}
                        className="items-center gap-4 p-3.5"
                    >
                        <PhotoFrame
                            src={cake.photoUrl}
                            alt=""
                            placeholder={t('Foto')}
                            className={cn(
                                'size-24 shrink-0 text-[11px]',
                                !cake.photoUrl && 'p-2',
                            )}
                        />
                        <span className="flex flex-col gap-1.5">
                            <span className="font-display text-2xl leading-tight">
                                {cake.name}
                            </span>
                            <span className="text-[13px] text-[#5C4134]">
                                {cake.decorNote}
                            </span>
                            <span className="text-[13px]">
                                {t('mulai :price', {
                                    price: format.rupiah(cake.priceFrom),
                                })}
                            </span>
                        </span>
                    </Choice>
                ))}
            </fieldset>
            <FieldError message={form.errors.cake_id} />
        </OrderLayout>
    );
}
