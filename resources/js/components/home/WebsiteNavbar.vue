<script setup lang="ts">
import AppLogoIcon from '@/components/AppLogoIcon.vue';
import { Button } from '@/components/ui/button';
import { Separator } from '@/components/ui/separator';
import { dashboard, home, login, logout, register } from '@/routes';
import { Link, usePage } from '@inertiajs/vue3';
import { List, X } from 'lucide-vue-next';
import { ref } from 'vue';
import { cn } from '@/lib/utils';
import { buttonVariants } from '@/components/ui/button';

const open = ref(false);

const page = usePage();
const user = page.props.auth?.user;
const appName = (page.props.name as string) ?? 'Laravel';

const navLinks: { href: string; label: string }[] = [
    { href: '#pricing', label: 'Pricing' },
];

function closeMenu() {
    open.value = false;
}
</script>

<template>
    <div class="relative">
        <nav
            class="sticky top-0 left-0 right-0 z-50 w-full border-b bg-background/80 backdrop-blur-lg">
            <div class="container flex min-h-12 items-center justify-between md:min-h-14">
                <!-- Logo/Brand -->
                <Link :href="home()" class="flex items-center gap-2">
                    <AppLogoIcon class="size-6 text-primary dark:text-foreground" />
                    <span class="text-2xl font-extrabold leading-none tracking-tight">{{
                        appName
                    }}</span>
                </Link>

                <!-- Desktop Navigation -->
                <div class="hidden items-center gap-2 md:flex">
                    <Link
                        v-for="link in navLinks"
                        :key="link.href"
                        :href="link.href"
                        :class="
                            cn(
                                buttonVariants({ variant: 'ghost', size: 'sm' }),
                            )
                        ">
                        {{ link.label }}
                    </Link>
                </div>

                <!-- Desktop Auth Buttons -->
                <div class="hidden items-center gap-2 md:flex">
                    <template v-if="user">
                        <Link :href="dashboard()">
                            <Button size="sm">
                                Dashboard
                            </Button>
                        </Link>
                        <Link :href="logout()" method="post" as="button">
                            <Button variant="ghost" size="sm">
                                Log out
                            </Button>
                        </Link>
                    </template>
                    <template v-else>
                        <Link :href="login()">
                            <Button size="sm" variant="outline">
                                Sign in
                            </Button>
                        </Link>
                        <Link v-if="($page.props.canRegister ?? true)" :href="register()">
                            <Button size="sm">
                                Get started
                            </Button>
                        </Link>
                    </template>
                </div>

                <!-- Mobile menu button -->
                <div class="md:hidden">
                    <Button
                        variant="ghost"
                        size="icon-sm"
                        type="button"
                        aria-label="Toggle menu"
                        @click="open = !open">
                        <X v-if="open" class="size-5" />
                        <List v-else class="size-5" />
                    </Button>
                </div>
            </div>

            <!-- Mobile menu -->
            <Transition
                enter-active-class="transition ease-out duration-200"
                enter-from-class="opacity-0 -translate-y-2"
                enter-to-class="opacity-100 translate-y-0"
                leave-active-class="transition ease-in duration-150"
                leave-from-class="opacity-100 translate-y-0"
                leave-to-class="opacity-0 -translate-y-2">
                <div
                    v-show="open"
                    class="absolute left-0 right-0 top-[calc(100%+1px)] z-50 border-b bg-background md:hidden"
                    role="dialog"
                    aria-label="Mobile menu">
                    <div class="flex flex-col gap-1 p-2">
                        <Link
                            v-for="link in navLinks"
                            :key="link.href"
                            :href="link.href"
                            class="w-full justify-start"
                            :class="
                                cn(
                                    buttonVariants({ variant: 'ghost', size: 'sm' }),
                                )
                            "
                            @click="closeMenu">
                            {{ link.label }}
                        </Link>
                    </div>

                    <Separator class="mx-2" />

                    <div class="flex flex-col gap-1 p-2">
                        <template v-if="user">
                            <Link
                                :href="dashboard()"
                                class="w-full justify-start"
                                :class="
                                    cn(
                                        buttonVariants({ size: 'sm' }),
                                    )
                                "
                                @click="closeMenu">
                                Dashboard
                            </Link>
                            <Link
                                :href="logout()"
                                method="post"
                                as="button"
                                class="w-full justify-start"
                                :class="
                                    cn(
                                        buttonVariants({
                                            variant: 'ghost',
                                            size: 'sm',
                                        }),
                                    )
                                "
                                @click="closeMenu">
                                Log out
                            </Link>
                        </template>
                        <template v-else>
                            <Link
                                :href="login()"
                                class="w-full justify-start"
                                :class="
                                    cn(
                                        buttonVariants({
                                            variant: 'outline',
                                            size: 'sm',
                                        }),
                                    )
                                "
                                @click="closeMenu">
                                Sign in
                            </Link>
                            <Link
                                v-if="($page.props.canRegister ?? true)"
                                :href="register()"
                                class="w-full justify-start"
                                :class="
                                    cn(
                                        buttonVariants({ size: 'sm' }),
                                    )
                                "
                                @click="closeMenu">
                                Get started
                            </Link>
                        </template>
                    </div>
                </div>
            </Transition>
        </nav>

        <!-- Background Overlay -->
        <Transition
            enter-active-class="transition-opacity ease-linear duration-200"
            enter-from-class="opacity-0"
            enter-to-class="opacity-100"
            leave-active-class="transition-opacity ease-linear duration-200"
            leave-from-class="opacity-100"
            leave-to-class="opacity-0">
            <button
                v-show="open"
                type="button"
                class="fixed inset-0 z-40 bg-black/80 backdrop-blur md:hidden"
                aria-label="Close menu"
                @click="closeMenu"
            />
        </Transition>
    </div>
</template>
