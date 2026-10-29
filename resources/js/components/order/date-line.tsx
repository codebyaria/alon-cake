import { useFormat, useT } from '@/lib/i18n';

/** "Untuk Sabtu, 14 November." — the date stays visible on every step. */
export function useDateLine(date: string) {
    const t = useT();
    const format = useFormat();

    return t('Untuk :date.', {
        date: format.date(date, {
            weekday: 'long',
            day: 'numeric',
            month: 'long',
        }),
    });
}
