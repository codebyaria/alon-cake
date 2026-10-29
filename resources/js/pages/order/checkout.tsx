import { Link, useForm, usePage } from '@inertiajs/react';
import type { FormEvent } from 'react';
import { Choice } from '@/components/order/choice';
import { FieldError } from '@/components/order/field-error';
import { PrimaryButton } from '@/components/order/primary-button';
import { StepHeading } from '@/components/order/step-heading';
import OrderLayout from '@/layouts/order-layout';
import { localePath, useFormat, useLocale, useT } from '@/lib/i18n';
import type { OrderSummary } from '@/types/site';

type Option = { value: string; label: string };

type Props = {
    summary: OrderSummary;
    slots: Option[];
    payments: Option[];
    deliveryFee: number;
};

const inputClass =
    'rounded-sm border border-border bg-bone px-4 text-[15px] placeholder:text-[#9C8577] focus-visible:border-cocoa focus-visible:ring-2 focus-visible:ring-ring focus-visible:outline-none';
const sectionLabel = 'pb-3 text-[13px] font-medium';

export default function OrderCheckout({
    summary,
    slots,
    payments,
    deliveryFee,
}: Props) {
    const t = useT();
    const locale = useLocale();
    const format = useFormat();
    const form = useForm({
        fulfillment: 'pickup',
        time_slot: slots[0]?.value ?? '',
        delivery_address: '',
        customer_name: '',
        customer_whatsapp: '',
        payment_method: payments[0]?.value ?? '',
    });
    // Set by the server when the date filled up while this customer was ordering.
    const dateError = (
        usePage().props.errors as Record<string, string | undefined>
    ).date;
    const delivery = form.data.fulfillment === 'delivery';
    const fee = delivery ? deliveryFee : 0;
    const total = (summary.cakePrice ?? 0) + fee;
    const day = format.date(summary.date, {
        weekday: 'long',
        day: 'numeric',
        month: 'long',
    });

    const submit = (event: FormEvent) => {
        event.preventDefault();
        form.post(localePath(locale, '/pesan/bayar'));
    };

    const includes = [
        summary.flavor,
        summary.cakeText && t('tulisan'),
        summary.hasCard && t('kartu pesan'),
    ]
        .filter(Boolean)
        .join(' · ');

    return (
        <OrderLayout
            step={5}
            title={t('Ambil atau antar, lalu bayar')}
            backHref={localePath(locale, '/pesan/kartu')}
            footer={
                <form onSubmit={submit} className="flex flex-col gap-3">
                    <p className="text-xs leading-relaxed text-muted-foreground">
                        {t(
                            'Bayar dengan QRIS atau transfer bank. Pesanan tercatat setelah pembayaran.',
                        )}{' '}
                        <span className="font-medium text-cocoa">
                            {t(
                                'Demo: pembayaran disimulasikan, tidak ada uang yang berpindah.',
                            )}
                        </span>
                    </p>
                    <PrimaryButton type="submit" disabled={form.processing}>
                        {form.processing
                            ? t('Mencatat pesanan…')
                            : t('Bayar :total', {
                                  total: format.rupiah(total),
                              })}
                    </PrimaryButton>
                </form>
            }
        >
            <StepHeading title={t('Hampir selesai')} />

            {dateError && (
                <div
                    role="alert"
                    className="mb-6 flex flex-col gap-2 rounded-sm border border-[#9B3B2E]/40 bg-bone p-4 text-sm"
                >
                    <p>{dateError}</p>
                    <Link
                        href={localePath(locale, '/pesan')}
                        className="self-start font-medium underline underline-offset-4"
                    >
                        {t('Pilih tanggal lain')}
                    </Link>
                </div>
            )}

            <div className="flex flex-col gap-8">
                <fieldset>
                    <legend className="sr-only">{t('Ambil atau antar')}</legend>
                    <div className="grid grid-cols-2 gap-2.5">
                        {(['pickup', 'delivery'] as const).map((value) => (
                            <Choice
                                key={value}
                                name="fulfillment"
                                value={value}
                                checked={form.data.fulfillment === value}
                                onChange={() =>
                                    form.setData('fulfillment', value)
                                }
                                className="justify-center rounded-full py-3 text-sm font-medium"
                            >
                                {value === 'pickup'
                                    ? t('Ambil sendiri')
                                    : t('Diantar')}
                            </Choice>
                        ))}
                    </div>
                    <FieldError message={form.errors.fulfillment} />
                </fieldset>

                <fieldset className="flex flex-col">
                    <legend className={sectionLabel}>
                        {delivery
                            ? t('Jam antar, :day', { day })
                            : t('Jam ambil, :day', { day })}
                    </legend>
                    <div className="grid grid-cols-3 gap-2.5">
                        {slots.map((slot) => (
                            <Choice
                                key={slot.value}
                                name="time_slot"
                                value={slot.value}
                                checked={form.data.time_slot === slot.value}
                                onChange={() =>
                                    form.setData('time_slot', slot.value)
                                }
                                className="justify-center py-3 text-[13px] tabular-nums"
                            >
                                {slot.label}
                            </Choice>
                        ))}
                    </div>
                    <FieldError message={form.errors.time_slot} />
                </fieldset>

                {delivery && (
                    <div className="flex flex-col gap-2">
                        <label
                            htmlFor="delivery_address"
                            className="text-[13px] font-medium"
                        >
                            {t('Alamat pengantaran')}
                        </label>
                        <textarea
                            id="delivery_address"
                            rows={3}
                            value={form.data.delivery_address}
                            onChange={(e) =>
                                form.setData('delivery_address', e.target.value)
                            }
                            autoComplete="street-address"
                            className={`${inputClass} resize-none py-3`}
                        />
                        <FieldError message={form.errors.delivery_address} />
                    </div>
                )}

                <div className="flex flex-col gap-2">
                    <label
                        htmlFor="customer_name"
                        className="text-[13px] font-medium"
                    >
                        {t('Nama')}
                    </label>
                    <input
                        id="customer_name"
                        type="text"
                        value={form.data.customer_name}
                        onChange={(e) =>
                            form.setData('customer_name', e.target.value)
                        }
                        autoComplete="name"
                        className={`${inputClass} h-12`}
                    />
                    <FieldError message={form.errors.customer_name} />
                </div>

                <div className="flex flex-col gap-2">
                    <label
                        htmlFor="customer_whatsapp"
                        className="text-[13px] font-medium"
                    >
                        WhatsApp
                    </label>
                    <input
                        id="customer_whatsapp"
                        type="tel"
                        inputMode="tel"
                        value={form.data.customer_whatsapp}
                        onChange={(e) =>
                            form.setData('customer_whatsapp', e.target.value)
                        }
                        autoComplete="tel"
                        placeholder="0812 3456 7890"
                        aria-describedby="whatsapp_hint"
                        className={`${inputClass} h-12`}
                    />
                    <p
                        id="whatsapp_hint"
                        className="text-xs text-muted-foreground"
                    >
                        {t('Untuk kabar di setiap tahap pembuatan kue.')}
                    </p>
                    <FieldError message={form.errors.customer_whatsapp} />
                </div>

                <dl className="flex flex-col gap-3 border-t border-border pt-6 text-sm">
                    <div className="flex justify-between gap-4">
                        <dt>
                            {summary.cake} · {summary.size}
                        </dt>
                        <dd>
                            {summary.cakePrice !== null &&
                                format.rupiah(summary.cakePrice)}
                        </dd>
                    </div>
                    <div className="flex justify-between gap-4 text-muted-foreground">
                        <dt>{includes}</dt>
                        <dd>{t('Termasuk')}</dd>
                    </div>
                    <div className="flex justify-between gap-4 text-muted-foreground">
                        <dt>{delivery ? t('Diantar') : t('Ambil sendiri')}</dt>
                        <dd>{delivery ? format.rupiah(fee) : t('Gratis')}</dd>
                    </div>
                    <div className="flex justify-between gap-4 border-t border-border pt-3 text-base font-medium">
                        <dt>{t('Total')}</dt>
                        <dd aria-live="polite">{format.rupiah(total)}</dd>
                    </div>
                </dl>

                <fieldset>
                    <legend className={sectionLabel}>{t('Cara bayar')}</legend>
                    <div className="grid grid-cols-2 gap-2.5">
                        {payments.map((method) => (
                            <Choice
                                key={method.value}
                                name="payment_method"
                                value={method.value}
                                checked={
                                    form.data.payment_method === method.value
                                }
                                onChange={() =>
                                    form.setData('payment_method', method.value)
                                }
                                className="justify-center py-3 text-sm"
                            >
                                {method.label}
                            </Choice>
                        ))}
                    </div>
                    <FieldError message={form.errors.payment_method} />
                </fieldset>
            </div>
        </OrderLayout>
    );
}
