<script setup lang="ts">
import HeroMockupDesktop from '@/components/home/HeroMockupDesktop.vue';
import HeroMockupMobile from '@/components/home/HeroMockupMobile.vue';
import WebsiteNavbar from '@/components/home/WebsiteNavbar.vue';
import { Badge } from '@/components/ui/badge';
import { dashboard, login, register } from '@/routes';
import AppHead from '@/components/AppHead.vue';
import { Link } from '@inertiajs/vue3';

withDefaults(
    defineProps<{
        canRegister: boolean;
    }>(),
    {
        canRegister: true,
    },
);
</script>

<template>
    <AppHead title="Home" />
    <div
        class="flex min-h-screen flex-col bg-[#FDFDFC] text-[#1b1b18] dark:bg-[#0a0a0a]">
        <WebsiteNavbar />

        <!-- Hero (recreates faqtuur hero section) -->
        <main
            class="container mx-auto flex min-h-[calc(100vh-3.5rem)] flex-col items-center justify-center gap-8 border-b py-16 text-center md:gap-12 md:py-20 lg:gap-20 lg:py-28">
            <div class="mx-auto flex max-w-3xl flex-col items-center justify-center gap-6 md:gap-8">
                <div class="flex flex-col items-center justify-center gap-3">
                    <Badge variant="outline">
                        New
                    </Badge>
                    <h1 class="font-bold leading-none tracking-tight text-4xl sm:text-5xl md:text-6xl">
                        Welcome
                    </h1>
                </div>
                <div class="flex flex-col items-center justify-center gap-3 sm:flex-row">
                    <template v-if="$page.props.auth.user">
                        <Link
                            :href="dashboard()"
                            class="inline-flex h-10 items-center justify-center rounded-md bg-primary px-6 font-medium text-primary-foreground transition-colors hover:bg-primary/90">
                            Dashboard
                        </Link>
                    </template>
                    <template v-else>
                        <Link
                            v-if="canRegister"
                            :href="register()"
                            class="inline-flex h-10 items-center justify-center rounded-md bg-primary px-6 font-medium text-primary-foreground transition-colors hover:bg-primary/90">
                            Get started
                        </Link>
                        <Link
                            :href="login()"
                            class="inline-flex h-10 items-center justify-center rounded-full border border-border bg-background px-6 font-medium shadow-xs transition-colors hover:border-border hover:bg-accent hover:text-accent-foreground">
                            Log in
                        </Link>
                    </template>
                </div>
            </div>

            <HeroMockupDesktop />
            <HeroMockupMobile />
        </main>
    </div>
</template>
