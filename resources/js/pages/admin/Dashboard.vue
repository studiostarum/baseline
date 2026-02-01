<script setup lang="ts">
import StatCard from '@/components/admin/StatCard.vue';
import { Avatar, AvatarFallback } from '@/components/ui/avatar';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { useInitials } from '@/composables/useInitials';
import AdminLayout from '@/layouts/AdminLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { Key, Shield, Users } from 'lucide-vue-next';
import { computed } from 'vue';

type RecentUser = {
    id: number;
    name: string;
    email: string;
    created_at: string;
};

type Props = {
    stats: {
        users: number;
        roles: number;
        permissions: number;
    };
    recentUsers: RecentUser[];
};

defineProps<Props>();

const { getInitials } = useInitials();

const breadcrumbs = computed(() => [
    { title: 'Admin', href: '/admin' },
    { title: 'Dashboard' },
]);

function formatDate(dateString: string): string {
    return new Date(dateString).toLocaleDateString('en-US', {
        month: 'short',
        day: 'numeric',
        year: 'numeric',
    });
}
</script>

<template>
    <Head title="Admin Dashboard" />

    <AdminLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 p-4 md:p-6">
            <div>
                <h1 class="text-2xl font-bold tracking-tight">
                    Admin Dashboard
                </h1>
                <p class="text-muted-foreground">
                    Overview of your application.
                </p>
            </div>

            <div class="grid gap-4 md:grid-cols-3">
                <StatCard
                    title="Total Users"
                    :value="stats.users"
                    description="Registered users"
                    :icon="Users"
                />
                <StatCard
                    title="Roles"
                    :value="stats.roles"
                    description="User roles defined"
                    :icon="Shield"
                />
                <StatCard
                    title="Permissions"
                    :value="stats.permissions"
                    description="Available permissions"
                    :icon="Key"
                />
            </div>

            <div class="grid gap-4 md:grid-cols-2">
                <Card>
                    <CardHeader>
                        <CardTitle>Recent Users</CardTitle>
                        <CardDescription
                            >Latest registered users</CardDescription
                        >
                    </CardHeader>
                    <CardContent>
                        <div class="space-y-4">
                            <div
                                v-for="user in recentUsers"
                                :key="user.id"
                                class="flex items-center gap-4"
                            >
                                <Avatar class="h-9 w-9">
                                    <AvatarFallback>{{
                                        getInitials(user.name)
                                    }}</AvatarFallback>
                                </Avatar>
                                <div class="flex-1 space-y-1">
                                    <p class="text-sm leading-none font-medium">
                                        {{ user.name }}
                                    </p>
                                    <p class="text-sm text-muted-foreground">
                                        {{ user.email }}
                                    </p>
                                </div>
                                <div class="text-sm text-muted-foreground">
                                    {{ formatDate(user.created_at) }}
                                </div>
                            </div>
                            <div
                                v-if="recentUsers.length === 0"
                                class="text-center text-muted-foreground"
                            >
                                No users yet.
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader>
                        <CardTitle>Quick Actions</CardTitle>
                        <CardDescription>Common admin tasks</CardDescription>
                    </CardHeader>
                    <CardContent>
                        <div class="grid gap-2">
                            <Link
                                href="/admin/users/create"
                                class="flex items-center gap-2 rounded-lg border p-3 transition-colors hover:bg-muted/50"
                            >
                                <Users class="h-4 w-4" />
                                <span>Create new user</span>
                            </Link>
                            <Link
                                href="/admin/roles/create"
                                class="flex items-center gap-2 rounded-lg border p-3 transition-colors hover:bg-muted/50"
                            >
                                <Shield class="h-4 w-4" />
                                <span>Create new role</span>
                            </Link>
                            <Link
                                href="/admin/settings"
                                class="flex items-center gap-2 rounded-lg border p-3 transition-colors hover:bg-muted/50"
                            >
                                <Key class="h-4 w-4" />
                                <span>Manage settings</span>
                            </Link>
                        </div>
                    </CardContent>
                </Card>
            </div>
        </div>
    </AdminLayout>
</template>
