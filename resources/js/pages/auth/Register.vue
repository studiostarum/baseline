<script setup lang="ts">
import { Form, router, usePage } from '@inertiajs/vue3';
import { onMounted } from 'vue';
import AppHead from '@/components/AppHead.vue';
import InputError from '@/components/InputError.vue';
import SocialLoginButton from '@/components/SocialLoginButton.vue';
import TextLink from '@/components/TextLink.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Separator } from '@/components/ui/separator';
import { Spinner } from '@/components/ui/spinner';
import { useTranslations } from '@/composables/useTranslations';
import AuthBase from '@/layouts/AuthLayout.vue';
import { login } from '@/routes';
import { store } from '@/routes/register';

const { t } = useTranslations();

defineProps<{
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
        :title="t('auth.register.title')"
        :description="t('auth.register.description')"
    >
        <AppHead :title="t('auth.register.heading')" />

        <Form
            v-bind="store.form()"
            :reset-on-success="['password', 'password_confirmation']"
            v-slot="{ errors, processing }"
            class="flex flex-col gap-6"
        >
            <div class="grid gap-6">
                <div class="grid gap-2">
                    <Label for="name">{{ t('fields.name') }}</Label>
                    <Input
                        id="name"
                        type="text"
                        required
                        autofocus
                        :tabindex="1"
                        autocomplete="name"
                        name="name"
                        :placeholder="t('fields.full_name')"
                    />
                    <InputError :message="errors.name" />
                </div>

                <div class="grid gap-2">
                    <Label for="email">{{ t('fields.email') }}</Label>
                    <Input
                        id="email"
                        type="email"
                        required
                        :tabindex="2"
                        autocomplete="email"
                        name="email"
                        :placeholder="t('fields.email_placeholder')"
                    />
                    <InputError :message="errors.email" />
                </div>

                <div class="grid gap-2">
                    <Label for="password">{{ t('fields.password') }}</Label>
                    <Input
                        id="password"
                        type="password"
                        required
                        :tabindex="3"
                        autocomplete="new-password"
                        name="password"
                        :placeholder="t('fields.password_placeholder')"
                    />
                    <InputError :message="errors.password" />
                </div>

                <div class="grid gap-2">
                    <Label for="password_confirmation">{{ t('fields.confirm_password') }}</Label>
                    <Input
                        id="password_confirmation"
                        type="password"
                        required
                        :tabindex="4"
                        autocomplete="new-password"
                        name="password_confirmation"
                        :placeholder="t('fields.confirm_password_placeholder')"
                    />
                    <InputError :message="errors.password_confirmation" />
                </div>

                <Button
                    type="submit"
                    class="mt-2 w-full"
                    tabindex="5"
                    :disabled="processing"
                    data-test="register-user-button"
                >
                    <Spinner v-if="processing" />
                    {{ t('auth.register.button') }}
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

            <div class="text-center text-sm text-muted-foreground">
                {{ t('auth.register.have_account') }}
                <TextLink
                    :href="login()"
                    class="underline underline-offset-4"
                    :tabindex="6"
                    >{{ t('auth.register.log_in') }}</TextLink
                >
            </div>
        </Form>
    </AuthBase>
</template>
