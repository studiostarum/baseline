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

type Props = {
    roles: Role[];
};

defineProps<Props>();

const breadcrumbs = computed(() => [
    { title: 'Admin', href: '/admin' },
    { title: 'Users', href: '/admin/users' },
    { title: 'Create' },
]);

const form = useForm({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
    roles: [] as string[],
});

function submit(): void {
    form.post('/admin/users');
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
    <Head title="Create User" />

    <AdminLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 p-4 md:p-6">
            <div>
                <h1 class="text-2xl font-bold tracking-tight">Create User</h1>
                <p class="text-muted-foreground">Add a new user account.</p>
            </div>

            <form @submit.prevent="submit" class="max-w-2xl space-y-6">
                <Card>
                    <CardHeader>
                        <CardTitle>User Information</CardTitle>
                        <CardDescription
                            >Enter the user's details.</CardDescription
                        >
                    </CardHeader>
                    <CardContent class="space-y-4">
                        <div class="space-y-2">
                            <Label for="name">Name</Label>
                            <Input
                                id="name"
                                v-model="form.name"
                                type="text"
                                placeholder="John Doe"
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
                                placeholder="email@example.com"
                                required
                            />
                            <InputError :message="form.errors.email" />
                        </div>

                        <div class="space-y-2">
                            <Label for="password">Password</Label>
                            <Input
                                id="password"
                                v-model="form.password"
                                type="password"
                                required
                            />
                            <InputError :message="form.errors.password" />
                        </div>

                        <div class="space-y-2">
                            <Label for="password_confirmation"
                                >Confirm password</Label
                            >
                            <Input
                                id="password_confirmation"
                                v-model="form.password_confirmation"
                                type="password"
                                required
                            />
                        </div>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader>
                        <CardTitle>Roles</CardTitle>
                        <CardDescription
                            >Select permissions for this role.</CardDescription
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
                                No roles available. Create some roles first.
                            </p>
                        </div>
                        <InputError :message="form.errors.roles" class="mt-2" />
                    </CardContent>
                </Card>

                <div class="flex items-center gap-4">
                    <Button type="submit" :disabled="form.processing">
                        {{ form.processing ? 'Creating...' : 'Create User' }}
                    </Button>
                    <Button variant="outline" as-child>
                        <Link href="/admin/users">Cancel</Link>
                    </Button>
                </div>
            </form>
        </div>
    </AdminLayout>
</template>
