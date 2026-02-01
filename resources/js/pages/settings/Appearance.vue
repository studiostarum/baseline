<script setup lang="ts">
import AppearanceTabs from '@/components/AppearanceTabs.vue';
import Heading from '@/components/Heading.vue';
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
import { Head } from '@inertiajs/vue3';
import { Palette } from 'lucide-vue-next';
import { computed } from 'vue';

const breadcrumbItems = computed<BreadcrumbItem[]>(() => [
    {
        title: 'Appearance settings',
        href: edit().url,
    },
]);
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbItems">
        <Head title="Appearance settings" />

        <h1 class="sr-only">Appearance settings</h1>

        <SettingsLayout>
            <div class="space-y-6">
                <Heading
                    variant="small"
                    title="Appearance"
                    description="Customize your visual preferences."
                />

                <Card>
                    <CardHeader>
                        <CardTitle class="flex items-center gap-2">
                            <Palette class="h-5 w-5" />
                            Theme
                        </CardTitle>
                        <CardDescription>
                            Choose your preferred color scheme for the
                            interface.
                        </CardDescription>
                    </CardHeader>
                    <CardContent>
                        <AppearanceTabs />
                    </CardContent>
                </Card>
            </div>
        </SettingsLayout>
    </AppLayout>
</template>
