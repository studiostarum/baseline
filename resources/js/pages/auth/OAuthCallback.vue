<script setup lang="ts">
import { useTranslations } from '@/composables/useTranslations';
import { router } from '@inertiajs/vue3';
import { onMounted } from 'vue';

type Props = {
    success?: boolean;
    error?: string;
    provider?: string;
};

const props = defineProps<Props>();
const { t } = useTranslations();

onMounted(() => {
    // Check if we're in a popup window
    if (window.opener) {
        // Send message to parent window
        if (props.success) {
            window.opener.postMessage(
                {
                    type: 'oauth-success',
                    provider: props.provider,
                },
                window.location.origin,
            );
        } else {
            window.opener.postMessage(
                {
                    type: 'oauth-error',
                    error: props.error || t('auth.oauth.auth_failed'),
                    provider: props.provider,
                },
                window.location.origin,
            );
        }

        // Close the popup after a short delay
        setTimeout(() => {
            window.close();
        }, 500);
    } else {
        // Not in a popup, redirect normally
        if (props.success) {
            // Check URL parameters to determine where to redirect
            const urlParams = new URLSearchParams(window.location.search);
            const intent = urlParams.get('intent');

            if (intent === 'link') {
                router.visit('/settings/profile');
            } else {
                // For login/register, redirect to dashboard or home
                router.visit('/dashboard');
            }
        } else {
            router.visit('/login', {
                data: {
                    error: props.error || t('auth.oauth.auth_failed'),
                },
            });
        }
    }
});
</script>

<template>
    <div class="flex min-h-screen items-center justify-center">
        <div class="text-center">
            <div v-if="success" class="space-y-4">
                <div
                    class="mx-auto flex size-12 items-center justify-center rounded-full bg-green-100 dark:bg-green-900/20"
                >
                    <svg
                        class="size-6 text-green-600 dark:text-green-400"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M5 13l4 4L19 7"
                        />
                    </svg>
                </div>
                <h2 class="text-lg font-semibold">
                    {{ t('auth.oauth.account_connected_success') }}
                </h2>
                <p class="text-muted-foreground">
                    {{ t('auth.oauth.window_will_close') }}
                </p>
            </div>
            <div v-else class="space-y-4">
                <div
                    class="mx-auto flex size-12 items-center justify-center rounded-full bg-red-100 dark:bg-red-900/20"
                >
                    <svg
                        class="size-6 text-red-600 dark:text-red-400"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M6 18L18 6M6 6l12 12"
                        />
                    </svg>
                </div>
                <h2 class="text-lg font-semibold">
                    {{
                        provider
                            ? t('auth.oauth.unable_to_authenticate', {
                                  provider:
                                      provider.charAt(0).toUpperCase() +
                                      provider.slice(1),
                              })
                            : t('auth.oauth.connection_failed')
                    }}
                </h2>
                <p class="text-muted-foreground">
                    {{ error || t('auth.oauth.auth_error_fallback') }}
                </p>
            </div>
        </div>
    </div>
</template>
