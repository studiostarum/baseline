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
import AdminLayout from '@/layouts/AdminLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { CheckCheck, X } from 'lucide-vue-next';
import { computed, ref, watch } from 'vue';

const { t } = useTranslations();
const { roleDisplayName } = useRoleDisplayName();

function slugify(value: string): string {
    return value
        .trim()
        .toLowerCase()
        .replace(/[\s_]+/g, '-')
        .replace(/-+/g, '-')
        .replace(/^-|-$/g, '');
}

type Permission = {
    id: number;
    name: string;
};

type Props = {
    permissions: Permission[];
};

defineProps<Props>();

const breadcrumbs = computed(() => [
    { title: t('admin.breadcrumb'), href: '/admin' },
    { title: t('admin.navigation.roles'), href: '/admin/roles' },
    { title: t('common.create') },
]);

const form = useForm({
    name: '',
    permissions: [] as string[],
});

const selectedPermissionNames = ref<string[]>([]);

watch(selectedPermissionNames, (names) => {
    form.permissions.length = 0;
    form.permissions.push(...names);
}, { immediate: true, deep: true });

const displayName = ref(roleDisplayName(form.name) || '');
watch(displayName, (val) => {
    form.name = slugify(val);
});

function submit(): void {
    form.permissions.length = 0;
    form.permissions.push(...selectedPermissionNames.value);
    form.post('/admin/roles');
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
</script>

<template>
    <Head :title="t('admin.roles.create_title')" />

    <AdminLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 p-4 md:p-6">
            <div>
                <h1 class="text-2xl font-bold tracking-tight">
                    {{ t('admin.roles.create_title') }}
                </h1>
                <p class="text-muted-foreground">
                    {{ t('admin.roles.create_description') }}
                </p>
            </div>

            <form @submit.prevent="submit" class="space-y-6">
                <div class="grid gap-6 sm:grid-cols-[7fr_3fr]">
                    <Card>
                        <CardHeader>
                            <CardTitle>{{ t('admin.roles.role_info') }}</CardTitle>
                            <CardDescription>
                                {{ t('admin.roles.role_info_description') }}
                            </CardDescription>
                        </CardHeader>
                        <CardContent class="space-y-4">
                            <div class="space-y-2">
                                <Label for="name">{{ t('admin.roles.role_name') }}</Label>
                                <Input
                                    id="name"
                                    v-model="displayName"
                                    type="text"
                                    :placeholder="t('admin.roles.role_name_placeholder')"
                                    required
                                />
                                <InputError :message="form.errors.name" />
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
                                    {{ t('admin.roles.permissions_description') }}
                                </CardDescription>
                            </div>
                            <div class="flex gap-2">
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
                                    :model-value="selectedPermissionNames.includes(permission.name)"
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
                                    class="cursor-pointer"
                                >
                                    {{ permissionDisplayName(permission.name) }}
                                </Label>
                            </div>
                            <p
                                v-if="permissions.length === 0"
                                class="text-sm text-muted-foreground"
                            >
                                {{ t('admin.roles.no_permissions') }}.
                                {{ t('admin.roles.no_permissions_hint') }}
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
                    <Button type="submit" :disabled="form.processing">
                        {{
                            form.processing
                                ? t('admin.roles.creating')
                                : t('admin.roles.create_title')
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
    </AdminLayout>
</template>
