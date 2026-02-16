<script setup lang="ts">
import { Link, router, useForm, usePage } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import AppHead from '@/components/AppHead.vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { useRoleDisplayName } from '@/composables/useRoleDisplayName';
import { useTranslations } from '@/composables/useTranslations';
import AppLayout from '@/layouts/AppLayout.vue';
import { show as billingShow } from '@/routes/admin/billing';

const { t } = useTranslations();
const canManageUsers = computed(() => usePage().props.auth.can_manage_users);
const { roleDisplayName } = useRoleDisplayName();

type Role = {
    id: number;
    name: string;
};

type User = {
    id: string;
    name: string;
    email: string;
    roles: Role[];
    two_factor_enabled: boolean;
    created_at: string | null;
    updated_at: string | null;
};

type Props = {
    user: User;
    roles: Role[];
};

const props = defineProps<Props>();

const breadcrumbs = computed(() => [
    { title: t('admin.breadcrumb'), href: '/admin' },
    { title: t('admin.navigation.users'), href: '/admin/users' },
    { title: props.user.name },
]);

const form = useForm({
    name: props.user.name,
    email: props.user.email,
    password: '',
    password_confirmation: '',
    role: props.user.roles[0]?.name ?? 'user',
});

function submit(): void {
    form.put(`/admin/users/${props.user.id}`);
}

const ROLE_ORDER = ['user', 'moderator', 'admin', 'super-admin'];

const sortedRoles = computed(() => {
    return [...props.roles].sort(
        (a, b) =>
            (ROLE_ORDER.indexOf(a.name) === -1 ? ROLE_ORDER.length : ROLE_ORDER.indexOf(a.name)) -
            (ROLE_ORDER.indexOf(b.name) === -1 ? ROLE_ORDER.length : ROLE_ORDER.indexOf(b.name)),
    );
});


const disablingTwoFactor = ref(false);

function formatDate(dateString: string | null): string {
    if (!dateString) {
        return '—';
    }
    return new Date(dateString).toLocaleDateString('en-US', {
        month: 'short',
        day: 'numeric',
        year: 'numeric',
        hour: 'numeric',
        minute: '2-digit',
    });
}

function disableTwoFactor(): void {
    if (disablingTwoFactor.value) {
        return;
    }
    if (!confirm(t('admin.users.two_factor_confirm'))) {
        return;
    }
    disablingTwoFactor.value = true;
    router.delete(`/admin/users/${props.user.id}/two-factor`, {
        preserveScroll: true,
        onFinish: () => {
            disablingTwoFactor.value = false;
        },
    });
}
</script>

