<script setup lang="ts">
import { Link, router, usePage } from '@inertiajs/vue3';
import { Pencil, Plus, Trash2 } from 'lucide-vue-next';
import { computed, ref } from 'vue';
import ConfirmDialog from '@/components/admin/ConfirmDialog.vue';
import DataTable, { type Column } from '@/components/admin/DataTable.vue';
import Pagination from '@/components/admin/Pagination.vue';
import AppHead from '@/components/AppHead.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { useFormatDate } from '@/composables/useFormatDate';
import { useRoleDisplayName } from '@/composables/useRoleDisplayName';
import { useTranslations } from '@/composables/useTranslations';
import AppLayout from '@/layouts/AppLayout.vue';

const { t } = useTranslations();
const { formatDate } = useFormatDate();
const canManageUsers = computed(() => usePage().props.auth.can_manage_users);
const { roleDisplayName } = useRoleDisplayName();

type Role = {
    id: number;
    name: string;
    display_name?: string | null;
};

type User = {
    id: string;
    name: string;
    email: string;
    created_at: string;
    updated_at: string;
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
    { title: t('admin.breadcrumb'), href: '/admin' },
    { title: t('admin.navigation.users') },
]);

const columns = computed<Column<User>[]>(() => [
    { key: 'name', label: t('admin.table.name'), sortable: true },
    { key: 'email', label: t('admin.table.email'), sortable: true },
    { key: 'roles', label: t('admin.table.roles') },
    { key: 'created_at', label: t('admin.table.created'), sortable: true },
    { key: 'updated_at', label: t('admin.table.updated'), sortable: true },
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

</script>

<template>
    <AppHead :title="t('admin.users.title')" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 p-4 md:p-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold tracking-tight">
                        {{ t('admin.users.title') }}
                    </h1>
                    <p class="text-muted-foreground">
                        {{ t('admin.users.description') }}
                    </p>
                </div>
                <Button
                    v-if="canManageUsers"
                    as-child
                >
                    <Link href="/admin/users/create">
                        <Plus class="mr-2 h-4 w-4" />
                        {{ t('admin.users.add_user') }}
                    </Link>
                </Button>
            </div>

            <DataTable
                :columns="columns"
                :data="users.data"
                :filters="filters"
                route-name="/admin/users"
                :search-placeholder="t('admin.users.search_placeholder')"
            >
                <template #cell-roles="{ item }">
                    <div class="flex flex-wrap gap-1">
                        <Badge
                            v-for="role in (item as User).roles"
                            :key="role.id"
                            variant="secondary"
                        >
                            {{ roleDisplayName(role.name, role.display_name) }}
                        </Badge>
                        <span
                            v-if="(item as User).roles.length === 0"
                            class="text-sm text-muted-foreground"
                        >
                            {{ t('admin.users.no_roles') }}
                        </span>
                    </div>
                </template>

                <template #cell-created_at="{ value }">
                    {{ formatDate(value as string) }}
                </template>

                <template #cell-updated_at="{ value }">
                    {{ formatDate(value as string) }}
                </template>

                <template
                    v-if="canManageUsers"
                    #actions="{ item }"
                >
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
    </AppLayout>

    <ConfirmDialog
        v-model:open="deleteDialog"
        :title="t('admin.users.delete_title')"
        :description="
            userToDelete
                ? t('admin.users.delete_confirm').replace(
                      '{name}',
                      userToDelete.name,
                  )
                : ''
        "
        :confirm-label="t('common.delete')"
        :loading="isDeleting"
        @confirm="confirmDelete"
    />
</template>
