export {};

declare module '@inertiajs/core' {
    export interface InertiaConfig {
        sharedPageProps: {
            name: string;
            locale: 'id' | 'en';
            translations: Record<string, string>;
            [key: string]: unknown;
        };
    }
}
