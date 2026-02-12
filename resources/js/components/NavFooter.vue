<script setup lang="ts">
import {
    SidebarGroup,
    SidebarGroupContent,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
} from '@/components/ui/sidebar';
import { toUrl } from '@/lib/utils';
import { type NavItem } from '@/types';
import { Link } from '@inertiajs/vue3';
import { isRef, type Ref } from 'vue';

type Props = {
    items: NavItem[];
    class?: string;
};

defineProps<Props>();

const isExternalUrl = (href: string | { url: string }) => {
    const url = typeof href === 'string' ? href : href.url;
    return url.startsWith('http://') || url.startsWith('https://');
};

const getTitle = (title: string | Ref<string>): string => {
    return isRef(title) ? title.value : title;
};
</script>

<template>
    <SidebarGroup
        :class="`group-data-[collapsible=icon]:p-0 ${$props.class || ''}`"
    >
        <SidebarGroupContent>
            <SidebarMenu>
                <SidebarMenuItem v-for="item in items" :key="toUrl(item.href)">
                    <SidebarMenuButton
                        class="text-neutral-600 hover:text-neutral-800 dark:text-neutral-300 dark:hover:text-neutral-100"
                        as-child
                        :tooltip="getTitle(item.title)"
                    >
                        <a
                            v-if="isExternalUrl(item.href)"
                            :href="toUrl(item.href)"
                            target="_blank"
                            rel="noopener noreferrer"
                        >
                            <component :is="item.icon" />
                            <span>{{ getTitle(item.title) }}</span>
                        </a>
                        <Link v-else :href="toUrl(item.href)">
                            <component :is="item.icon" />
                            <span>{{ getTitle(item.title) }}</span>
                        </Link>
                    </SidebarMenuButton>
                </SidebarMenuItem>
            </SidebarMenu>
        </SidebarGroupContent>
    </SidebarGroup>
</template>
