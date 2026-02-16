<script setup lang="ts">
import AppLogo from '@/components/AppLogo.vue';
import NavFooter from '@/components/NavFooter.vue';
import NavMain from '@/components/NavMain.vue';
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
import { useTranslations } from '@/composables/useTranslations';
import { dashboard as appDashboard } from '@/routes';
import { dashboard as adminDashboard } from '@/routes/admin';
import { index as billingIndex, users as billingUsers } from '@/routes/admin/billing';
import { index as rolesIndex } from '@/routes/admin/roles';
import { index as settingsIndex } from '@/routes/admin/settings';
import { index as usersIndex } from '@/routes/admin/users';
import { type NavItem } from '@/types';
import { Link, usePage } from '@inertiajs/vue3';
import {
    ArrowLeft,
    CreditCard,
    LayoutDashboard,
    LayoutGrid,
    Settings,
    Shield,
    Users,
} from 'lucide-vue-next';
import { computed } from 'vue';
import { isRef, type Ref } from 'vue';

const page = usePage();
const { isCurrentUrl } = useCurrentUrl();
const { t } = useTranslations();

const isAdminArea = computed(() => page.url.startsWith('/admin'));
const showBilling = computed(() => page.props.features?.billing !== false);
const showAdmin = computed(
    () =>
        page.props.auth?.can_access_admin === true &&
        page.props.features?.admin !== false,
);

const getTitle = (title: string | Ref<string>): string => {
    return isRef(title) ? title.value : title;
};

const mainNavItems = computed<NavItem[]>(() => [
    {
        title: t('navigation.dashboard'),
        href: appDashboard(),
        icon: LayoutGrid,
    },
]);

const adminNavItems = computed<NavItem[]>(() => [
    {
        title: t('navigation.admin'),
        href: '/admin',
        icon: Shield,
    },
]);

const overviewNavItems = computed<NavItem[]>(() => [
    {
        title: t('admin.navigation.dashboard'),
        href: adminDashboard.url(),
        icon: LayoutDashboard,
    },
]);

const settingsNavItems = computed<NavItem[]>(() => [
    {
        title: t('admin.navigation.settings'),
        href: settingsIndex.url(),
        icon: Settings,
    },
]);

const userManagementNavItems = computed<NavItem[]>(() => [
    {
        title: t('admin.navigation.users'),
        href: usersIndex.url(),
        icon: Users,
    },
    {
        title: t('admin.navigation.roles'),
        href: rolesIndex.url(),
        icon: Shield,
    },
]);

const billingNavItems = computed<NavItem[]>(() => [
    {
        title: t('admin.navigation.billing_dashboard'),
        href: billingIndex.url(),
        icon: CreditCard,
    },
    {
        title: t('admin.navigation.billing_users'),
        href: billingUsers.url(),
        icon: Users,
    },
]);

const footerNavItems = computed<NavItem[]>(() => [
    {
        title: t('admin.navigation.back_to_app'),
        href: appDashboard.url(),
        icon: ArrowLeft,
    },
]);
</script>

<template>
    <Sidebar collapsible="icon" variant="inset">
        <SidebarHeader>
            <SidebarMenu>
                <SidebarMenuItem>
                    <SidebarMenuButton size="lg" as-child>
                        <Link :href="isAdminArea ? adminDashboard.url() : appDashboard()">
                            <AppLogo />
                        </Link>
                    </SidebarMenuButton>
                </SidebarMenuItem>
            </SidebarMenu>
        </SidebarHeader>

        <SidebarContent>
            <template v-if="isAdminArea">
                <SidebarGroup class="px-2 py-0">
                    <SidebarGroupLabel>{{ t('admin.sidebar.overview') }}</SidebarGroupLabel>
                    <SidebarMenu>
                        <SidebarMenuItem
                            v-for="item in overviewNavItems"
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
                <SidebarGroup class="px-2 py-0">
                    <SidebarGroupLabel>{{ t('admin.sidebar.user_management') }}</SidebarGroupLabel>
                    <SidebarMenu>
                        <SidebarMenuItem
                            v-for="item in userManagementNavItems"
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
                <SidebarGroup
                    v-if="showBilling"
                    class="px-2 py-0"
                >
                    <SidebarGroupLabel>{{ t('admin.sidebar.billing') }}</SidebarGroupLabel>
                    <SidebarMenu>
                        <SidebarMenuItem
                            v-for="item in billingNavItems"
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
                <SidebarGroup class="px-2 py-0">
                    <SidebarGroupLabel>{{ t('admin.sidebar.settings') }}</SidebarGroupLabel>
                    <SidebarMenu>
                        <SidebarMenuItem
                            v-for="item in settingsNavItems"
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
            </template>
            <NavMain
                v-else
                :items="mainNavItems"
            />
        </SidebarContent>

        <SidebarFooter>
            <NavFooter
                v-if="isAdminArea"
                :items="footerNavItems"
            />
            <NavFooter
                v-else-if="showAdmin"
                :items="adminNavItems"
            />
            <NavUser />
        </SidebarFooter>
    </Sidebar>
    <slot />
</template>
