<script setup lang="ts">
import { Form, router, usePage } from '@inertiajs/vue3';
import { onMounted } from 'vue';
import AppHead from '@/components/AppHead.vue';
import InputError from '@/components/InputError.vue';
import SocialLoginButton from '@/components/SocialLoginButton.vue';
import TextLink from '@/components/TextLink.vue';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Separator } from '@/components/ui/separator';
import { Spinner } from '@/components/ui/spinner';
import { useTranslations } from '@/composables/useTranslations';
import AuthBase from '@/layouts/AuthLayout.vue';
import { register } from '@/routes';
import { store } from '@/routes/login';
import { request } from '@/routes/password';

const { t } = useTranslations();

defineProps<{
    status?: string;
    canResetPassword: boolean;
    canRegister: boolean;
}>();

const page = usePage();

onMounted(() => {
    // Check if user is now authenticated (from OAuth popup)
    if (page.props.auth?.user) {
        router.visit('/dashboard');
    }
});
</script>

<template>
    <AuthBase
        :title="t('auth.login.title')"
        :description="t('auth.login.description')"
    >
        <AppHead :title="t('auth.login.heading')" />

        <div
            v-if="status"
            class="mb-4 text-center text-sm font-medium text-green-600"
        >
            {{ status }}
        </div>

        <Form
            v-bind="store.form()"
            :reset-on-success="['password']"
            v-slot="{ errors, processing }"
            class="flex flex-col gap-6"
        >
            <div class="grid gap-6">
                <div class="grid gap-2">
                    <Label for="email">{{ t('fields.email') }}</Label>
                    <Input
                        id="email"
                        type="email"
                        name="email"
                        required
                        autofocus
                        :tabindex="1"
                        autocomplete="email"
                        :placeholder="t('fields.email_placeholder')"
                    />
                    <InputError :message="errors.email" />
                </div>

                <div class="grid gap-2">
                    <div class="flex items-center justify-between">
                        <Label for="password">{{ t('fields.password') }}</Label>
                        <TextLink
                            v-if="canResetPassword"
                            :href="request()"
                            class="text-sm"
                            :tabindex="5"
                        >
                            {{ t('auth.login.forgot_password') }}
                        </TextLink>
                    </div>
                    <Input
                        id="password"
                        type="password"
                        name="password"
                        required
                        :tabindex="2"
                        autocomplete="current-password"
                        :placeholder="t('fields.password_placeholder')"
                    />
                    <InputError :message="errors.password" />
                </div>

                <div class="flex items-center justify-between">
                    <Label for="remember" class="flex items-center space-x-3">
                        <Checkbox id="remember" name="remember" :tabindex="3" />
                        <span>{{ t('auth.login.remember_me') }}</span>
                    </Label>
                </div>

                <Button
                    type="submit"
                    class="mt-4 w-full"
                    :tabindex="4"
                    :disabled="processing"
                    data-test="login-button"
                >
                    <Spinner v-if="processing" />
                    {{ t('auth.login.button') }}
                </Button>
            </div>

            <div class="relative">
                <div class="absolute inset-0 flex items-center">
                    <Separator />
                </div>
                <div class="relative flex justify-center text-xs uppercase">
                    <span class="bg-background px-2 text-muted-foreground">
                        {{ t('common.or') }}
                    </span>
                </div>
            </div>

            <SocialLoginButton provider="google" class="w-full" />

            <div
                class="text-center text-sm text-muted-foreground"
                v-if="canRegister"
            >
                {{ t('auth.login.no_account') }}
                <TextLink :href="register()" :tabindex="5">{{ t('auth.login.sign_up') }}</TextLink>
            </div>
        </Form>
    </AuthBase>
</template>
