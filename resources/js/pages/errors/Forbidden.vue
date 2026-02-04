<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { useTranslations } from '@/composables/useTranslations';
import AuthLayout from '@/layouts/AuthLayout.vue';
import { dashboard } from '@/routes';
import AppHead from '@/components/AppHead.vue';
import { Link } from '@inertiajs/vue3';
import { ArrowLeft } from 'lucide-vue-next';

defineProps<{
    message?: string;
}>();

const { t } = useTranslations();
</script>

<template>
    <AuthLayout
        :title="t('errors.forbidden.title')"
        :description="t('errors.forbidden.description')"
    >
        <AppHead :title="t('errors.forbidden.title')" />

        <div class="flex flex-col gap-6">
            <p
                v-if="message"
                class="text-center text-sm text-muted-foreground"
            >
                {{ message }}
            </p>
            <Button as-child class="w-full" variant="default">
                <Link :href="dashboard().url" class="inline-flex items-center gap-2">
                    <ArrowLeft class="h-4 w-4" />
                    {{ t('errors.forbidden.head_back') }}
                </Link>
            </Button>
        </div>
    </AuthLayout>
</template>
