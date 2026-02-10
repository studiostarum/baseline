<script setup lang="ts">
import AppLogoIcon from '@/components/AppLogoIcon.vue';
import LanguageDropdown from '@/components/LanguageDropdown.vue';
import { useTranslations } from '@/composables/useTranslations';
import { home } from '@/routes';
import { Link, usePage } from '@inertiajs/vue3';
import { ArrowLeft } from 'lucide-vue-next';

const { t } = useTranslations();
const page = usePage();
const name = (page.props as { name?: string }).name ?? 'App';

defineProps<{
    title?: string;
    description?: string;
}>();
</script>

<template>
    <div
        class="relative grid h-dvh flex-col items-center justify-center px-8 sm:px-0 lg:max-w-none lg:grid-cols-2 lg:px-0"
    >
        <div
            class="relative hidden h-full flex-col justify-between bg-muted p-10 text-white lg:flex dark:border-r"
        >
            <div class="absolute inset-0 bg-zinc-900" />
            <Link
                :href="home()"
                class="relative z-20 flex items-center text-lg font-medium"
            >
                <AppLogoIcon class="mr-2 size-8 fill-current text-white" />
                {{ name }}
            </Link>
            <div class="relative z-20 text-white [&_button]:text-white [&_button:hover]:bg-white/10">
                <LanguageDropdown />
            </div>
        </div>
        <div class="relative flex h-full min-h-dvh flex-col justify-center p-10 lg:min-h-0 lg:p-10">
            <Link
                :href="home()"
                class="absolute left-10 top-10 z-[1] flex items-center gap-2 text-sm text-muted-foreground underline-offset-4 hover:underline"
            >
                <ArrowLeft class="size-4 shrink-0" />
                {{ t('auth.back_to_home') }}
            </Link>
            <div
                class="mx-auto flex w-full flex-col justify-center space-y-6 sm:w-[350px]"
            >
                <div class="flex flex-col space-y-2 text-center">
                    <h1 class="text-xl font-medium tracking-tight" v-if="title">
                        {{ title }}
                    </h1>
                    <p class="text-sm text-muted-foreground" v-if="description">
                        {{ description }}
                    </p>
                </div>
                <slot />
            </div>
        </div>
    </div>
</template>
