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
import AdminLayout from '@/layouts/AdminLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { computed } from 'vue';

type Permission = {
    id: number;
    name: string;
};

type Role = {
    id: number;
    name: string;
    permissions: Permission[];
};

type Props = {
    role: Role;
    permissions: Permission[];
};

const props = defineProps<Props>();

const breadcrumbs = computed(() => [
    { title: 'Admin', href: '/admin' },
    { title: 'Roles', href: '/admin/roles' },
    { title: props.role.name },
]);

const form = useForm({
    name: props.role.name,
    permissions: props.role.permissions.map((p) => p.name),
});

const isSuperAdmin = props.role.name === 'super-admin';

function submit(): void {
    form.put(`/admin/roles/${props.role.id}`);
}

function togglePermission(permissionName: string): void {
    const index = form.permissions.indexOf(permissionName);
    if (index === -1) {
        form.permissions.push(permissionName);
    } else {
        form.permissions.splice(index, 1);
    }
}

function selectAll(permissions: Permission[]): void {
    form.permissions = permissions.map((p) => p.name);
}

function deselectAll(): void {
    form.permissions = [];
}
</script>

<template>
    <Head :title="`Edit ${role.name}`" />

    <AdminLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 p-4 md:p-6">
            <div>
                <h1 class="text-2xl font-bold tracking-tight">Edit Role</h1>
                <p class="text-muted-foreground">
                    Update role details and permissions.
                </p>
            </div>

            <form @submit.prevent="submit" class="max-w-2xl space-y-6">
                <Card>
                    <CardHeader>
                        <CardTitle>Role Information</CardTitle>
                        <CardDescription
                            >Update the role details.</CardDescription
                        >
                    </CardHeader>
                    <CardContent class="space-y-4">
                        <div class="space-y-2">
                            <Label for="name">Role Name</Label>
                            <Input
                                id="name"
                                v-model="form.name"
                                type="text"
                                :disabled="isSuperAdmin"
                                required
                            />
                            <p
                                v-if="isSuperAdmin"
                                class="text-sm text-muted-foreground"
                            >
                                The super-admin role name cannot be changed.
                            </p>
                            <InputError :message="form.errors.name" />
                        </div>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader>
                        <div class="flex items-center justify-between">
                            <div>
                                <CardTitle>Permissions</CardTitle>
                                <CardDescription>
                                    {{
                                        isSuperAdmin
                                            ? 'Super admin has all permissions by default.'
                                            : 'Manage role permissions.'
                                    }}
                                </CardDescription>
                            </div>
                            <div v-if="!isSuperAdmin" class="flex gap-2">
                                <Button
                                    type="button"
                                    variant="outline"
                                    size="sm"
                                    @click="selectAll(permissions)"
                                >
                                    Select All
                                </Button>
                                <Button
                                    type="button"
                                    variant="outline"
                                    size="sm"
                                    @click="deselectAll"
                                >
                                    Deselect All
                                </Button>
                            </div>
                        </div>
                    </CardHeader>
                    <CardContent>
                        <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
                            <div
                                v-for="permission in permissions"
                                :key="permission.id"
                                class="flex items-center space-x-2"
                            >
                                <Checkbox
                                    :id="`permission-${permission.id}`"
                                    :checked="
                                        isSuperAdmin ||
                                        form.permissions.includes(
                                            permission.name,
                                        )
                                    "
                                    :disabled="isSuperAdmin"
                                    @update:checked="
                                        togglePermission(permission.name)
                                    "
                                />
                                <Label
                                    :for="`permission-${permission.id}`"
                                    :class="{
                                        'cursor-pointer': !isSuperAdmin,
                                        'opacity-50': isSuperAdmin,
                                    }"
                                >
                                    {{ permission.name }}
                                </Label>
                            </div>
                            <p
                                v-if="permissions.length === 0"
                                class="col-span-full text-sm text-muted-foreground"
                            >
                                No permissions available.
                            </p>
                        </div>
                        <InputError
                            :message="form.errors.permissions"
                            class="mt-2"
                        />
                    </CardContent>
                </Card>

                <div class="flex items-center gap-4">
                    <Button type="submit" :disabled="form.processing">
                        {{ form.processing ? 'Saving...' : 'Save Changes' }}
                    </Button>
                    <Button variant="outline" as-child>
                        <Link href="/admin/roles">Cancel</Link>
                    </Button>
                </div>
            </form>
        </div>
    </AdminLayout>
</template>
