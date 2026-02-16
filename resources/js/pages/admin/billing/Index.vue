<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { CreditCard, TrendingUp, Users, XCircle } from 'lucide-vue-next';
import { computed } from 'vue';
import StatCard from '@/components/app/admin/StatCard.vue';
import AppHead from '@/components/shared/AppHead.vue';
import { Avatar, AvatarFallback } from '@/components/shared/ui/avatar';
import { Badge } from '@/components/shared/ui/badge';
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

type RecentSubscription = {
    id: number;
    user: {
        id: number;
        name: string;
        email: string;
    } | null;
    type: string;
    status: string;
    createdAt: string;
};

type Props = {
    stats: {
        active: number;
        trialing: number;
        canceled: number;
        total: number;
    };
    recentSubscriptions: RecentSubscription[];
};

defineProps<Props>();

const { t } = useTranslations();
const { formatDate } = useFormatDate();
const { getInitials } = useInitials();

const breadcrumbs = computed(() => [
    { title: t('admin.breadcrumb'), href: '/admin' },
    { title: t('admin.navigation.billing_dashboard') },
]);

const statusKeyMap: Record<string, string> = {
    active: 'admin.billing.status_active',
    trialing: 'admin.billing.status_trialing',
    canceled: 'admin.billing.status_canceled',
    past_due: 'admin.billing.status_past_due',
    unpaid: 'admin.billing.status_unpaid',
};

function getTranslatedStatus(status: string): string {
    const key = statusKeyMap[status];
    return key ? t(key) : status.replace(/_/g, ' ').replace(/\b\w/g, (l) => l.toUpperCase());
}

function getStatusVariant(
    status: string,
): 'default' | 'secondary' | 'destructive' | 'outline' {
    switch (status) {
        case 'active':
        case 'trialing':
            return 'default';
        case 'past_due':
        case 'unpaid':
            return 'destructive';
        case 'canceled':
            return 'secondary';
        default:
            return 'outline';
    }
}

</script>

<template>
    <AppHead :title="t('admin.billing.head_title')" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 p-4 md:p-6">
            <div>
                <h1 class="text-2xl font-bold tracking-tight">
                    {{ t('admin.billing.title') }}
                </h1>
                <p class="text-muted-foreground">
                    {{ t('admin.billing.description') }}
                </p>
            </div>

            <div class="grid gap-4 md:grid-cols-4">
                <StatCard
                    :title="t('admin.billing.stats.active')"
                    :value="stats.active"
                    :description="t('admin.billing.stats.active_description')"
                    :icon="TrendingUp"
                />
                <StatCard
                    :title="t('admin.billing.stats.trialing')"
                    :value="stats.trialing"
                    :description="t('admin.billing.stats.trialing_description')"
                    :icon="CreditCard"
                />
                <StatCard
                    :title="t('admin.billing.stats.canceled')"
                    :value="stats.canceled"
                    :description="t('admin.billing.stats.canceled_description')"
                    :icon="XCircle"
                />
                <StatCard
                    :title="t('admin.billing.stats.total')"
                    :value="stats.total"
                    :description="t('admin.billing.stats.total_description')"
                    :icon="Users"
                />
            </div>

            <div class="grid gap-4 md:grid-cols-2">
                <Card>
                    <CardHeader>
                        <CardTitle>{{
                            t('admin.billing.recent_subscriptions')
                        }}</CardTitle>
                        <CardDescription>
                            {{
                                t(
                                    'admin.billing.recent_subscriptions_description',
                                )
                            }}
                        </CardDescription>
                    </CardHeader>
                    <CardContent>
                        <div class="space-y-4">
                            <div
                                v-for="subscription in recentSubscriptions"
                                :key="subscription.id"
                                class="flex items-center gap-4"
                            >
                                <Avatar class="h-9 w-9">
                                    <AvatarFallback>{{
                                        subscription.user
                                            ? getInitials(
                                                  subscription.user.name,
                                              )
                                            : '?'
                                    }}</AvatarFallback>
                                </Avatar>
                                <div class="flex-1 space-y-1">
                                    <Link
                                        v-if="subscription.user"
                                        :href="`/admin/billing/users/${subscription.user.id}`"
                                        class="text-sm leading-none font-medium hover:underline"
                                    >
                                        {{ subscription.user.name }}
                                    </Link>
                                    <p
                                        v-else
                                        class="text-sm leading-none font-medium text-muted-foreground"
                                    >
                                        {{ t('admin.billing.unknown_user') }}
                                    </p>
                                    <p
                                        v-if="subscription.user"
                                        class="text-sm text-muted-foreground"
                                    >
                                        {{ subscription.user.email }}
                                    </p>
                                </div>
                                <Badge
                                    :variant="
                                        getStatusVariant(subscription.status)
                                    "
                                >
                                    {{
                                        getTranslatedStatus(subscription.status)
                                    }}
                                </Badge>
                                <div class="text-sm text-muted-foreground">
                                    {{ formatDate(subscription.createdAt) }}
                                </div>
                            </div>
                            <div
                                v-if="recentSubscriptions.length === 0"
                                class="text-center text-muted-foreground"
                            >
                                {{ t('admin.billing.no_subscriptions') }}
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader>
                        <CardTitle>{{
                            t('admin.billing.quick_actions')
                        }}</CardTitle>
                        <CardDescription>
                            {{ t('admin.billing.quick_actions_description') }}
                        </CardDescription>
                    </CardHeader>
                    <CardContent>
                        <div class="grid gap-2">
                            <Link
                                href="/admin/billing/users"
                                class="flex items-center gap-2 rounded-lg border p-3 transition-colors hover:bg-muted/50"
                            >
                                <Users class="h-4 w-4" />
                                <span>{{
                                    t('admin.billing.view_all_subscribers')
                                }}</span>
                            </Link>
                        </div>
                    </CardContent>
                </Card>
            </div>
        </div>
    </AppLayout>
</template>
