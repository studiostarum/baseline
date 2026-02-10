<script setup lang="ts">
import AppLogoIcon from '@/components/AppLogoIcon.vue';
import UserMenuContent from '@/components/UserMenuContent.vue';
import UserInfo from '@/components/UserInfo.vue';
import { Button } from '@/components/ui/button';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import { Separator } from '@/components/ui/separator';
import { contact, dashboard, home, login, logout, register } from '@/routes';
import { Link, usePage } from '@inertiajs/vue3';
import { ChevronsUpDown, List, X } from 'lucide-vue-next';
import { computed, onMounted, onUnmounted, ref } from 'vue';
import { cn } from '@/lib/utils';
import { buttonVariants } from '@/components/ui/button';
import { useTranslations } from '@/composables/useTranslations';

const open = ref(false);
const navVisible = ref(true);
const lastScrollY = ref(0);
const scrollThreshold = 60;

function onScroll() {
    if (open.value) {
        return;
    }
    const y = window.scrollY;
    if (y <= 10) {
        navVisible.value = true;
    } else if (y > lastScrollY.value && y > scrollThreshold) {
        navVisible.value = false;
    } else if (y < lastScrollY.value) {
        navVisible.value = true;
    }
    lastScrollY.value = y;
}

let tick: ReturnType<typeof requestAnimationFrame> | null = null;
function handleScroll() {
    if (tick === null) {
        tick = requestAnimationFrame(() => {
            onScroll();
            tick = null;
        });
    }
}

onMounted(() => {
    lastScrollY.value = window.scrollY;
    window.addEventListener('scroll', handleScroll, { passive: true });
});

onUnmounted(() => {
    window.removeEventListener('scroll', handleScroll);
});

const { t } = useTranslations();

const page = usePage();
const user = page.props.auth?.user;
const appName = (page.props.name as string) ?? 'Laravel';
const canRegister = (page.props.canRegister ?? true) as boolean;

const navLinks = computed(() => [
    { href: `${home().url}#features`, label: t('website.footer.features') },
    { href: `${home().url}#pricing`, label: t('website.pricing') },
    { href: contact().url, label: t('website.footer.contact') },
]);

function closeMenu() {
    open.value = false;
}

function toggleMenu() {
    open.value = !open.value;
    if (open.value) {
        navVisible.value = true;
    }
}
</script>

<template>
    <div
        class="sticky top-0 z-50 w-full bg-background/80 backdrop-blur-lg transition-transform duration-300 ease-out"
        :class="{ '-translate-y-full': !navVisible }"
    >
        <nav class="border-b">
            <div class="container mx-auto flex min-h-12 items-center justify-between md:min-h-14">
                <!-- Logo/Brand -->
                <Link :href="home()" class="flex items-center gap-2">
                    <AppLogoIcon class="size-6 text-primary dark:text-foreground" />
                    <span class="text-xl font-bold leading-none tracking-tight">{{
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

                <!-- Desktop: Auth – same buttons as hero when not logged in, user menu when logged in -->
                <div class="hidden items-center gap-2 md:flex">
                    <template v-if="user">
                        <DropdownMenu>
                            <DropdownMenuTrigger as-child>
                                <Button
                                    variant="ghost"
                                    size="sm"
                                    class="flex h-9 items-center gap-2 px-2 data-[state=open]:bg-accent"
                                >
                                    <UserInfo :user="user" />
                                    <ChevronsUpDown class="size-4 opacity-50" />
                                </Button>
                            </DropdownMenuTrigger>
                            <DropdownMenuContent align="end" class="min-w-56">
                                <UserMenuContent :user="user" />
                            </DropdownMenuContent>
                        </DropdownMenu>
                    </template>
                    <template v-else>
                        <Button v-if="canRegister" as-child size="sm">
                            <Link :href="register()">
                                {{ t('website.get_started') }}
                            </Link>
                        </Button>
                        <Button as-child variant="outline" size="sm">
                            <Link :href="login()">
                                {{ t('website.sign_in') }}
                            </Link>
                        </Button>
                    </template>
                </div>

                <!-- Mobile menu button -->
                <div class="md:hidden">
                    <Button
                        variant="ghost"
                        size="icon-sm"
                        type="button"
                        aria-label="Toggle menu"
                        @click="toggleMenu">
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
                                {{ t('navigation.dashboard') }}
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
                                {{ t('navigation.log_out') }}
                            </Link>
                        </template>
                        <template v-else>
                            <Link
                                v-if="canRegister"
                                :href="register()"
                                class="w-full justify-start"
                                :class="
                                    cn(
                                        buttonVariants({ size: 'sm' }),
                                    )
                                "
                                @click="closeMenu">
                                {{ t('website.get_started') }}
                            </Link>
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
                                {{ t('website.sign_in') }}
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
