<script setup lang="ts">
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import { Button } from '@/components/ui/button';
import LanguageDropdownSidebar from '@/components/LanguageDropdownSidebar.vue';
import { usePage } from '@inertiajs/vue3';
import { Languages } from 'lucide-vue-next';

type Props = {
    variant?: 'button' | 'sidebar';
};

const props = withDefaults(defineProps<Props>(), {
    variant: 'button',
});

const page = usePage();
const locale = page.props.locale ?? 'en';
const locales = (page.props.locales ?? { en: 'English', nl: 'Nederlands' }) as Record<string, string>;
const translations = (page.props.translations ?? {}) as Record<string, string>;
const label = translations['common.language'] ?? 'Language';

const switchLocale = (code: string) => {
    if (code !== locale) {
        window.location.href = `/locale/${code}`;
    }
};
</script>

<template>
    <LanguageDropdownSidebar v-if="variant === 'sidebar'" />
    <DropdownMenu v-else>
        <DropdownMenuTrigger as-child>
            <Button variant="ghost" size="icon" class="h-9 w-9">
                <Languages class="size-5" />
                <span class="sr-only">{{ label }}</span>
            </Button>
        </DropdownMenuTrigger>
        <DropdownMenuContent align="end" class="min-w-40">
            <DropdownMenuItem
                v-for="(localeLabel, code) in locales"
                :key="code"
                :class="{ 'bg-accent': locale === code }"
                @select="switchLocale(code)"
            >
                {{ localeLabel }}
            </DropdownMenuItem>
        </DropdownMenuContent>
    </DropdownMenu>
</template>
