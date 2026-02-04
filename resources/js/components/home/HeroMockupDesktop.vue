<script setup lang="ts">
import AppLogoIcon from '@/components/AppLogoIcon.vue';
import {
    Bookmark,
    ChevronLeft,
    ChevronRight,
    Lock,
    Minus,
    Plus,
    RotateCw,
    Square,
    X,
} from 'lucide-vue-next';
import { computed } from 'vue';

const props = withDefaults(
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

const tabTitle = computed(() => {
    try {
        return new URL(props.url).host ?? 'example.com';
    } catch {
        return 'example.com';
    }
});

const radialBgStyle = {
    backgroundImage:
        'radial-gradient(ellipse at center, color-mix(in srgb, var(--primary) 20%, transparent), transparent 70%)',
};

const windowControls = [
    { bg: 'bg-red-500/80 hover:bg-red-500', icon: X },
    { bg: 'bg-yellow-500/80 hover:bg-yellow-500', icon: Minus },
    { bg: 'bg-green-500/80 hover:bg-green-500', icon: Square },
];

const browserControls = [
    { icon: ChevronLeft },
    { icon: ChevronRight },
    { icon: RotateCw },
    { icon: Bookmark },
];
</script>

<template>
    <div class="relative hidden w-full md:block">
        <div
            class="absolute inset-0 origin-center"
            :style="radialBgStyle" />

        <div
            class="relative flex w-full flex-col overflow-hidden rounded-xl border bg-card/60 text-primary-foreground shadow-2xl md:aspect-video md:min-h-[70vh]">
            <!-- Tab Bar -->
            <div class="flex h-8 items-center gap-2 border-b border-border/30 bg-muted/30 px-4">
                <div class="mr-2 flex items-center gap-2">
                    <button
                        v-for="(control, i) in windowControls"
                        :key="i"
                        type="button"
                        class="group flex aspect-square cursor-pointer items-center justify-center rounded-full p-0.5 transition"
                        :class="control.bg">
                        <component
                            :is="control.icon"
                            class="size-2.5 text-black/80 opacity-0 transition-opacity group-hover:opacity-100" />
                    </button>
                </div>
                <div
                    class="flex items-center gap-1.5 rounded-t border border-b-0 border-border/50 bg-background/80 px-3 py-1">
                    <AppLogoIcon class="size-3 text-primary" />
                    <span class="truncate text-xs text-muted-foreground">{{
                        tabTitle
                    }}</span>
                </div>
                <div
                    class="flex size-6 cursor-pointer items-center justify-center rounded transition hover:bg-foreground/5">
                    <Plus class="text-xs text-muted-foreground" />
                </div>
            </div>

            <!-- Browser Chrome -->
            <div class="flex h-12 items-center justify-between gap-4 border bg-secondary px-4">
                <div class="flex items-center gap-2">
                    <button
                        v-for="(control, i) in browserControls"
                        :key="i"
                        type="button"
                        class="flex cursor-pointer items-center justify-center rounded p-1 transition-colors hover:bg-foreground/5">
                        <component
                            :is="control.icon"
                            class="text-sm text-muted-foreground" />
                    </button>
                </div>
                <div
                    class="flex w-full items-center gap-2 rounded-full border border-border/50 bg-background/80 px-4 py-1.5 text-xs">
                    <Lock class="shrink-0 text-muted-foreground" />
                    <span class="truncate text-muted-foreground">{{ url }}</span>
                </div>
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
        </div>
    </div>
</template>
