<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import { ArrowLeft } from 'lucide-vue-next';
import { computed } from 'vue';
import AppHead from '@/components/AppHead.vue';
import { Button } from '@/components/ui/button';
import { useTranslations } from '@/composables/useTranslations';
import AuthLayout from '@/layouts/AuthLayout.vue';
import { dashboard, home } from '@/routes';

defineProps<{
    message?: string;
}>();

const { t } = useTranslations();
const page = usePage();
const isAuthenticated = computed(() => Boolean(page.props.auth?.user));
const backUrl = computed(() => (isAuthenticated.value ? dashboard().url : home().url));
const backLabel = computed(() =>
    isAuthenticated.value ? t('errors.forbidden.head_back') : t('errors.not_found.back_home'),
);
</script>

<template>
    <AuthLayout
        :title="t('errors.not_found.title')"
        :description="t('errors.not_found.description')"
    >
        <AppHead :title="t('errors.not_found.title')" />

        <div class="flex flex-col gap-6">
            <p
                v-if="message"
                class="text-center text-sm text-muted-foreground"
            >
                {{ message }}
            </p>
            <Button as-child class="w-full" variant="default">
                <Link :href="backUrl" class="inline-flex items-center gap-2">
                    <ArrowLeft class="h-4 w-4" />
                    {{ backLabel }}
                </Link>
            </Button>
        </div>
    </AuthLayout>
</template>
