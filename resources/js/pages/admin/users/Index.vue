<script setup lang="ts">
import ConfirmDialog from '@/components/admin/ConfirmDialog.vue';
import DataTable, { type Column } from '@/components/admin/DataTable.vue';
import Pagination from '@/components/admin/Pagination.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import AdminLayout from '@/layouts/AdminLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { Pencil, Plus, Trash2 } from 'lucide-vue-next';
import { computed, ref } from 'vue';

type Role = {
    id: number;
    name: string;
};

type User = {
    id: number;
    name: string;
    email: string;
    created_at: string;
    roles: Role[];
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
    { title: 'Users' },
]);

const columns = computed<Column<User>[]>(() => [
    { key: 'name', label: 'Name', sortable: true },
    { key: 'email', label: 'Email', sortable: true },
    { key: 'roles', label: 'Roles' },
    { key: 'created_at', label: 'Created', sortable: true },
]);

const deleteDialog = ref(false);
const userToDelete = ref<User | null>(null);
const isDeleting = ref(false);

function openDeleteDialog(user: User): void {
    userToDelete.value = user;
    deleteDialog.value = true;
}

function confirmDelete(): void {
    if (!userToDelete.value) {
        return;
    }

    isDeleting.value = true;

    router.delete(`/admin/users/${userToDelete.value.id}`, {
        preserveScroll: true,
        onSuccess: () => {
            deleteDialog.value = false;
            userToDelete.value = null;
        },
        onFinish: () => {
            isDeleting.value = false;
        },
    });
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
    <Head title="Users" />

    <AdminLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 p-4 md:p-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold tracking-tight">Users</h1>
                    <p class="text-muted-foreground">Manage user accounts.</p>
                </div>
                <Button as-child>
                    <Link href="/admin/users/create">
                        <Plus class="mr-2 h-4 w-4" />
                        Add User
                    </Link>
                </Button>
            </div>

            <DataTable
                :columns="columns"
                :data="users.data"
                :filters="filters"
                route-name="/admin/users"
                search-placeholder="Search users..."
            >
                <template #cell-roles="{ item }">
                    <div class="flex flex-wrap gap-1">
                        <Badge
                            v-for="role in (item as User).roles"
                            :key="role.id"
                            variant="secondary"
                        >
                            {{ role.name }}
                        </Badge>
                        <span
                            v-if="(item as User).roles.length === 0"
                            class="text-sm text-muted-foreground"
                        >
                            No roles
                        </span>
                    </div>
                </template>

                <template #cell-created_at="{ value }">
                    {{ formatDate(value as string) }}
                </template>

                <template #actions="{ item }">
                    <div class="flex items-center justify-end gap-2">
                        <Button variant="ghost" size="icon" as-child>
                            <Link
                                :href="`/admin/users/${(item as User).id}/edit`"
                            >
                                <Pencil class="h-4 w-4" />
                            </Link>
                        </Button>
                        <Button
                            variant="ghost"
                            size="icon"
                            @click="openDeleteDialog(item as User)"
                        >
                            <Trash2 class="h-4 w-4 text-destructive" />
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

    <ConfirmDialog
        v-model:open="deleteDialog"
        title="Delete User"
        :description="`Are you sure you want to delete ${userToDelete?.name}? This action cannot be undone.`"
        confirm-label="Delete"
        :loading="isDeleting"
        @confirm="confirmDelete"
    />
</template>
