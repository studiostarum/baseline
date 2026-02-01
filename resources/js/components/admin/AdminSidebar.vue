<script setup lang="ts">
import AppLogo from '@/components/AppLogo.vue';
import NavFooter from '@/components/NavFooter.vue';
import NavUser from '@/components/NavUser.vue';
import {
    Sidebar,
    SidebarContent,
    SidebarFooter,
    SidebarGroup,
    SidebarGroupLabel,
    SidebarHeader,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
} from '@/components/ui/sidebar';
import { useCurrentUrl } from '@/composables/useCurrentUrl';
import { dashboard } from '@/routes';
import { dashboard as adminDashboard } from '@/routes/admin';
import { index as billingIndex } from '@/routes/admin/billing';
import { index as rolesIndex } from '@/routes/admin/roles';
import { index as settingsIndex } from '@/routes/admin/settings';
import { index as usersIndex } from '@/routes/admin/users';
import { type NavItem } from '@/types';
import { Link } from '@inertiajs/vue3';
import {
    ArrowLeft,
    CreditCard,
    LayoutDashboard,
    Settings,
    Shield,
    Users,
} from 'lucide-vue-next';
import { isRef, type Ref } from 'vue';

const { isCurrentUrl } = useCurrentUrl();

const getTitle = (title: string | Ref<string>): string => {
    return isRef(title) ? title.value : title;
};

const adminNavItems: NavItem[] = [
    {
        title: 'Dashboard',
        href: adminDashboard.url(),
        icon: LayoutDashboard,
    },
    {
        title: 'Users',
        href: usersIndex.url(),
        icon: Users,
    },
    {
        title: 'Roles',
        href: rolesIndex.url(),
        icon: Shield,
    },
    {
        title: 'Billing',
        href: billingIndex.url(),
        icon: CreditCard,
    },
    {
        title: 'Settings',
        href: settingsIndex.url(),
        icon: Settings,
    },
];

const footerNavItems: NavItem[] = [
    {
        title: 'Back to App',
        href: dashboard.url(),
        icon: ArrowLeft,
    },
];
</script>

<template>
    <Sidebar collapsible="icon" variant="inset">
        <SidebarHeader>
            <SidebarMenu>
                <SidebarMenuItem>
                    <SidebarMenuButton size="lg" as-child>
                        <Link :href="adminDashboard.url()">
                            <AppLogo />
                        </Link>
                    </SidebarMenuButton>
                </SidebarMenuItem>
            </SidebarMenu>
        </SidebarHeader>

        <SidebarContent>
            <SidebarGroup class="px-2 py-0">
                <SidebarGroupLabel>Admin</SidebarGroupLabel>
                <SidebarMenu>
                    <SidebarMenuItem
                        v-for="item in adminNavItems"
                        :key="item.href"
                    >
                        <SidebarMenuButton
                            as-child
                            :is-active="isCurrentUrl(item.href)"
                            :tooltip="getTitle(item.title)"
                        >
                            <Link :href="item.href">
                                <component :is="item.icon" />
                                <span>{{ getTitle(item.title) }}</span>
                            </Link>
                        </SidebarMenuButton>
                    </SidebarMenuItem>
                </SidebarMenu>
            </SidebarGroup>
        </SidebarContent>

        <SidebarFooter>
            <NavFooter :items="footerNavItems" />
            <NavUser />
        </SidebarFooter>
    </Sidebar>
    <slot />
</template>
