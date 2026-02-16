<script setup lang="ts">
import { usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

const page = usePage();
const showLocales = computed(() => page.props.features?.locales !== false);
const locale = page.props.locale ?? 'en';
const locales = (page.props.locales ?? { en: 'English', nl: 'Nederlands' }) as Record<string, string>;

const switchLocale = (code: string) => {
    if (code !== locale) {
        window.location.href = `/locale/${code}`;
    }
};
</script>

<template>
    <div
        v-if="showLocales"
        class="inline-flex gap-1 rounded-lg bg-neutral-100 p-1 dark:bg-neutral-800"
    >
        <button
            v-for="(label, code) in locales"
            :key="code"
            type="button"
            @click="switchLocale(code)"
            :class="[
                'flex items-center rounded-md px-3.5 py-1.5 transition-colors',
                locale === code
                    ? 'bg-white shadow-xs dark:bg-neutral-700 dark:text-neutral-100'
                    : 'text-neutral-500 hover:bg-neutral-200/60 hover:text-black dark:text-neutral-400 dark:hover:bg-neutral-700/60',
            ]"
        >
            <span class="text-sm">{{ label }}</span>
        </button>
    </div>
</template>
