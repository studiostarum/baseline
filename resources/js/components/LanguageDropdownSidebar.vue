<script setup lang="ts">
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import {
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
    useSidebar,
} from '@/components/ui/sidebar';
import { usePage } from '@inertiajs/vue3';
import { Languages } from 'lucide-vue-next';

const page = usePage();
const locale = page.props.locale ?? 'en';
const locales = (page.props.locales ?? { en: 'English', nl: 'Nederlands' }) as Record<string, string>;

const { isMobile, state } = useSidebar();

const switchLocale = (code: string) => {
    if (code !== locale) {
        window.location.href = `/locale/${code}`;
    }
};
</script>

<template>
    <SidebarMenu>
        <SidebarMenuItem>
            <DropdownMenu>
                <DropdownMenuTrigger as-child>
                    <SidebarMenuButton
                        size="sm"
                        class="text-neutral-600 hover:text-neutral-800 data-[state=open]:bg-sidebar-accent data-[state=open]:text-sidebar-accent-foreground dark:text-neutral-300 dark:hover:text-neutral-100"
                    >
                        <Languages class="size-4" />
                        <span class="truncate">{{ locales[locale] ?? locale }}</span>
                    </SidebarMenuButton>
                </DropdownMenuTrigger>
                <DropdownMenuContent
                    :side="
                        isMobile || state === 'collapsed' ? 'right' : 'bottom'
                    "
                    align="start"
                    :side-offset="4"
                    class="min-w-40"
                >
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
        </SidebarMenuItem>
    </SidebarMenu>
</template>
