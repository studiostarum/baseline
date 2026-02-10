<script setup lang="ts">
import AppLogoIcon from '@/components/AppLogoIcon.vue';
import LanguageDropdown from '@/components/LanguageDropdown.vue';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
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
        class="relative flex min-h-svh flex-col items-center justify-center gap-6 bg-muted p-6 md:p-10"
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
        <div class="flex w-full max-w-md flex-col gap-6">
            <Link
                :href="home()"
                class="flex items-center gap-2 self-center font-medium"
            >
                <div class="flex h-9 w-9 items-center justify-center">
                    <AppLogoIcon
                        class="size-9 fill-current text-black dark:text-white"
                    />
                </div>
            </Link>

            <div class="flex flex-col gap-6">
                <Card class="rounded-xl">
                    <CardHeader class="px-10 pt-8 pb-0 text-center">
                        <CardTitle class="text-xl">{{ title }}</CardTitle>
                        <CardDescription>
                            {{ description }}
                        </CardDescription>
                    </CardHeader>
                    <CardContent class="px-10 py-8">
                        <slot />
                    </CardContent>
                </Card>
            </div>
        </div>
    </div>
</template>
