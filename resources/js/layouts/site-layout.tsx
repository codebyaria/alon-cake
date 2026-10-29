import type { ReactNode } from 'react';
import { SiteFooter } from '@/components/site/site-footer';
import { SiteHeader } from '@/components/site/site-header';
import { useT } from '@/lib/i18n';

export default function SiteLayout({ children }: { children: ReactNode }) {
    const t = useT();

    return (
        <div className="min-h-screen bg-cream text-cocoa">
            <a
                href="#isi"
                className="sr-only rounded-full bg-cocoa px-4 py-2 text-bone focus:not-sr-only focus:absolute focus:top-4 focus:left-4 focus:z-50"
            >
                {t('Langsung ke isi')}
            </a>
            <SiteHeader />
            <main id="isi">{children}</main>
            <SiteFooter />
        </div>
    );
}
