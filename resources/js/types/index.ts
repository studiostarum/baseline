export * from './auth';
export * from './navigation';
export * from './ui';

import type { Auth } from './auth';

export type AppPageProps<
    T extends Record<string, unknown> = Record<string, unknown>,
> = T & {
    name: string;
    auth: Auth;
    locale: string;
    locales: Record<string, string>;
    translations: Record<string, string>;
    sidebarOpen: boolean;
    [key: string]: unknown;
};
