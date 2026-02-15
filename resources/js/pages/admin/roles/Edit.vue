<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { useRoleDisplayName } from '@/composables/useRoleDisplayName';
import { useTranslations } from '@/composables/useTranslations';
import AppLayout from '@/layouts/AppLayout.vue';
import AppHead from '@/components/AppHead.vue';
import { Link, useForm, usePage } from '@inertiajs/vue3';
import { CheckCheck, X } from 'lucide-vue-next';
import { computed, nextTick, ref, watch } from 'vue';

const { t } = useTranslations();
const canManageRoles = computed(() => usePage().props.auth.can_manage_roles);
const { roleDisplayName } = useRoleDisplayName();

function permissionLabel(name: string): string {
    return name
        .split(/[-_]/)
        .map((word) => word.charAt(0).toUpperCase() + word.slice(1).toLowerCase())
        .join(' ');
}

function permissionDisplayName(name: string): string {
    const key = `admin.permissions.${name.replace(/-/g, '_')}`;
    const translated = t(key);
    if (translated === key || translated.includes('admin.permissions.')) {
        return permissionLabel(name);
    }
    return translated;
}

type Permission = {
    id: number;
    name: string;
};

type Role = {
    id: number;
    name: string;
    display_name?: string | null;
    permissions: Permission[];
};

type Props = {
    role: Role;
    permissions: Permission[];
    is_system_role?: boolean;
};

const props = withDefaults(defineProps<Props>(), { is_system_role: false });

function getRolePermissionNames(role: Role): string[] {
    const perms = role.permissions;
    if (!perms) {
        return [];
    }
    const list = Array.isArray(perms) ? perms : Object.values(perms);
    return list.map((p: { name?: string }) => p.name ?? '').filter(Boolean);
}

const breadcrumbs = computed(() => [
    { title: t('admin.breadcrumb'), href: '/admin' },
    { title: t('admin.navigation.roles'), href: '/admin/roles' },
    { title: roleDisplayName(props.role.name, props.role.display_name) },
]);

const form = useForm({
    name: props.role.name,
    display_name: props.role.display_name ?? roleDisplayName(props.role.name),
    permissions: [] as string[],
});

const selectedPermissionNames = ref<string[]>(getRolePermissionNames(props.role));

function syncSelectedToRole(): void {
    const names = getRolePermissionNames(props.role);
    if (names.length > 0 && JSON.stringify(selectedPermissionNames.value) !== JSON.stringify(names)) {
        selectedPermissionNames.value = [...names];
    }
}

watch(
    () => props.role.permissions,
    syncSelectedToRole,
    { immediate: true, deep: true },
);

nextTick(syncSelectedToRole);

watch(selectedPermissionNames, (names) => {
    form.permissions.length = 0;
    form.permissions.push(...names);
}, { immediate: true, deep: true });

const isSuperAdmin = props.role.name === 'super-admin';
const isPermissionChecked = (name: string): boolean =>
    isSuperAdmin || selectedPermissionNames.value.includes(name);

const isPermissionsDisabled = computed(() => true);

function submit(): void {
    form.permissions.length = 0;
    form.permissions.push(...selectedPermissionNames.value);
    form.transform((data) => ({ ...data, name: props.role.name })).put(`/admin/roles/${props.role.id}`);
}

function setPermissionChecked(permissionName: string, checked: boolean): void {
    if (checked) {
        if (!selectedPermissionNames.value.includes(permissionName)) {
            selectedPermissionNames.value = [...selectedPermissionNames.value, permissionName];
        }
    } else {
        selectedPermissionNames.value = selectedPermissionNames.value.filter((n) => n !== permissionName);
    }
}

function selectAll(permissions: Permission[]): void {
    selectedPermissionNames.value = permissions.map((p) => p.name);
}

function deselectAll(): void {
    selectedPermissionNames.value = [];
}
</script>

