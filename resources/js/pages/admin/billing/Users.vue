<script setup lang="ts">
import DataTable, { type Column } from '@/components/admin/DataTable.vue';
import Pagination from '@/components/admin/Pagination.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import AdminLayout from '@/layouts/AdminLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { Eye } from 'lucide-vue-next';
import { computed } from 'vue';

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

const breadcrumbs = computed(() => [
    { title: 'Admin', href: '/admin' },
    { title: 'Billing', href: '/admin/billing' },
    { title: 'Users' },
]);

const columns: Column<User>[] = [
    { key: 'name', label: 'Name', sortable: true },
    { key: 'email', label: 'Email', sortable: true },
    { key: 'subscription', label: 'Subscription' },
    { key: 'createdAt', label: 'Joined', sortable: true },
];

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
    <Head title="Billing Users" />

    <AdminLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 p-4 md:p-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold tracking-tight">
                        Billing Users
                    </h1>
                    <p class="text-muted-foreground">
                        View and manage user subscriptions.
                    </p>
                </div>
            </div>

            <DataTable
                :columns="columns"
                :data="users.data"
                :filters="filters"
                route-name="/admin/billing/users"
                search-placeholder="Search users..."
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
                            (Trial)
                        </span>
                        <span
                            v-if="(item as User).subscription?.onGracePeriod"
                            class="text-xs text-muted-foreground"
                        >
                            (Grace Period)
                        </span>
                        <span
                            v-if="!(item as User).subscription"
                            class="text-sm text-muted-foreground"
                        >
                            No subscription
                        </span>
                    </div>
                </template>

                <template #cell-createdAt="{ value }">
                    {{ formatDate(value as string) }}
                </template>

                <template #actions="{ item }">
                    <div class="flex items-center justify-end gap-2">
                        <Button variant="ghost" size="icon" as-child>
                            <Link
                                :href="`/admin/billing/users/${(item as User).id}`"
                            >
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
    </AdminLayout>
</template>
