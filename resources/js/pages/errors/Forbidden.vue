<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { ArrowLeft } from 'lucide-vue-next';
import { computed } from 'vue';
import AppHead from '@/components/shared/AppHead.vue';
import { Button } from '@/components/shared/ui/button';
import { useTranslations } from '@/composables/useTranslations';
import AuthLayout from '@/layouts/AuthLayout.vue';
import { dashboard } from '@/routes';

const props = defineProps<{
    message?: string;
}>();

const { t } = useTranslations();

const displayMessage = computed(() => {
    if (!props.message) {
        return null;
    }
    if (/^[\w.]+$/.test(props.message)) {
        return t(props.message);
    }
    return props.message;
});
</script>

<template>
    <AuthLayout
        :title="t('errors.forbidden.title')"
        :description="t('errors.forbidden.description')"
    >
        <AppHead :title="t('errors.forbidden.title')" />

        <div class="flex flex-col gap-6">
            <p
                v-if="displayMessage"
                class="text-center text-sm text-muted-foreground"
            >
                {{ displayMessage }}
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
