<script setup lang="ts">
import { OhVueIcon, addIcons } from 'oh-vue-icons';
import { SiGithub, SiGoogle } from 'oh-vue-icons/icons';

if (typeof window !== 'undefined') {
    addIcons(SiGoogle, SiGithub);
}

const PROVIDER_ICONS: Record<string, string> = {
    google: 'si-google',
    github: 'si-github',
};

const props = defineProps<{
    provider: string;
    size?: string;
}>();

const iconName = PROVIDER_ICONS[props.provider.toLowerCase()] ?? null;
const sizeClass = props.size ?? 'size-10';
</script>

<template>
    <div
        class="flex items-center justify-center rounded-full bg-muted"
        :class="sizeClass"
    >
        <OhVueIcon
            v-if="iconName"
            :name="iconName"
            class="size-6"
        />
        <span
            v-else
            class="text-sm font-medium uppercase"
        >
            {{ provider.charAt(0) }}
        </span>
    </div>
</template>
