import { router, useForm } from '@inertiajs/react';
import type { FormEvent } from 'react';
import { FieldError } from '@/components/order/field-error';
import { PrimaryButton } from '@/components/order/primary-button';
import { StepHeading } from '@/components/order/step-heading';
import OrderLayout from '@/layouts/order-layout';
import { cn } from '@/lib/utils';
import { localePath, useLocale, useT } from '@/lib/i18n';
import type { OrderSummary } from '@/types/site';

type Props = {
    summary: OrderSummary;
    values: { card_message: string; card_from: string };
    messageMax: number;
    fromMax: number;
};

const inputClass =
    'rounded-sm border border-border bg-bone px-4 text-[15px] placeholder:text-[#9C8577] focus-visible:border-cocoa focus-visible:ring-2 focus-visible:ring-ring focus-visible:outline-none';

export default function OrderCard({ values, messageMax, fromMax }: Props) {
    const t = useT();
    const locale = useLocale();
    const url = localePath(locale, '/pesan/kartu');
    const form = useForm(values);

    const submit = (event: FormEvent) => {
        event.preventDefault();
        form.post(url);
    };

    // Personal, never pushed: the card is optional.
    const skip = () => router.post(url, { skip: true });

    return (
        <OrderLayout
            step={4}
            title={t('Kartu pesan')}
            backHref={localePath(locale, '/pesan/detail')}
            footer={
                <form onSubmit={submit} className="flex flex-col gap-2">
                    <PrimaryButton
                        type="submit"
                        disabled={
                            !form.data.card_message.trim() || form.processing
                        }
                    >
                        {t('Simpan pesan')}
                    </PrimaryButton>
                    <button
                        type="button"
                        onClick={skip}
                        className="h-11 rounded-full text-sm text-muted-foreground underline-offset-4 hover:underline focus-visible:ring-2 focus-visible:ring-ring focus-visible:outline-none"
                    >
                        {t('Lewati')}
                    </button>
                </form>
            }
        >
            <StepHeading title={t('Ada pesan untuknya?')}>
                {t(
                    'Kami tuliskan di kartu kecil yang ikut bersama kue. Boleh dilewati.',
                )}
            </StepHeading>

            {/* The card as it will look, written by hand. */}
            <figure
                aria-hidden="true"
                className="mb-8 rotate-[-1.5deg] rounded-sm border border-border bg-bone px-7 py-8 shadow-[0_1px_0_#E4D9C8]"
            >
                <p
                    className={cn(
                        'font-display text-2xl leading-snug italic',
                        !form.data.card_message.trim() && 'opacity-40',
                    )}
                >
                    {form.data.card_message.trim() ||
                        t(
                            'Selamat ulang tahun, Nara. Pelan-pelan saja, semua yang baik akan datang.',
                        )}
                </p>
                <p
                    className={cn(
                        'pt-4 text-right font-display text-2xl italic',
                        !form.data.card_from.trim() && 'opacity-40',
                    )}
                >
                    — {form.data.card_from.trim() || t('Ibu')}
                </p>
            </figure>

            <div className="flex flex-col gap-6">
                <div className="flex flex-col gap-2">
                    <label
                        htmlFor="card_message"
                        className="text-[13px] font-medium"
                    >
                        {t('Pesan')}
                    </label>
                    <textarea
                        id="card_message"
                        rows={4}
                        value={form.data.card_message}
                        maxLength={messageMax}
                        onChange={(e) =>
                            form.setData('card_message', e.target.value)
                        }
                        aria-describedby="card_message_hint card_message_error"
                        className={`${inputClass} resize-none py-3 leading-relaxed`}
                    />
                    <p
                        id="card_message_hint"
                        className="flex justify-end text-xs text-muted-foreground tabular-nums"
                    >
                        {form.data.card_message.length}/{messageMax}
                    </p>
                    <FieldError
                        id="card_message_error"
                        message={form.errors.card_message}
                    />
                </div>

                <div className="flex flex-col gap-2">
                    <label
                        htmlFor="card_from"
                        className="text-[13px] font-medium"
                    >
                        {t('Dari')}
                    </label>
                    <input
                        id="card_from"
                        type="text"
                        value={form.data.card_from}
                        maxLength={fromMax}
                        onChange={(e) =>
                            form.setData('card_from', e.target.value)
                        }
                        className={`${inputClass} h-12`}
                    />
                    <FieldError message={form.errors.card_from} />
                </div>
            </div>
        </OrderLayout>
    );
}
