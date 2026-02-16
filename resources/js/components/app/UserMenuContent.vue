<script setup lang="ts">
import { Link, usePage, router } from '@inertiajs/vue3';
import { Globe, Languages, LayoutGrid, LogOut, Monitor, Moon, Settings, Sun } from 'lucide-vue-next';
import { computed } from 'vue';
import {
    DropdownMenuGroup,
    DropdownMenuItem,
    DropdownMenuLabel,
    DropdownMenuRadioGroup,
    DropdownMenuRadioItem,
    DropdownMenuSeparator,
} from '@/components/shared/ui/dropdown-menu';
import UserInfo from '@/components/app/UserInfo.vue';
import { useAppearance } from '@/composables/useAppearance';
import { useCurrentUrl } from '@/composables/useCurrentUrl';
import { useTranslations } from '@/composables/useTranslations';
import { dashboard, home, logout } from '@/routes';
import { edit } from '@/routes/profile';
import type { Appearance, User } from '@/types';

type Props = {
    user: User;
};

const handleLogout = () => {
    router.flushAll();
};

defineProps<Props>();

const { t } = useTranslations();
const { appearance, updateAppearance } = useAppearance();

const page = usePage();
const showLocales = computed(() => page.props.features?.locales !== false);
const locale = page.props.locale ?? 'en';
const locales = (page.props.locales ?? {
    en: 'English',
    nl: 'Nederlands',
}) as Record<string, string>;

const switchLocale = (code: string) => {
    if (code !== locale) {
        window.location.href = `/locale/${code}`;
    }
};

const { currentUrl } = useCurrentUrl();
const isAppSide = computed(() => {
    const path = currentUrl.value;
    return path.startsWith('/dashboard') || path.startsWith('/settings') || path.startsWith('/admin');
});
</script>

<template>
    <DropdownMenuLabel class="p-0 font-normal">
        <div class="flex items-center gap-2 px-1 py-1.5 text-left text-sm">
            <UserInfo :user="user" :show-email="true" />
        </div>
    </DropdownMenuLabel>
    <DropdownMenuSeparator />
    <DropdownMenuGroup>
        <DropdownMenuItem :as-child="true">
            <Link
                class="block w-full cursor-pointer"
                :href="isAppSide ? home() : dashboard()"
                prefetch
            >
                <LayoutGrid v-if="!isAppSide" class="mr-2 h-4 w-4" />
                <Globe v-else class="mr-2 h-4 w-4" />
                {{ isAppSide ? t('navigation.go_to_website') : t('navigation.go_to_app') }}
            </Link>
        </DropdownMenuItem>
        <DropdownMenuItem :as-child="true">
            <Link class="block w-full cursor-pointer" :href="edit()" prefetch>
                <Settings class="mr-2 h-4 w-4" />
                {{ t('navigation.settings') }}
            </Link>
        </DropdownMenuItem>
    </DropdownMenuGroup>
    <DropdownMenuSeparator />
    <DropdownMenuGroup>
        <DropdownMenuRadioGroup
            :model-value="appearance"
            @update:model-value="(v) => v && updateAppearance(v as Appearance)"
        >
            <DropdownMenuRadioItem value="light">
                <Sun class="mr-2 size-4 shrink-0" />
                {{ t('settings.appearance.light') }}
            </DropdownMenuRadioItem>
            <DropdownMenuRadioItem value="dark">
                <Moon class="mr-2 size-4 shrink-0" />
                {{ t('settings.appearance.dark') }}
            </DropdownMenuRadioItem>
            <DropdownMenuRadioItem value="system">
                <Monitor class="mr-2 size-4 shrink-0" />
                {{ t('settings.appearance.system') }}
            </DropdownMenuRadioItem>
        </DropdownMenuRadioGroup>
    </DropdownMenuGroup>
    <DropdownMenuSeparator v-if="showLocales" />
    <DropdownMenuGroup v-if="showLocales">
        <DropdownMenuRadioGroup
            :model-value="locale"
            @update:model-value="(v) => typeof v === 'string' && switchLocale(v)"
        >
            <DropdownMenuRadioItem
                v-for="(localeLabel, code) in locales"
                :key="code"
                :value="code"
            >
                <Languages class="mr-2 size-4 shrink-0" />
                {{ localeLabel }}
            </DropdownMenuRadioItem>
        </DropdownMenuRadioGroup>
    </DropdownMenuGroup>
    <DropdownMenuSeparator />
    <DropdownMenuItem :as-child="true">
        <Link
            class="block w-full cursor-pointer"
            :href="logout()"
            @click="handleLogout"
            as="button"
            data-test="logout-button"
        >
            <LogOut class="mr-2 h-4 w-4" />
            {{ t('navigation.log_out') }}
        </Link>
    </DropdownMenuItem>
</template>
