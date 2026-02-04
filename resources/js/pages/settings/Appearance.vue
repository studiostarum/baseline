<script setup lang="ts">
import AppearanceTabs from '@/components/AppearanceTabs.vue';
import LanguageTabs from '@/components/LanguageTabs.vue';
import Heading from '@/components/Heading.vue';
import { useTranslations } from '@/composables/useTranslations';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import AppLayout from '@/layouts/AppLayout.vue';
import SettingsLayout from '@/layouts/settings/Layout.vue';
import { edit } from '@/routes/appearance';
import { type BreadcrumbItem } from '@/types';
import AppHead from '@/components/AppHead.vue';
import { Languages, Palette } from 'lucide-vue-next';
import { computed } from 'vue';

const { t } = useTranslations();

const breadcrumbItems = computed<BreadcrumbItem[]>(() => [
    {
        title: t('settings.appearance.title'),
        href: edit().url,
    },
]);
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbItems">
        <AppHead :title="t('settings.appearance.title')" />

        <h1 class="sr-only">{{ t('settings.appearance.title') }}</h1>

        <SettingsLayout>
            <div class="space-y-6">
                <Heading
                    variant="small"
                    :title="t('navigation.appearance')"
                    :description="t('settings.appearance.description_short')"
                />

                <Card>
                    <CardHeader>
                        <CardTitle class="flex items-center gap-2">
                            <Palette class="h-5 w-5" />
                            {{ t('settings.appearance.theme') }}
                        </CardTitle>
                        <CardDescription>
                            {{ t('settings.appearance.theme_description') }}
                        </CardDescription>
                    </CardHeader>
                    <CardContent>
                        <AppearanceTabs />
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader>
                        <CardTitle class="flex items-center gap-2">
                            <Languages class="h-5 w-5" />
                            {{ t('settings.appearance.language') }}
                        </CardTitle>
                        <CardDescription>
                            {{ t('settings.appearance.language_description') }}
                        </CardDescription>
                    </CardHeader>
                    <CardContent>
                        <LanguageTabs />
                    </CardContent>
                </Card>
            </div>
        </SettingsLayout>
    </AppLayout>
</template>
