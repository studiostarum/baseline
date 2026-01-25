<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { Pencil, Plus, Trash2 } from 'lucide-vue-next';
import { ref } from 'vue';
import ConfirmDialog from '@/components/admin/ConfirmDialog.vue';
import DataTable, { type Column } from '@/components/admin/DataTable.vue';
import Pagination from '@/components/admin/Pagination.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import AdminLayout from '@/layouts/AdminLayout.vue';

type Role = {
    id: number;
    name: string;
    permissions_count: number;
    created_at: string;
};

type PaginatedRoles = {
    data: Role[];
    links: Array<{ url: string | null; label: string; active: boolean }>;
    from: number;
    to: number;
    total: number;
};

type Props = {
    roles: PaginatedRoles;
    filters: Record<string, string>;
};

defineProps<Props>();

const breadcrumbs = [
    { title: 'Admin', href: '/admin' },
    { title: 'Roles' },
];

const columns: Column<Role>[] = [
    { key: 'name', label: 'Name', sortable: true },
    { key: 'permissions_count', label: 'Permissions' },
    { key: 'created_at', label: 'Created', sortable: true },
];

const deleteDialog = ref(false);
const roleToDelete = ref<Role | null>(null);
const isDeleting = ref(false);

function openDeleteDialog(role: Role): void {
    roleToDelete.value = role;
    deleteDialog.value = true;
}

function confirmDelete(): void {
    if (!roleToDelete.value) {
        return;
    }

    isDeleting.value = true;

    router.delete(`/admin/roles/${roleToDelete.value.id}`, {
        preserveScroll: true,
        onSuccess: () => {
            deleteDialog.value = false;
            roleToDelete.value = null;
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
    <Head title="Roles" />

    <AdminLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 p-4 md:p-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold tracking-tight">Roles</h1>
                    <p class="text-muted-foreground">Manage user roles and permissions.</p>
                </div>
                <Button as-child>
                    <Link href="/admin/roles/create">
                        <Plus class="mr-2 h-4 w-4" />
                        Add Role
                    </Link>
                </Button>
            </div>

            <DataTable
                :columns="columns"
                :data="roles.data"
                :filters="filters"
                route-name="/admin/roles"
                search-placeholder="Search roles..."
            >
                <template #cell-name="{ item }">
                    <div class="flex items-center gap-2">
                        <span class="font-medium">{{ (item as Role).name }}</span>
                        <Badge v-if="(item as Role).name === 'super-admin'" variant="default">
                            System
                        </Badge>
                    </div>
                </template>

                <template #cell-permissions_count="{ value }">
                    <Badge variant="secondary">
                        {{ value }} permission{{ (value as number) !== 1 ? 's' : '' }}
                    </Badge>
                </template>

                <template #cell-created_at="{ value }">
                    {{ formatDate(value as string) }}
                </template>

                <template #actions="{ item }">
                    <div class="flex items-center justify-end gap-2">
                        <Button variant="ghost" size="icon" as-child>
                            <Link :href="`/admin/roles/${(item as Role).id}/edit`">
                                <Pencil class="h-4 w-4" />
                            </Link>
                        </Button>
                        <Button
                            variant="ghost"
                            size="icon"
                            :disabled="(item as Role).name === 'super-admin'"
                            @click="openDeleteDialog(item as Role)"
                        >
                            <Trash2 class="h-4 w-4 text-destructive" />
                        </Button>
                    </div>
                </template>
            </DataTable>

            <Pagination
                v-if="roles.data.length > 0"
                :links="roles.links"
                :from="roles.from"
                :to="roles.to"
                :total="roles.total"
            />
        </div>
    </AdminLayout>

    <ConfirmDialog
        v-model:open="deleteDialog"
        title="Delete Role"
        :description="`Are you sure you want to delete the '${roleToDelete?.name}' role? This action cannot be undone.`"
        confirm-label="Delete"
        :loading="isDeleting"
        @confirm="confirmDelete"
    />
</template>
