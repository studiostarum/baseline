<script setup lang="ts">
import AppHead from '@/components/AppHead.vue';
import WebsiteFooter from '@/components/home/WebsiteFooter.vue';
import WebsiteNavbar from '@/components/home/WebsiteNavbar.vue';

defineProps<{
    title?: string;
    description?: string;
}>();
</script>

<template>
    <AppHead :title="title" :description="description" />
    <div class="flex min-h-screen flex-col">
        <WebsiteNavbar />
        <main
            class="flex min-h-[calc(100vh-3.5rem)] flex-col items-center justify-center gap-8 border-b py-16 text-center md:gap-12 md:py-20 lg:gap-20 lg:py-28">
            <slot />
        </main>
        <slot name="below" />
        <WebsiteFooter />
    </div>
</template>
