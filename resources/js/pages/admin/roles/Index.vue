<script setup lang="ts">
import { Link, router, usePage } from '@inertiajs/vue3';
import { Pencil, Plus, Trash2 } from 'lucide-vue-next';
import { computed, ref } from 'vue';
import ConfirmDialog from '@/components/app/admin/ConfirmDialog.vue';
import DataTable, { type Column } from '@/components/app/admin/DataTable.vue';
import Pagination from '@/components/app/admin/Pagination.vue';
import AppHead from '@/components/shared/AppHead.vue';
import { Badge } from '@/components/shared/ui/badge';
import { Button } from '@/components/shared/ui/button';
import { useRoleDisplayName } from '@/composables/useRoleDisplayName';
import { useFormatDate } from '@/composables/useFormatDate';
import { useTranslations } from '@/composables/useTranslations';
import AppLayout from '@/layouts/AppLayout.vue';

const { t } = useTranslations();
const { formatDate } = useFormatDate();
const canManageRoles = computed(() => usePage().props.auth.can_manage_roles);
const { roleDisplayName } = useRoleDisplayName();

type Role = {
    id: number;
    name: string;
    display_name?: string | null;
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

const breadcrumbs = computed(() => [
    { title: t('admin.breadcrumb'), href: '/admin' },
    { title: t('admin.navigation.roles') },
]);

const columns = computed<Column<Role>[]>(() => [
    { key: 'name', label: t('admin.table.name'), sortable: true },
    { key: 'permissions_count', label: t('admin.table.permissions') },
    { key: 'created_at', label: t('admin.table.created'), sortable: true },
]);

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

</script>

<template>
    <AppHead :title="t('admin.roles.title')" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 p-4 md:p-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold tracking-tight">
                        {{ t('admin.roles.title') }}
                    </h1>
                    <p class="text-muted-foreground">
                        {{ t('admin.roles.description') }}
                    </p>
                </div>
                <Button
                    v-if="canManageRoles"
                    as-child
                >
                    <Link href="/admin/roles/create">
                        <Plus class="mr-2 h-4 w-4" />
                        {{ t('admin.roles.add_role') }}
                    </Link>
                </Button>
            </div>

            <DataTable
                :columns="columns"
                :data="roles.data"
                :filters="filters"
                route-name="/admin/roles"
                :search-placeholder="t('admin.roles.search_placeholder')"
            >
                <template #cell-name="{ item }">
                    <span class="font-medium">{{
                        roleDisplayName((item as Role).name, (item as Role).display_name)
                    }}</span>
                </template>

                <template #cell-permissions_count="{ value }">
                    <Badge variant="secondary">
                        {{ value }}
                        {{
                            (value as number) === 1
                                ? t('admin.roles.permission_singular')
                                : t('admin.roles.permission_plural')
                        }}
                    </Badge>
                </template>

                <template #cell-created_at="{ value }">
                    {{ formatDate(value as string) }}
                </template>

                <template
                    v-if="canManageRoles"
                    #actions="{ item }"
                >
                    <div class="flex items-center justify-end gap-2">
                        <Button variant="ghost" size="icon" as-child>
                            <Link
                                :href="`/admin/roles/${(item as Role).id}/edit`"
                            >
                                <Pencil class="h-4 w-4" />
                            </Link>
                        </Button>
                        <Button
                            variant="ghost"
                            size="icon"
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
    </AppLayout>

    <ConfirmDialog
        v-model:open="deleteDialog"
        :title="t('admin.roles.delete_title')"
        :description="
            roleToDelete
                ? t('admin.roles.delete_confirm').replace(
                      '{name}',
                      roleDisplayName(roleToDelete.name, roleToDelete.display_name),
                  )
                : ''
        "
        :confirm-label="t('common.delete')"
        :loading="isDeleting"
        @confirm="confirmDelete"
    />
</template>
