<script setup lang="ts">
import { Form } from '@inertiajs/vue3';
import { KeyRound } from 'lucide-vue-next';
import { computed } from 'vue';
import PasswordController from '@/actions/App/Http/Controllers/Settings/PasswordController';
import AppHead from '@/components/AppHead.vue';
import Heading from '@/components/Heading.vue';
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
import { useTranslations } from '@/composables/useTranslations';
import AppLayout from '@/layouts/AppLayout.vue';
import SettingsLayout from '@/layouts/settings/Layout.vue';
import { edit } from '@/routes/user-password';
import { type BreadcrumbItem } from '@/types';

const { t } = useTranslations();

const breadcrumbItems = computed<BreadcrumbItem[]>(() => [
    {
        title: t('settings.password.title'),
        href: edit().url,
    },
]);
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbItems">
        <AppHead :title="t('settings.password.title')" />

        <h1 class="sr-only">{{ t('settings.password.title') }}</h1>

        <SettingsLayout>
            <div class="space-y-6">
                <Heading
                    variant="small"
                    :title="t('navigation.password')"
                    :description="t('settings.password.description_short')"
                />

                <Card>
                    <CardHeader>
                        <CardTitle class="flex items-center gap-2">
                            <KeyRound class="h-5 w-5" />
                            {{ t('settings.password.heading') }}
                        </CardTitle>
                        <CardDescription>
                            {{ t('settings.password.description') }}
                        </CardDescription>
                    </CardHeader>
                    <CardContent>
                        <Form
                            v-bind="PasswordController.update.form()"
                            :options="{
                                preserveScroll: true,
                            }"
                            reset-on-success
                            :reset-on-error="[
                                'password',
                                'password_confirmation',
                                'current_password',
                            ]"
                            class="space-y-6"
                            v-slot="{ errors, processing, recentlySuccessful }"
                        >
                            <div class="grid gap-2">
                                <Label for="current_password"
                                    >{{ t('fields.current_password') }}</Label
                                >
                                <Input
                                    id="current_password"
                                    name="current_password"
                                    type="password"
                                    class="mt-1 block w-full"
                                    autocomplete="current-password"
                                    :placeholder="t('fields.current_password_placeholder')"
                                />
                                <InputError
                                    :message="errors.current_password"
                                />
                            </div>

                            <div class="grid gap-2">
                                <Label for="password">{{ t('fields.new_password') }}</Label>
                                <Input
                                    id="password"
                                    name="password"
                                    type="password"
                                    class="mt-1 block w-full"
                                    autocomplete="new-password"
                                    :placeholder="t('fields.new_password_placeholder')"
                                />
                                <InputError :message="errors.password" />
                            </div>

                            <div class="grid gap-2">
                                <Label for="password_confirmation"
                                    >{{ t('fields.confirm_password') }}</Label
                                >
                                <Input
                                    id="password_confirmation"
                                    name="password_confirmation"
                                    type="password"
                                    class="mt-1 block w-full"
                                    autocomplete="new-password"
                                    :placeholder="t('fields.confirm_password_placeholder')"
                                />
                                <InputError
                                    :message="errors.password_confirmation"
                                />
                            </div>

                            <div class="flex items-center gap-4">
                                <Button
                                    :disabled="processing"
                                    data-test="update-password-button"
                                >
                                    {{ t('settings.password.save_button') }}
                                </Button>

                                <Transition
                                    enter-active-class="transition ease-in-out"
                                    enter-from-class="opacity-0"
                                    leave-active-class="transition ease-in-out"
                                    leave-to-class="opacity-0"
                                >
                                    <p
                                        v-show="recentlySuccessful"
                                        class="text-sm text-neutral-600"
                                    >
                                        {{ t('common.saved') }}
                                    </p>
                                </Transition>
                            </div>
                        </Form>
                    </CardContent>
                </Card>
            </div>
        </SettingsLayout>
    </AppLayout>
</template>
