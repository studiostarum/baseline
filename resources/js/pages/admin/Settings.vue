<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AdminLayout from '@/layouts/AdminLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { computed } from 'vue';

type Props = {
    settings: Record<string, string>;
};

const props = defineProps<Props>();

const breadcrumbs = computed(() => [
    { title: 'Admin', href: '/admin' },
    { title: 'Settings' },
]);

const form = useForm({
    settings: {
        site_name: props.settings.site_name || '',
        site_description: props.settings.site_description || '',
        contact_email: props.settings.contact_email || '',
        support_phone: props.settings.support_phone || '',
        maintenance_mode: props.settings.maintenance_mode || 'false',
    },
});

function submit(): void {
    form.post('/admin/settings');
}
</script>

<template>
    <Head title="Settings" />

    <AdminLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 p-4 md:p-6">
            <div>
                <h1 class="text-2xl font-bold tracking-tight">Settings</h1>
                <p class="text-muted-foreground">
                    Manage application settings.
                </p>
            </div>

            <form @submit.prevent="submit" class="max-w-2xl space-y-6">
                <Card>
                    <CardHeader>
                        <CardTitle>General Settings</CardTitle>
                        <CardDescription
                            >Configure basic application
                            settings.</CardDescription
                        >
                    </CardHeader>
                    <CardContent class="space-y-4">
                        <div class="space-y-2">
                            <Label for="site_name">Site Name</Label>
                            <Input
                                id="site_name"
                                v-model="form.settings.site_name"
                                type="text"
                                placeholder="My Application"
                            />
                            <InputError
                                :message="form.errors['settings.site_name']"
                            />
                        </div>

                        <div class="space-y-2">
                            <Label for="site_description"
                                >Site Description</Label
                            >
                            <Input
                                id="site_description"
                                v-model="form.settings.site_description"
                                type="text"
                                placeholder="A brief description of your application"
                            />
                            <InputError
                                :message="
                                    form.errors['settings.site_description']
                                "
                            />
                        </div>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader>
                        <CardTitle>Contact Information</CardTitle>
                        <CardDescription
                            >Contact details for the
                            application.</CardDescription
                        >
                    </CardHeader>
                    <CardContent class="space-y-4">
                        <div class="space-y-2">
                            <Label for="contact_email">Contact Email</Label>
                            <Input
                                id="contact_email"
                                v-model="form.settings.contact_email"
                                type="email"
                                placeholder="contact@example.com"
                            />
                            <InputError
                                :message="form.errors['settings.contact_email']"
                            />
                        </div>

                        <div class="space-y-2">
                            <Label for="support_phone">Support Phone</Label>
                            <Input
                                id="support_phone"
                                v-model="form.settings.support_phone"
                                type="tel"
                                placeholder="+1 (555) 123-4567"
                            />
                            <InputError
                                :message="form.errors['settings.support_phone']"
                            />
                        </div>
                    </CardContent>
                </Card>

                <div class="flex items-center gap-4">
                    <Button type="submit" :disabled="form.processing">
                        {{ form.processing ? 'Saving...' : 'Save Settings' }}
                    </Button>
                    <span
                        v-if="form.recentlySuccessful"
                        class="text-sm text-green-600"
                    >
                        Saved successfully.
                    </span>
                </div>
            </form>
        </div>
    </AdminLayout>
</template>
