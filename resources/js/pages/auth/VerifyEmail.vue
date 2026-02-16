<script setup lang="ts">
import { Form } from '@inertiajs/vue3';
import AppHead from '@/components/shared/AppHead.vue';
import TextLink from '@/components/shared/TextLink.vue';
import { Button } from '@/components/shared/ui/button';
import { Spinner } from '@/components/shared/ui/spinner';
import { useTranslations } from '@/composables/useTranslations';
import AuthLayout from '@/layouts/AuthLayout.vue';
import { logout } from '@/routes';
import { send } from '@/routes/verification';

const { t } = useTranslations();

defineProps<{
    status?: string;
}>();
</script>

<template>
    <AuthLayout
        :title="t('auth.verify_email.title')"
        :description="t('auth.verify_email.description')"
    >
        <AppHead :title="t('auth.verify_email.title')" />

        <div
            v-if="status === 'verification-link-sent'"
            class="mb-4 text-center text-sm font-medium text-green-600"
        >
            {{ t('auth.verify_email.link_sent') }}
        </div>

        <Form
            v-bind="send.form()"
            class="space-y-6 text-center"
            v-slot="{ processing }"
        >
            <Button :disabled="processing" variant="secondary">
                <Spinner v-if="processing" />
                {{ t('auth.verify_email.resend_button') }}
            </Button>

            <TextLink
                :href="logout()"
                as="button"
                class="mx-auto block text-sm"
            >
                {{ t('navigation.log_out') }}
            </TextLink>
        </Form>
    </AuthLayout>
</template>
