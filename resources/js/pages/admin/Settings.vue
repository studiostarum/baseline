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
import { useTranslations } from '@/composables/useTranslations';
import AdminLayout from '@/layouts/AdminLayout.vue';
import AppHead from '@/components/AppHead.vue';
import { useForm, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

const { t } = useTranslations();
const canManageSettings = computed(
    () => usePage().props.auth.can_manage_settings,
);

type Props = {
    settings: Record<string, string>;
};

const props = defineProps<Props>();

const breadcrumbs = computed(() => [
    { title: t('admin.breadcrumb'), href: '/admin' },
    { title: t('admin.settings.title') },
]);

const form = useForm({
    settings: {
        site_name: props.settings.site_name || '',
        site_description: props.settings.site_description || '',
        contact_email: props.settings.contact_email || '',
        support_phone: props.settings.support_phone || '',
        maintenance_mode: props.settings.maintenance_mode || 'false',
    },
});

function submit(): void {
    form.post('/admin/settings');
}
</script>

<template>
    <AppHead :title="t('admin.settings.title')" />

    <AdminLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 p-4 md:p-6">
            <div>
                <h1 class="text-2xl font-bold tracking-tight">
                    {{ t('admin.settings.title') }}
                </h1>
                <p class="text-muted-foreground">
                    {{ t('admin.settings.description') }}
                </p>
            </div>

            <form @submit.prevent="submit" class="space-y-6">
                <Card>
                    <CardHeader>
                        <CardTitle>{{ t('admin.settings.general') }}</CardTitle>
                        <CardDescription>
                            {{ t('admin.settings.general_description') }}
                        </CardDescription>
                    </CardHeader>
                    <CardContent class="space-y-4">
                        <div class="space-y-2">
                            <Label for="site_name">{{
                                t('admin.settings.site_name')
                            }}</Label>
                            <Input
                                id="site_name"
                                v-model="form.settings.site_name"
                                type="text"
                                :placeholder="t('admin.settings.site_name_placeholder')"
                                :disabled="!canManageSettings"
                            />
                            <InputError
                                :message="form.errors['settings.site_name']"
                            />
                        </div>

                        <div class="space-y-2">
                            <Label for="site_description">
                                {{ t('admin.settings.site_description') }}
                            </Label>
                            <Input
                                id="site_description"
                                v-model="form.settings.site_description"
                                type="text"
                                :placeholder="t('admin.settings.site_description_placeholder')"
                                :disabled="!canManageSettings"
                            />
                            <InputError
                                :message="
                                    form.errors['settings.site_description']
                                "
                            />
                        </div>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader>
                        <CardTitle>{{ t('admin.settings.contact') }}</CardTitle>
                        <CardDescription>
                            {{ t('admin.settings.contact_description') }}
                        </CardDescription>
                    </CardHeader>
                    <CardContent class="space-y-4">
                        <div class="space-y-2">
                            <Label for="contact_email">{{
                                t('admin.settings.contact_email')
                            }}</Label>
                            <Input
                                id="contact_email"
                                v-model="form.settings.contact_email"
                                type="email"
                                :placeholder="t('admin.settings.contact_email_placeholder')"
                                :disabled="!canManageSettings"
                            />
                            <InputError
                                :message="form.errors['settings.contact_email']"
                            />
                        </div>

                        <div class="space-y-2">
                            <Label for="support_phone">{{
                                t('admin.settings.support_phone')
                            }}</Label>
                            <Input
                                id="support_phone"
                                v-model="form.settings.support_phone"
                                type="tel"
                                :placeholder="t('admin.settings.support_phone_placeholder')"
                                :disabled="!canManageSettings"
                            />
                            <InputError
                                :message="form.errors['settings.support_phone']"
                            />
                        </div>
                    </CardContent>
                </Card>

                <div class="flex items-center gap-4">
                    <Button
                        v-if="canManageSettings"
                        type="submit"
                        :disabled="form.processing"
                    >
                        {{
                            form.processing
                                ? t('admin.roles.saving')
                                : t('admin.settings.save_settings')
                        }}
                    </Button>
                    <span
                        v-if="form.recentlySuccessful"
                        class="text-sm text-green-600"
                    >
                        {{ t('admin.settings.saved_success') }}
                    </span>
                </div>
            </form>
        </div>
    </AdminLayout>
</template>
