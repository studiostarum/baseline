<script setup lang="ts">
import {
    ArrowLeft,
    ArrowRight,
    Home,
    List,
    Lock,
    MoreVertical,
} from 'lucide-vue-next';

withDefaults(
    defineProps<{
        image?: string | null;
        title?: string;
        url?: string;
    }>(),
    {
        title: 'Preview',
        url: 'https://example.com',
    },
);

const bottomBarIcons = [
    { icon: Home },
    { icon: ArrowLeft },
    { icon: ArrowRight },
    { icon: List },
];
</script>

<template>
    <div class="relative block w-full md:hidden">
        <div
            class="absolute inset-0 origin-center"
            :style="{
                backgroundImage:
                    'radial-gradient(ellipse at center, color-mix(in srgb, var(--primary) 20%, transparent), transparent 70%)',
            }" />

        <div
            class="relative mx-auto flex h-full min-h-[60vh] w-full max-w-sm flex-col overflow-hidden rounded-2xl border bg-card/60 text-primary-foreground shadow-2xl aspect-9/16">
            <!-- Address Bar -->
            <div class="flex h-12 items-center justify-between gap-2 border-b bg-secondary px-3 min-w-0">
                <div class="flex min-w-0 flex-1 items-center gap-2">
                    <Lock class="size-3.5 shrink-0 text-muted-foreground" />
                    <span class="truncate text-xs text-muted-foreground">{{
                        url
                    }}</span>
                </div>
                <button
                    type="button"
                    class="flex shrink-0 cursor-pointer items-center justify-center rounded p-1.5 transition-colors hover:bg-foreground/5">
                    <MoreVertical class="text-sm text-muted-foreground" />
                </button>
            </div>

            <!-- Content Area -->
            <div
                class="flex-1 overflow-hidden bg-gradient-to-b from-card to-background">
                <img
                    v-if="image"
                    :src="image"
                    :alt="title"
                    class="h-full w-full object-cover" />
                <div
                    v-else
                    class="flex h-full w-full items-center justify-center bg-gradient-to-br from-primary to-secondary text-primary-foreground" />
            </div>

            <!-- Bottom Bar -->
            <div class="flex h-14 items-center justify-around border-t bg-secondary px-2">
                <button
                    v-for="(item, i) in bottomBarIcons"
                    :key="i"
                    type="button"
                    class="flex cursor-pointer items-center justify-center rounded p-2 transition-colors hover:bg-foreground/5">
                    <component
                        :is="item.icon"
                        class="text-lg text-muted-foreground" />
                </button>
            </div>
        </div>
    </div>
</template>
