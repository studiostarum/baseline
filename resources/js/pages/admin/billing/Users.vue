<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { Eye } from 'lucide-vue-next';
import { computed } from 'vue';
import DataTable, { type Column } from '@/components/admin/DataTable.vue';
import Pagination from '@/components/admin/Pagination.vue';
import AppHead from '@/components/AppHead.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { useFormatDate } from '@/composables/useFormatDate';
import { useTranslations } from '@/composables/useTranslations';
import AppLayout from '@/layouts/AppLayout.vue';
import { index as billingIndex, show as billingShow } from '@/routes/admin/billing';

type Subscription = {
    status: string;
    onTrial: boolean;
    onGracePeriod: boolean;
};

type User = {
    id: number;
    name: string;
    email: string;
    createdAt: string;
    subscription: Subscription | null;
};

type PaginatedUsers = {
    data: User[];
    links: Array<{ url: string | null; label: string; active: boolean }>;
    from: number;
    to: number;
    total: number;
};

type Props = {
    users: PaginatedUsers;
    filters: Record<string, string>;
};

defineProps<Props>();

const { t } = useTranslations();
const { formatDate } = useFormatDate();

const breadcrumbs = computed(() => [
    { title: t('admin.breadcrumb'), href: '/admin' },
    { title: t('admin.navigation.billing_dashboard'), href: billingIndex.url() },
    { title: t('admin.billing.users.title') },
]);

const columns = computed<Column<User>[]>(() => [
    { key: 'name', label: t('admin.billing.users.column_name'), sortable: true },
    { key: 'email', label: t('admin.billing.users.column_email'), sortable: true },
    { key: 'subscription', label: t('admin.billing.users.column_subscription') },
    { key: 'createdAt', label: t('admin.billing.users.column_joined'), sortable: true },
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

const statusKeyMap: Record<string, string> = {
    active: 'admin.billing.status_active',
    trialing: 'admin.billing.status_trialing',
    canceled: 'admin.billing.status_canceled',
    past_due: 'admin.billing.status_past_due',
    unpaid: 'admin.billing.status_unpaid',
};

function formatStatus(status: string): string {
    const key = statusKeyMap[status];
    return key ? t(key) : status.replace(/_/g, ' ').replace(/\b\w/g, (l) => l.toUpperCase());
}

</script>

<template>
    <AppHead :title="t('admin.billing.users.head_title')" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 p-4 md:p-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold tracking-tight">
                        {{ t('admin.billing.users.title') }}
                    </h1>
                    <p class="text-muted-foreground">
                        {{ t('admin.billing.users.description') }}
                    </p>
                </div>
            </div>

            <DataTable
                :columns="columns"
                :data="users.data"
                :filters="filters"
                route-name="/admin/billing/users"
                :search-placeholder="t('admin.billing.users.search_placeholder')"
            >
                <template #cell-subscription="{ item }">
                    <div class="flex items-center gap-2">
                        <Badge
                            v-if="(item as User).subscription"
                            :variant="
                                getStatusVariant(
                                    (item as User).subscription!.status,
                                )
                            "
                        >
                            {{
                                formatStatus(
                                    (item as User).subscription!.status,
                                )
                            }}
                        </Badge>
                        <span
                            v-if="(item as User).subscription?.onTrial"
                            class="text-xs text-muted-foreground"
                        >
                            ({{ t('admin.billing.users.trial') }})
                        </span>
                        <span
                            v-if="(item as User).subscription?.onGracePeriod"
                            class="text-xs text-muted-foreground"
                        >
                            ({{ t('admin.billing.users.grace_period') }})
                        </span>
                        <span
                            v-if="!(item as User).subscription"
                            class="text-sm text-muted-foreground"
                        >
                            {{ t('admin.billing.users.no_subscription') }}
                        </span>
                    </div>
                </template>

                <template #cell-createdAt="{ value }">
                    {{ formatDate(value as string) }}
                </template>

                <template #actions="{ item }">
                    <div class="flex items-center justify-end gap-2">
                        <Button variant="ghost" size="icon" as-child>
                            <Link :href="billingShow.url((item as User).id)">
                                <Eye class="h-4 w-4" />
                            </Link>
                        </Button>
                    </div>
                </template>
            </DataTable>

            <Pagination
                v-if="users.data.length > 0"
                :links="users.links"
                :from="users.from"
                :to="users.to"
                :total="users.total"
            />
        </div>
    </AppLayout>
</template>
