<script setup lang="ts">
import { Form, Link, router, usePage } from '@inertiajs/vue3';
import { Link2, User } from 'lucide-vue-next';
import { computed } from 'vue';
import ProfileController from '@/actions/App/Http/Controllers/Settings/ProfileController';
import AppHead from '@/components/shared/AppHead.vue';
import DeleteUser from '@/components/auth/DeleteUser.vue';
import Heading from '@/components/shared/Heading.vue';
import InputError from '@/components/shared/InputError.vue';
import SocialLoginButton from '@/components/auth/SocialLoginButton.vue';
import SocialProviderIcon from '@/components/auth/SocialProviderIcon.vue';
import { Button } from '@/components/shared/ui/button';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/shared/ui/card';
import { Input } from '@/components/shared/ui/input';
import { Label } from '@/components/shared/ui/label';
import { useFormatDate } from '@/composables/useFormatDate';
import { useTranslations } from '@/composables/useTranslations';
import AppLayout from '@/layouts/AppLayout.vue';
import SettingsLayout from '@/layouts/settings/Layout.vue';
import { edit } from '@/routes/profile';
import { send } from '@/routes/verification';
import { type BreadcrumbItem } from '@/types';

type Props = {
    mustVerifyEmail: boolean;
    status?: string;
    error?: string;
    socialAccounts?: Array<{
        provider: string;
        created_at: string;
    }>;
};

const props = defineProps<Props>();
const { t } = useTranslations();
const { formatDate } = useFormatDate();

const breadcrumbItems = computed<BreadcrumbItem[]>(() => [
    {
        title: t('settings.profile.title'),
        href: edit().url,
    },
]);

const page = usePage();
const user = page.props.auth.user;

const linkedProviders = computed(() => {
    return props.socialAccounts?.map((account) => account.provider) || [];
});

const availableProviders = ['google'];

const unlinkedProviders = computed(() => {
    const linked = linkedProviders.value;
    const unlinked = availableProviders.filter(
        (provider) => !linked.includes(provider),
    );
    return unlinked;
});

function handleUnlink(provider: string): void {
    router.visit(`/settings/profile/social/${provider}/unlink`);
}
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbItems">
        <AppHead :title="t('settings.profile.title')" />

        <h1 class="sr-only">{{ t('settings.profile.title') }}</h1>

        <SettingsLayout>
            <div class="space-y-6">
                <Heading
                    variant="small"
                    :title="t('navigation.profile')"
                    :description="t('settings.description')"
                />

                <div
                    v-if="props.error"
                    class="rounded-lg border border-destructive/50 bg-destructive/10 px-4 py-3 text-sm text-destructive"
                    role="alert"
                >
                    {{ props.error }}
                </div>
                <div
                    v-if="props.status"
                    class="rounded-lg border border-green-500/50 bg-green-500/10 px-4 py-3 text-sm text-green-700 dark:text-green-400"
                    role="status"
                >
                    {{ props.status }}
                </div>

                <!-- Profile Information Card -->
                <Card>
                    <CardHeader>
                        <CardTitle class="flex items-center gap-2">
                            <User class="h-5 w-5" />
                            {{ t('settings.profile.heading') }}
                        </CardTitle>
                        <CardDescription>
                            {{ t('settings.profile.description') }}
                        </CardDescription>
                    </CardHeader>
                    <CardContent>
                        <Form
                            :action="ProfileController.update.url()"
                            method="patch"
                            class="space-y-6"
                            v-slot="{ errors, processing, recentlySuccessful }"
                        >
                            <div class="grid gap-2">
                                <Label for="name">{{ t('fields.name') }}</Label>
                                <Input
                                    id="name"
                                    class="mt-1 block w-full"
                                    name="name"
                                    :default-value="user.name"
                                    required
                                    autocomplete="name"
                                    :placeholder="t('fields.full_name')"
                                />
                                <InputError
                                    class="mt-2"
                                    :message="errors.name"
                                />
                            </div>

                            <div class="grid gap-2">
                                <Label for="email">{{ t('fields.email') }}</Label>
                                <Input
                                    id="email"
                                    type="email"
                                    class="mt-1 block w-full"
                                    name="email"
                                    :default-value="user.email"
                                    required
                                    autocomplete="username"
                                    :placeholder="t('fields.email_placeholder')"
                                />
                                <InputError
                                    class="mt-2"
                                    :message="errors.email"
                                />
                            </div>

                            <div
                                v-if="
                                    mustVerifyEmail && !user.email_verified_at
                                "
                            >
                                <p class="-mt-4 text-sm text-muted-foreground">
                                    {{ t('settings.profile.email_unverified') }}
                                    <Link
                                        :href="send()"
                                        as="button"
                                        class="text-foreground underline decoration-neutral-300 underline-offset-4 transition-colors duration-300 ease-out hover:decoration-current! dark:decoration-neutral-500"
                                    >
                                        {{ t('settings.profile.resend_verification') }}
                                    </Link>
                                </p>

                                <div
                                    v-if="status === 'verification-link-sent'"
                                    class="mt-2 text-sm font-medium text-green-600"
                                >
                                    {{ t('settings.profile.verification_sent') }}
                                </div>
                            </div>

                            <div class="flex items-center gap-4">
                                <Button
                                    :disabled="processing"
                                    data-test="update-profile-button"
                                >
                                    {{ t('common.save') }}
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

                <!-- Connected Accounts Card -->
                <Card>
                    <CardHeader>
                        <CardTitle class="flex items-center gap-2">
                            <Link2 class="h-5 w-5" />
                            {{ t('settings.profile.social_heading') }}
                        </CardTitle>
                        <CardDescription>
                            {{ t('settings.profile.social_description') }}
                        </CardDescription>
                    </CardHeader>
                    <CardContent>
                        <div class="space-y-4">
                            <template
                                v-if="
                                    props.socialAccounts &&
                                    props.socialAccounts.length > 0
                                "
                            >
                                <div
                                    v-for="account in props.socialAccounts"
                                    :key="account.provider"
                                    class="flex items-center justify-between rounded-lg border p-4"
                                >
                                    <div class="flex items-center gap-3">
                                        <SocialProviderIcon
                                            :provider="account.provider"
                                        />
                                        <div>
                                            <p class="font-medium capitalize">
                                                {{ account.provider }}
                                            </p>
                                            <p
                                                class="text-sm text-muted-foreground"
                                            >
                                                {{ t('settings.profile.connected') }}
                                                {{ formatDate(account.created_at) }}
                                            </p>
                                        </div>
                                    </div>
                                    <Button
                                        type="button"
                                        variant="outline"
                                        size="sm"
                                        @click="handleUnlink(account.provider)"
                                    >
                                        {{ t('settings.profile.unlink') }}
                                    </Button>
                                </div>
                            </template>

                            <div
                                v-for="provider in unlinkedProviders"
                                :key="provider"
                                class="flex items-center justify-between rounded-lg border p-4"
                            >
                                <div class="flex items-center gap-3">
                                    <SocialProviderIcon :provider="provider" />
                                    <div>
                                        <p class="font-medium capitalize">
                                            {{ provider }}
                                        </p>
                                        <p
                                            class="text-sm text-muted-foreground"
                                        >
                                            {{ t('settings.profile.not_connected') }}
                                        </p>
                                    </div>
                                </div>
                                <SocialLoginButton
                                    provider="google"
                                    intent="link"
                                    size="sm"
                                />
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <DeleteUser />
            </div>
        </SettingsLayout>
    </AppLayout>
</template>