<template>
    <AppHead :title="`${t('admin.roles.edit_title')} ${roleDisplayName(role.name, role.display_name)}`" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 p-4 md:p-6">
            <div>
                <h1 class="text-2xl font-bold tracking-tight">
                    {{ t('admin.roles.edit_title') }}
                </h1>
                <p class="text-muted-foreground">
                    {{ t('admin.roles.edit_description') }}
                </p>
            </div>

            <form @submit.prevent="submit" class="space-y-6">
                <div class="grid gap-6 sm:grid-cols-[7fr_3fr]">
                    <Card>
                        <CardHeader>
                            <CardTitle>{{ t('admin.roles.role_info') }}</CardTitle>
                            <CardDescription>
                                {{ t('admin.roles.role_info_update') }}
                            </CardDescription>
                        </CardHeader>
                        <CardContent class="space-y-4">
                            <div class="space-y-2">
                                <Label for="display_name">{{
                                    t('admin.roles.role_name')
                                }}</Label>
                                <Input
                                    id="display_name"
                                    v-model="form.display_name"
                                    type="text"
                                    :disabled="!canManageRoles"
                                    required
                                />
                                <p
                                    v-if="is_system_role"
                                    class="text-sm text-muted-foreground"
                                >
                                    {{ t('admin.roles.system_role_key_readonly') }}
                                </p>
                                <InputError :message="form.errors.display_name" />
                            </div>
                            <div class="space-y-2">
                                <Label for="role-key">{{
                                    t('admin.roles.role_key')
                                }}</Label>
                                <Input
                                    id="role-key"
                                    :model-value="form.name"
                                    type="text"
                                    disabled
                                    class="bg-muted font-mono text-muted-foreground"
                                />
                                <p class="text-sm text-muted-foreground">
                                    {{
                                        t('admin.roles.role_key_description')
                                    }}
                                </p>
                            </div>
                        </CardContent>
                    </Card>

                    <Card>
                        <CardHeader>
                            <div class="flex items-center justify-between">
                                <div>
                                    <CardTitle>{{ t('admin.roles.permissions_title') }}</CardTitle>
                                <CardDescription>
                                    {{
                                        isSuperAdmin
                                            ? t('admin.roles.super_admin_all')
                                            : t('admin.roles.permissions_manage')
                                    }}
                                </CardDescription>
                            </div>
                            <div
                                v-if="canManageRoles && !isPermissionsDisabled"
                                class="flex gap-2"
                            >
                                <Button
                                    type="button"
                                    variant="outline"
                                    size="icon"
                                    :title="t('admin.roles.select_all')"
                                    @click="selectAll(permissions)"
                                >
                                    <CheckCheck class="h-4 w-4" />
                                </Button>
                                <Button
                                    type="button"
                                    variant="outline"
                                    size="icon"
                                    :title="t('admin.roles.deselect_all')"
                                    @click="deselectAll"
                                >
                                    <X class="h-4 w-4" />
                                </Button>
                            </div>
                        </div>
                    </CardHeader>
                    <CardContent>
                        <div class="flex flex-col gap-3">
                            <div
                                v-for="permission in permissions"
                                :key="permission.id"
                                class="flex items-center space-x-2"
                            >
                                <Checkbox
                                    :id="`permission-${permission.id}`"
                                    :model-value="isPermissionChecked(permission.name)"
                                    :disabled="isPermissionsDisabled"
                                    @update:model-value="
                                        (val: boolean) =>
                                            setPermissionChecked(
                                                permission.name,
                                                val,
                                            )
                                    "
                                />
                                <Label
                                    :for="`permission-${permission.id}`"
                                    :class="{
                                        'cursor-pointer': !isPermissionsDisabled,
                                        'opacity-50': isPermissionsDisabled,
                                    }"
                                >
                                    {{ permissionDisplayName(permission.name) }}
                                </Label>
                            </div>
                            <p
                                v-if="permissions.length === 0"
                                class="text-sm text-muted-foreground"
                            >
                                {{ t('admin.roles.no_permissions') }}
                            </p>
                        </div>
                        <InputError
                            :message="form.errors.permissions"
                            class="mt-2"
                        />
                    </CardContent>
                    </Card>
                </div>

                <div class="flex items-center gap-4">
                    <Button
                        v-if="canManageRoles"
                        type="submit"
                        :disabled="form.processing"
                    >
                        {{
                            form.processing
                                ? t('admin.roles.saving')
                                : t('admin.users.save_changes')
                        }}
                    </Button>
                    <Button variant="outline" as-child>
                        <Link href="/admin/roles">{{
                            t('common.cancel')
                        }}</Link>
                    </Button>
                </div>
            </form>
        </div>
    </AppLayout>
</template>
