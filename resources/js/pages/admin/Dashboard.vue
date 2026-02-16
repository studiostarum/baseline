<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { Key, Shield, Users } from 'lucide-vue-next';
import { computed } from 'vue';
import StatCard from '@/components/app/admin/StatCard.vue';
import AppHead from '@/components/shared/AppHead.vue';
import { Avatar, AvatarFallback } from '@/components/shared/ui/avatar';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/shared/ui/card';
import { useInitials } from '@/composables/useInitials';
import { useFormatDate } from '@/composables/useFormatDate';
import { useTranslations } from '@/composables/useTranslations';
import AppLayout from '@/layouts/AppLayout.vue';

const { t } = useTranslations();
const { formatDate } = useFormatDate();

type RecentUser = {
    id: number;
    name: string;
    email: string;
    created_at: string;
};

type Props = {
    stats: {
        users: number;
        roles: number;
        permissions: number;
    };
    recentUsers: RecentUser[];
};

defineProps<Props>();

const { getInitials } = useInitials();

const breadcrumbs = computed(() => [
    { title: t('admin.breadcrumb'), href: '/admin' },
    { title: t('admin.navigation.dashboard') },
]);

</script>

<template>
    <AppHead :title="t('admin.title')" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 p-4 md:p-6">
            <div>
                <h1 class="text-2xl font-bold tracking-tight">
                    {{ t('admin.title') }}
                </h1>
                <p class="text-muted-foreground">
                    {{ t('admin.description') }}
                </p>
            </div>

            <div class="grid gap-4 md:grid-cols-3">
                <StatCard
                    :title="t('admin.stats.total_users')"
                    :value="stats.users"
                    :description="t('admin.stats.users_description')"
                    :icon="Users"
                />
                <StatCard
                    :title="t('admin.stats.roles')"
                    :value="stats.roles"
                    :description="t('admin.stats.roles_description')"
                    :icon="Shield"
                />
                <StatCard
                    :title="t('admin.stats.permissions')"
                    :value="stats.permissions"
                    :description="t('admin.stats.permissions_description')"
                    :icon="Key"
                />
            </div>

            <div class="grid gap-4 md:grid-cols-2">
                <Card>
                    <CardHeader>
                        <CardTitle>{{ t('admin.recent_users.title') }}</CardTitle>
                        <CardDescription>
                            {{ t('admin.recent_users.description') }}
                        </CardDescription>
                    </CardHeader>
                    <CardContent>
                        <div class="space-y-4">
                            <div
                                v-for="user in recentUsers"
                                :key="user.id"
                                class="flex items-center gap-4"
                            >
                                <Avatar class="h-9 w-9">
                                    <AvatarFallback>{{
                                        getInitials(user.name)
                                    }}</AvatarFallback>
                                </Avatar>
                                <div class="flex-1 space-y-1">
                                    <p class="text-sm leading-none font-medium">
                                        {{ user.name }}
                                    </p>
                                    <p class="text-sm text-muted-foreground">
                                        {{ user.email }}
                                    </p>
                                </div>
                                <div class="text-sm text-muted-foreground">
                                    {{ formatDate(user.created_at) }}
                                </div>
                            </div>
                            <div
                                v-if="recentUsers.length === 0"
                                class="text-center text-muted-foreground"
                            >
                                {{ t('admin.recent_users.empty') }}
                            </div>
                        </div>
                    </CardContent>
                </Card>
                <Card>
                    <CardHeader>
                        <CardTitle>{{ t('admin.quick_actions.title') }}</CardTitle>
                        <CardDescription>
                            {{ t('admin.quick_actions.description') }}
                        </CardDescription>
                    </CardHeader>
                    <CardContent>
                        <div class="grid gap-2">
                            <Link
                                href="/admin/users/create"
                                class="flex items-center gap-2 rounded-lg border p-3 transition-colors hover:bg-muted/50"
                            >
                                <Users class="h-4 w-4" />
                                <span>{{ t('admin.quick_actions.create_user') }}</span>
                            </Link>
                            <Link
                                href="/admin/roles/create"
                                class="flex items-center gap-2 rounded-lg border p-3 transition-colors hover:bg-muted/50"
                            >
                                <Shield class="h-4 w-4" />
                                <span>{{ t('admin.quick_actions.create_role') }}</span>
                            </Link>
                            <Link
                                href="/admin/settings"
                                class="flex items-center gap-2 rounded-lg border p-3 transition-colors hover:bg-muted/50"
                            >
                                <Key class="h-4 w-4" />
                                <span>{{ t('admin.quick_actions.manage_settings') }}</span>
                            </Link>
                        </div>
                    </CardContent>
                </Card>
            </div>
        </div>
    </AppLayout>
</template>
