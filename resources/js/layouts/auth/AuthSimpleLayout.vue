<script setup lang="ts">
import AppLogoIcon from '@/components/AppLogoIcon.vue';
import LanguageDropdown from '@/components/LanguageDropdown.vue';
import { useTranslations } from '@/composables/useTranslations';
import { home } from '@/routes';
import { Link } from '@inertiajs/vue3';
import { ArrowLeft } from 'lucide-vue-next';

const { t } = useTranslations();

defineProps<{
    title?: string;
    description?: string;
}>();
</script>

<template>
    <div
        class="relative flex min-h-svh flex-col items-center justify-center gap-6 bg-background p-6 md:p-10"
    >
        <Link
            :href="home()"
            class="absolute left-4 top-4 flex items-center gap-2 text-sm text-muted-foreground underline-offset-4 hover:underline"
        >
            <ArrowLeft class="size-4 shrink-0" />
            {{ t('auth.back_to_home') }}
        </Link>
        <div class="absolute right-4 top-4">
            <LanguageDropdown />
        </div>
        <div class="w-full max-w-sm">
            <div class="flex flex-col gap-8">
                <div class="flex flex-col items-center gap-4">
                    <Link
                        :href="home()"
                        class="flex flex-col items-center gap-2 font-medium"
                    >
                        <div
                            class="mb-1 flex h-9 w-9 items-center justify-center rounded-md"
                        >
                            <AppLogoIcon
                                class="size-9 fill-current text-[var(--foreground)] dark:text-white"
                            />
                        </div>
                        <span class="sr-only">{{ title }}</span>
                    </Link>
                    <div class="space-y-2 text-center">
                        <h1 class="text-xl font-medium">{{ title }}</h1>
                        <p class="text-center text-sm text-muted-foreground">
                            {{ description }}
                        </p>
                    </div>
                </div>
                <slot />
            </div>
        </div>
    </div>
</template>
