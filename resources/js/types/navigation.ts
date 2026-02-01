import type { InertiaLinkProps } from '@inertiajs/vue3';
import type { LucideIcon } from 'lucide-vue-next';
import type { Ref } from 'vue';

export type BreadcrumbItem = {
    title: string;
    href?: string;
};

export type NavItem = {
    title: string | Ref<string>;
    href: NonNullable<InertiaLinkProps['href']>;
    icon?: LucideIcon;
    isActive?: boolean;
};
