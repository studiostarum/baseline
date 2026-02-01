<script setup lang="ts">
import StatCard from '@/components/admin/StatCard.vue';
import { Avatar, AvatarFallback } from '@/components/ui/avatar';
import { Badge } from '@/components/ui/badge';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { useInitials } from '@/composables/useInitials';
import AdminLayout from '@/layouts/AdminLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { CreditCard, TrendingUp, Users, XCircle } from 'lucide-vue-next';
import { computed } from 'vue';

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

const { getInitials } = useInitials();

const breadcrumbs = computed(() => [
    { title: 'Admin', href: '/admin' },
    { title: 'Billing' },
]);

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

function formatStatus(status: string): string {
    return status.replace(/_/g, ' ').replace(/\b\w/g, (l) => l.toUpperCase());
}

function formatDate(dateString: string): string {
    return new Date(dateString).toLocaleDateString('en-US', {
        month: 'short',
        day: 'numeric',
        year: 'numeric',
    });
}
</script>

<template>
    <Head title="Billing Dashboard" />

    <AdminLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 p-4 md:p-6">
            <div>
                <h1 class="text-2xl font-bold tracking-tight">Billing</h1>
                <p class="text-muted-foreground">
                    Overview of subscriptions and payments.
                </p>
            </div>

            <div class="grid gap-4 md:grid-cols-4">
                <StatCard
                    title="Active Subscriptions"
                    :value="stats.active"
                    description="Currently active"
                    :icon="TrendingUp"
                />
                <StatCard
                    title="Trialing"
                    :value="stats.trialing"
                    description="In trial period"
                    :icon="CreditCard"
                />
                <StatCard
                    title="Canceled"
                    :value="stats.canceled"
                    description="Canceled subscriptions"
                    :icon="XCircle"
                />
                <StatCard
                    title="Total"
                    :value="stats.total"
                    description="All subscriptions"
                    :icon="Users"
                />
            </div>

            <div class="grid gap-4 md:grid-cols-2">
                <Card>
                    <CardHeader>
                        <CardTitle>Recent Subscriptions</CardTitle>
                        <CardDescription>
                            Latest subscription activity.
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
                                        Unknown user
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
                                    {{ formatStatus(subscription.status) }}
                                </Badge>
                                <div class="text-sm text-muted-foreground">
                                    {{ formatDate(subscription.createdAt) }}
                                </div>
                            </div>
                            <div
                                v-if="recentSubscriptions.length === 0"
                                class="text-center text-muted-foreground"
                            >
                                No subscriptions yet.
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader>
                        <CardTitle>Quick Actions</CardTitle>
                        <CardDescription>
                            Common billing management tasks.
                        </CardDescription>
                    </CardHeader>
                    <CardContent>
                        <div class="grid gap-2">
                            <Link
                                href="/admin/billing/users"
                                class="flex items-center gap-2 rounded-lg border p-3 transition-colors hover:bg-muted/50"
                            >
                                <Users class="h-4 w-4" />
                                <span>View All Subscribers</span>
                            </Link>
                        </div>
                    </CardContent>
                </Card>
            </div>
        </div>
    </AdminLayout>
</template>
