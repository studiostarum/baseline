<script setup lang="ts">
import {
    DropdownMenuGroup,
    DropdownMenuItem,
    DropdownMenuLabel,
    DropdownMenuSeparator,
} from '@/components/ui/dropdown-menu';
import UserInfo from '@/components/UserInfo.vue';
import { useTranslations } from '@/composables/useTranslations';
import { home, logout } from '@/routes';
import { edit } from '@/routes/profile';
import type { User } from '@/types';
import { Link, usePage, router } from '@inertiajs/vue3';
import { Globe, Languages, LogOut, Settings } from 'lucide-vue-next';

type Props = {
    user: User;
};

const handleLogout = () => {
    router.flushAll();
};

defineProps<Props>();

const { t } = useTranslations();

const page = usePage();
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
            <Link class="block w-full cursor-pointer" :href="edit()" prefetch>
                <Settings class="mr-2 h-4 w-4" />
                {{ t('navigation.settings') }}
            </Link>
        </DropdownMenuItem>
        <DropdownMenuItem :as-child="true">
            <Link class="block w-full cursor-pointer" :href="home()" prefetch>
                <Globe class="mr-2 h-4 w-4" />
                {{ t('navigation.website') }}
            </Link>
        </DropdownMenuItem>
    </DropdownMenuGroup>
    <DropdownMenuSeparator />
    <DropdownMenuGroup>
        <DropdownMenuItem
            v-for="(localeLabel, code) in locales"
            :key="code"
            :class="{ 'bg-accent': locale === code }"
            @select="switchLocale(code)"
        >
            <Languages class="mr-2 h-4 w-4" />
            {{ localeLabel }}
        </DropdownMenuItem>
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