<template>
    <AppHead :title="`${t('admin.users.edit_title')} ${user.name}`" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 p-4 md:p-6">
            <div>
                <h1 class="text-2xl font-bold tracking-tight">
                    {{ t('admin.users.edit_title') }}
                </h1>
                <p class="text-muted-foreground">
                    {{ t('admin.users.edit_description') }}
                </p>
            </div>

            <form @submit.prevent="submit" class="space-y-6">
                <div class="grid gap-6 sm:grid-cols-[7fr_3fr]">
                    <Card>
                        <CardHeader>
                            <CardTitle>{{ t('admin.users.user_info') }}</CardTitle>
                            <CardDescription>
                                {{ t('admin.users.user_info_update') }}
                            </CardDescription>
                        </CardHeader>
                        <CardContent class="space-y-4">
                            <div class="space-y-2">
                                <Label for="name">{{ t('fields.name') }}</Label>
                                <Input
                                    id="name"
                                    v-model="form.name"
                                    type="text"
                                    required
                                    :disabled="!canManageUsers"
                                />
                                <InputError :message="form.errors.name" />
                            </div>

                            <div class="space-y-2">
                                <Label for="email">{{ t('fields.email') }}</Label>
                                <Input
                                    id="email"
                                    v-model="form.email"
                                    type="email"
                                    required
                                    :disabled="!canManageUsers"
                                />
                                <InputError :message="form.errors.email" />
                            </div>

                            <div class="space-y-2">
                                <Label>{{ t('admin.users.roles') }}</Label>
                                <div class="flex flex-col gap-3">
                                    <div
                                        v-for="role in sortedRoles"
                                        :key="role.id"
                                        class="flex items-center space-x-2"
                                    >
                                        <input
                                            :id="`role-${role.id}`"
                                            v-model="form.role"
                                            type="radio"
                                            name="role"
                                            :value="role.name"
                                            :disabled="!canManageUsers"
                                            class="h-4 w-4 border-input text-primary focus:ring-primary"
                                        />
                                        <Label
                                            :for="`role-${role.id}`"
                                            :class="{
                                                'cursor-pointer': canManageUsers,
                                                'opacity-50': !canManageUsers,
                                            }"
                                        >
                                            {{ roleDisplayName(role.name, role.display_name) }}
                                        </Label>
                                    </div>
                                </div>
                                <p
                                    v-if="sortedRoles.length === 0"
                                    class="text-sm text-muted-foreground"
                                >
                                    {{ t('admin.users.no_roles_available') }}
                                </p>
                            </div>
                            <InputError :message="form.errors.role" class="mt-2" />
                        </CardContent>
                    </Card>

                    <Card>
                        <CardHeader>
                            <CardTitle>{{ t('admin.users.details') }}</CardTitle>
                        </CardHeader>
                        <CardContent class="space-y-4">
                            <div class="space-y-2">
                                <Label>{{ t('admin.users.user_id') }}</Label>
                                <p
                                    class="font-mono text-sm text-muted-foreground break-all"
                                    :title="user.id"
                                >
                                    {{ user.id }}
                                </p>
                                <Link
                                    :href="billingShow.url(user.id)"
                                    class="text-sm text-primary underline-offset-4 hover:underline"
                                >
                                    {{ t('admin.users.view_subscription') }}
                                </Link>
                            </div>
                            <div class="space-y-2">
                                <Label>{{ t('admin.users.created') }}</Label>
                                <p class="text-sm text-muted-foreground">
                                    {{ formatDate(user.created_at) }}
                                </p>
                            </div>
                            <div class="space-y-2">
                                <Label>{{ t('admin.users.updated') }}</Label>
                                <p class="text-sm text-muted-foreground">
                                    {{ formatDate(user.updated_at) }}
                                </p>
                            </div>
                        </CardContent>
                    </Card>
                </div>

                <Card>
                    <CardHeader>
                        <CardTitle>{{ t('admin.users.security') }}</CardTitle>
                        <CardDescription>
                            {{ t('admin.users.security_description') }}
                        </CardDescription>
                    </CardHeader>
                    <CardContent class="space-y-4">
                        <div class="space-y-2">
                            <Label>{{ t('admin.users.two_factor') }}</Label>
                            <div class="flex items-center gap-3">
                                <span class="text-sm text-muted-foreground">
                                    {{
                                        user.two_factor_enabled
                                            ? t('common.enabled')
                                            : t('common.disabled')
                                    }}
                                </span>
                                <Button
                                    v-if="canManageUsers && user.two_factor_enabled"
                                    type="button"
                                    variant="outline"
                                    size="sm"
                                    :disabled="disablingTwoFactor"
                                    @click="disableTwoFactor"
                                >
                                    {{
                                        disablingTwoFactor
                                            ? t('admin.users.turning_off')
                                            : t('admin.users.turn_off_2fa')
                                    }}
                                </Button>
                            </div>
                        </div>

                        <div class="space-y-2">
                            <Label for="password">{{
                                t('fields.new_password')
                            }}</Label>
                            <Input
                                id="password"
                                v-model="form.password"
                                type="password"
                                :placeholder="t('admin.users.leave_blank')"
                                :disabled="!canManageUsers"
                            />
                            <InputError :message="form.errors.password" />
                        </div>

                        <div class="space-y-2">
                            <Label for="password_confirmation">
                                {{ t('admin.users.confirm_new_password') }}
                            </Label>
                            <Input
                                id="password_confirmation"
                                v-model="form.password_confirmation"
                                type="password"
                                :disabled="!canManageUsers"
                            />
                        </div>
                    </CardContent>
                </Card>

                <div class="flex items-center gap-4">
                    <Button
                        v-if="canManageUsers"
                        type="submit"
                        :disabled="form.processing"
                    >
                        {{
                            form.processing
                                ? t('admin.users.saving')
                                : t('admin.users.save_changes')
                        }}
                    </Button>
                    <Button variant="outline" as-child>
                        <Link href="/admin/users">{{
                            t('common.cancel')
                        }}</Link>
                    </Button>
                </div>
            </form>
        </div>
    </AppLayout>
</template>
