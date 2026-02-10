<script setup lang="ts">
import TextLink from '@/components/TextLink.vue';
import { Button } from '@/components/ui/button';
import { Spinner } from '@/components/ui/spinner';
import AuthLayout from '@/layouts/AuthLayout.vue';
import { useTranslations } from '@/composables/useTranslations';
import { logout } from '@/routes';
import { send } from '@/routes/verification';
import AppHead from '@/components/AppHead.vue';
import { Form } from '@inertiajs/vue3';

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
