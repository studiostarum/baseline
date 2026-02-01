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

type Role = {
    id: number;
    name: string;
};

type User = {
    id: number;
    name: string;
    email: string;
    roles: Role[];
};

type Props = {
    user: User;
    roles: Role[];
};

const props = defineProps<Props>();

const breadcrumbs = computed(() => [
    { title: 'Admin', href: '/admin' },
    { title: 'Users', href: '/admin/users' },
    { title: props.user.name },
]);

const form = useForm({
    name: props.user.name,
    email: props.user.email,
    password: '',
    password_confirmation: '',
    roles: props.user.roles.map((r) => r.name),
});

function submit(): void {
    form.put(`/admin/users/${props.user.id}`);
}

function toggleRole(roleName: string): void {
    const index = form.roles.indexOf(roleName);
    if (index === -1) {
        form.roles.push(roleName);
    } else {
        form.roles.splice(index, 1);
    }
}
</script>

<template>
    <Head :title="`Edit ${user.name}`" />

    <AdminLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 p-4 md:p-6">
            <div>
                <h1 class="text-2xl font-bold tracking-tight">Edit User</h1>
                <p class="text-muted-foreground">
                    Update user account details.
                </p>
            </div>

            <form @submit.prevent="submit" class="max-w-2xl space-y-6">
                <Card>
                    <CardHeader>
                        <CardTitle>User Information</CardTitle>
                        <CardDescription
                            >Update the user's details.</CardDescription
                        >
                    </CardHeader>
                    <CardContent class="space-y-4">
                        <div class="space-y-2">
                            <Label for="name">Name</Label>
                            <Input
                                id="name"
                                v-model="form.name"
                                type="text"
                                required
                            />
                            <InputError :message="form.errors.name" />
                        </div>

                        <div class="space-y-2">
                            <Label for="email">Email address</Label>
                            <Input
                                id="email"
                                v-model="form.email"
                                type="email"
                                required
                            />
                            <InputError :message="form.errors.email" />
                        </div>

                        <div class="space-y-2">
                            <Label for="password">New Password</Label>
                            <Input
                                id="password"
                                v-model="form.password"
                                type="password"
                                placeholder="Leave blank to keep current"
                            />
                            <InputError :message="form.errors.password" />
                        </div>

                        <div class="space-y-2">
                            <Label for="password_confirmation"
                                >Confirm New Password</Label
                            >
                            <Input
                                id="password_confirmation"
                                v-model="form.password_confirmation"
                                type="password"
                            />
                        </div>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader>
                        <CardTitle>Roles</CardTitle>
                        <CardDescription
                            >Manage role permissions.</CardDescription
                        >
                    </CardHeader>
                    <CardContent>
                        <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
                            <div
                                v-for="role in roles"
                                :key="role.id"
                                class="flex items-center space-x-2"
                            >
                                <Checkbox
                                    :id="`role-${role.id}`"
                                    :checked="form.roles.includes(role.name)"
                                    @update:checked="toggleRole(role.name)"
                                />
                                <Label
                                    :for="`role-${role.id}`"
                                    class="cursor-pointer"
                                >
                                    {{ role.name }}
                                </Label>
                            </div>
                            <p
                                v-if="roles.length === 0"
                                class="text-sm text-muted-foreground"
                            >
                                No permissions available.
                            </p>
                        </div>
                        <InputError :message="form.errors.roles" class="mt-2" />
                    </CardContent>
                </Card>

                <div class="flex items-center gap-4">
                    <Button type="submit" :disabled="form.processing">
                        {{ form.processing ? 'Saving...' : 'Save Changes' }}
                    </Button>
                    <Button variant="outline" as-child>
                        <Link href="/admin/users">Cancel</Link>
                    </Button>
                </div>
            </form>
        </div>
    </AdminLayout>
</template>
