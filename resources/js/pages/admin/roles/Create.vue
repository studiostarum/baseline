<script setup lang="ts">
import { Link, useForm } from '@inertiajs/vue3';
import { CheckCheck, X } from 'lucide-vue-next';
import { computed, ref, watch } from 'vue';
import AppHead from '@/components/shared/AppHead.vue';
import InputError from '@/components/shared/InputError.vue';
import { Button } from '@/components/shared/ui/button';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/shared/ui/card';
import { Checkbox } from '@/components/shared/ui/checkbox';
import { Input } from '@/components/shared/ui/input';
import { Label } from '@/components/shared/ui/label';
import { useRoleDisplayName } from '@/composables/useRoleDisplayName';
import { useTranslations } from '@/composables/useTranslations';
import AppLayout from '@/layouts/AppLayout.vue';

const { t } = useTranslations();
void useRoleDisplayName();

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
    display_name: '',
    permissions: [] as string[],
});

const selectedPermissionNames = ref<string[]>([]);

watch(selectedPermissionNames, (names) => {
    form.permissions.length = 0;
    form.permissions.push(...names);
}, { immediate: true, deep: true });

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
    <AppHead :title="t('admin.roles.create_title')" />

    <AppLayout :breadcrumbs="breadcrumbs">
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
                                <Label for="name">{{ t('admin.roles.role_key') }}</Label>
                                <Input
                                    id="name"
                                    v-model="form.name"
                                    type="text"
                                    :placeholder="t('admin.roles.role_key_placeholder')"
                                    class="font-mono"
                                    required
                                />
                                <p class="text-sm text-muted-foreground">
                                    {{ t('admin.roles.role_key_description') }}
                                </p>
                                <InputError :message="form.errors.name" />
                            </div>
                            <div class="space-y-2">
                                <Label for="display_name">{{ t('admin.roles.role_name') }}</Label>
                                <Input
                                    id="display_name"
                                    v-model="form.display_name"
                                    type="text"
                                    :placeholder="t('admin.roles.role_name_placeholder')"
                                />
                                <InputError :message="form.errors.display_name" />
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
    </AppLayout>
</template>
