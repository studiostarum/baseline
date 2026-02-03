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
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { useRoleDisplayName } from '@/composables/useRoleDisplayName';
import { useTranslations } from '@/composables/useTranslations';
import AdminLayout from '@/layouts/AdminLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { computed, ref, watch } from 'vue';

const { t } = useTranslations();
const { roleDisplayName } = useRoleDisplayName();

type Role = {
    id: number;
    name: string;
};

type Props = {
    roles: Role[];
};

const props = defineProps<Props>();

const breadcrumbs = computed(() => [
    { title: t('admin.breadcrumb'), href: '/admin' },
    { title: t('admin.navigation.users'), href: '/admin/users' },
    { title: t('admin.users.create_breadcrumb') },
]);

const form = useForm({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
    roles: ['user'] as string[],
});

function submit(): void {
    form.post('/admin/users');
}

const ROLE_ORDER = ['user', 'moderator', 'admin', 'super-admin'];

const sortedRoles = computed(() => {
    return [...props.roles].sort(
        (a, b) =>
            (ROLE_ORDER.indexOf(a.name) === -1 ? ROLE_ORDER.length : ROLE_ORDER.indexOf(a.name)) -
            (ROLE_ORDER.indexOf(b.name) === -1 ? ROLE_ORDER.length : ROLE_ORDER.indexOf(b.name)),
    );
});

const selectedRole = ref(form.roles[0] ?? 'user');

watch(
    () => form.roles,
    (roles) => {
        const next = roles[0] ?? 'user';
        if (selectedRole.value !== next) {
            selectedRole.value = next;
        }
    },
    { deep: true },
);

watch(selectedRole, (value) => {
    const next = value ? [value] : ['user'];
    if (JSON.stringify(form.roles) !== JSON.stringify(next)) {
        form.roles = next;
    }
});
</script>

<template>
    <Head :title="t('admin.users.create_title')" />

    <AdminLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 p-4 md:p-6">
            <div>
                <h1 class="text-2xl font-bold tracking-tight">
                    {{ t('admin.users.create_title') }}
                </h1>
                <p class="text-muted-foreground">
                    {{ t('admin.users.create_description') }}
                </p>
            </div>

            <form @submit.prevent="submit" class="max-w-2xl space-y-6">
                <Card>
                    <CardHeader>
                        <CardTitle>{{ t('admin.users.user_info') }}</CardTitle>
                        <CardDescription>
                            {{ t('admin.users.user_info_description') }}
                        </CardDescription>
                    </CardHeader>
                    <CardContent class="space-y-4">
                        <div class="space-y-2">
                            <Label for="name">{{ t('fields.name') }}</Label>
                            <Input
                                id="name"
                                v-model="form.name"
                                type="text"
                                :placeholder="t('fields.full_name')"
                                required
                            />
                            <InputError :message="form.errors.name" />
                        </div>

                        <div class="space-y-2">
                            <Label for="email">{{ t('fields.email') }}</Label>
                            <Input
                                id="email"
                                v-model="form.email"
                                type="email"
                                :placeholder="t('fields.email_placeholder')"
                                required
                            />
                            <InputError :message="form.errors.email" />
                        </div>

                        <div class="space-y-2">
                            <Label for="password">{{ t('fields.password') }}</Label>
                            <Input
                                id="password"
                                v-model="form.password"
                                type="password"
                                required
                            />
                            <InputError :message="form.errors.password" />
                        </div>

                        <div class="space-y-2">
                            <Label for="password_confirmation">
                                {{ t('fields.confirm_password') }}
                            </Label>
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
                        <CardTitle>{{ t('admin.table.roles') }}</CardTitle>
                        <CardDescription>
                            {{ t('admin.users.roles_description') }}
                        </CardDescription>
                    </CardHeader>
                    <CardContent>
                        <div class="space-y-2">
                            <Label for="roles">{{ t('admin.users.role') }}</Label>
                            <Select
                                v-model="selectedRole"
                                :disabled="sortedRoles.length === 0"
                                @click.prevent
                            >
                                <SelectTrigger id="roles" class="w-full">
                                    <SelectValue
                                        :placeholder="t('admin.users.select_role')"
                                    />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem
                                        v-for="role in sortedRoles"
                                        :key="role.id"
                                        :value="role.name"
                                    >
                                        {{ roleDisplayName(role.name) }}
                                    </SelectItem>
                                </SelectContent>
                            </Select>
                            <p
                                v-if="sortedRoles.length === 0"
                                class="text-sm text-muted-foreground"
                            >
                                {{ t('admin.users.no_roles_available_create') }}
                            </p>
                        </div>
                        <InputError :message="form.errors.roles" class="mt-2" />
                    </CardContent>
                </Card>

                <div class="flex items-center gap-4">
                    <Button type="submit" :disabled="form.processing">
                        {{
                            form.processing
                                ? t('admin.users.creating')
                                : t('admin.users.create_user_button')
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
    </AdminLayout>
</template>
