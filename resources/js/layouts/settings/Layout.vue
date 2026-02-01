<script setup lang="ts">
import Heading from '@/components/Heading.vue';
import { Button } from '@/components/ui/button';
import { Separator } from '@/components/ui/separator';
import { useCurrentUrl } from '@/composables/useCurrentUrl';
import { toUrl } from '@/lib/utils';
import { edit as editAppearance } from '@/routes/appearance';
import { show as showBilling } from '@/routes/billing';
import { edit as editProfile } from '@/routes/profile';
import { show } from '@/routes/two-factor';
import { edit as editPassword } from '@/routes/user-password';
import { type NavItem } from '@/types';
import { Link } from '@inertiajs/vue3';
import {
    CreditCard,
    KeyRound,
    Palette,
    ShieldCheck,
    User,
} from 'lucide-vue-next';
import { isRef, type Ref } from 'vue';

const sidebarNavItems: NavItem[] = [
    {
        title: 'Profile',
        href: editProfile(),
        icon: User,
    },
    {
        title: 'Password',
        href: editPassword(),
        icon: KeyRound,
    },
    {
        title: 'Two-Factor Auth',
        href: show(),
        icon: ShieldCheck,
    },
    {
        title: 'Appearance',
        href: editAppearance(),
        icon: Palette,
    },
    {
        title: 'Billing',
        href: showBilling(),
        icon: CreditCard,
    },
];

const { isCurrentUrl } = useCurrentUrl();

const getTitle = (title: string | Ref<string>): string => {
    return isRef(title) ? title.value : title;
};
</script>

<template>
    <div class="px-4 py-6">
        <Heading
            title="Settings"
            description="Manage your profile and account settings"
        />

        <div class="flex flex-col lg:flex-row lg:space-x-12">
            <aside class="w-full max-w-xl lg:w-48">
                <nav
                    class="flex flex-col space-y-1 space-x-0"
                    aria-label="Settings"
                >
                    <Button
                        v-for="item in sidebarNavItems"
                        :key="toUrl(item.href)"
                        variant="ghost"
                        :class="[
                            'w-full justify-start',
                            { 'bg-muted': isCurrentUrl(item.href) },
                        ]"
                        as-child
                    >
                        <Link :href="item.href">
                            <component
                                v-if="item.icon"
                                :is="item.icon"
                                class="h-4 w-4"
                            />
                            {{ getTitle(item.title) }}
                        </Link>
                    </Button>
                </nav>
            </aside>

            <Separator class="my-6 lg:hidden" />

            <section class="flex-1 space-y-12">
                <slot />
            </section>
        </div>
    </div>
</template>
