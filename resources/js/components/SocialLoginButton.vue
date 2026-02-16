<script setup lang="ts">
import { OhVueIcon, addIcons } from 'oh-vue-icons';
import { SiGoogle } from 'oh-vue-icons/icons';
import { computed } from 'vue';
import { Button } from '@/components/ui/button';
import { useTranslations } from '@/composables/useTranslations';
import { redirect, link } from '@/routes/social';

const { t } = useTranslations();

// Register the Google icon
if (typeof window !== 'undefined') {
    addIcons(SiGoogle);
}

type Props = {
    provider: 'google' | 'github';
    intent?: 'login' | 'link';
    variant?:
        | 'default'
        | 'outline'
        | 'secondary'
        | 'ghost'
        | 'destructive'
        | 'link';
    size?: 'default' | 'sm' | 'lg' | 'icon' | 'icon-sm' | 'icon-lg';
    class?: string;
};

const props = withDefaults(defineProps<Props>(), {
    intent: 'login',
    variant: 'outline',
    size: 'default',
    class: '',
});

const customClass = computed(() => props.class);

const providerNames: Record<string, string> = {
    google: 'Google',
    github: 'GitHub',
};

const providerIcons: Record<string, string> = {
    google: 'si-google',
    github: 'si-github',
};

const handleClick = (event: MouseEvent) => {
    event.preventDefault();
    event.stopPropagation();
    event.stopImmediatePropagation();

    console.log('SocialLoginButton clicked', {
        intent: props.intent,
        provider: props.provider,
    });

    if (props.intent === 'link') {
        // For account linking, use popup window (user is already authenticated)
        const url = link(
            { provider: props.provider },
            { query: { intent: 'link', popup: '1' } },
        ).url;
        console.log('Opening popup with URL:', url);

        const width = 600;
        const height = 700;
        const left = (window.screen.width - width) / 2;
        const top = (window.screen.height - height) / 2;

        const popup = window.open(
            url,
            'oauth-popup',
            `width=${width},height=${height},left=${left},top=${top},toolbar=no,menubar=no,scrollbars=yes,resizable=yes`,
        );

        // Listen for message from popup
        const messageListener = (event: MessageEvent) => {
            // Verify origin for security
            if (event.origin !== window.location.origin) {
                return;
            }

            if (
                event.data.type === 'oauth-success' ||
                event.data.type === 'oauth-error'
            ) {
                window.removeEventListener('message', messageListener);
                popup?.close();

                // Reload page to show updated social accounts
                if (event.data.type === 'oauth-success') {
                    window.location.reload();
                }
            }
        };

        window.addEventListener('message', messageListener);

        // Check if popup was blocked
        if (!popup || popup.closed || typeof popup.closed === 'undefined') {
            alert(
                'Please allow popups for this site to connect your social account.',
            );
            console.error('Popup was blocked or failed to open');
        } else {
            console.log('Popup opened successfully');
        }
    } else {
        // For login/register, use popup window
        const url = redirect(
            { provider: props.provider },
            { query: { intent: props.intent, popup: '1' } },
        ).url;
        console.log('Opening popup with URL:', url);

        const width = 600;
        const height = 700;
        const left = (window.screen.width - width) / 2;
        const top = (window.screen.height - height) / 2;

        const popup = window.open(
            url,
            'oauth-popup',
            `width=${width},height=${height},left=${left},top=${top},toolbar=no,menubar=no,scrollbars=yes,resizable=yes`,
        );

        // Listen for message from popup
        const messageListener = (event: MessageEvent) => {
            // Verify origin for security
            if (event.origin !== window.location.origin) {
                return;
            }

            if (
                event.data.type === 'oauth-success' ||
                event.data.type === 'oauth-error'
            ) {
                window.removeEventListener('message', messageListener);
                popup?.close();

                if (event.data.type === 'oauth-success') {
                    // For login/register, reload the page to establish the session
                    // The page reload will detect the user is now authenticated and redirect
                    window.location.reload();
                } else {
                    // Handle error
                    console.error('OAuth failed:', event.data.error);
                    window.location.reload();
                }
            }
        };

        window.addEventListener('message', messageListener);

        // Check if popup was blocked
        if (!popup || popup.closed || typeof popup.closed === 'undefined') {
            alert(
                'Please allow popups for this site to sign in with your social account.',
            );
            console.error('Popup was blocked or failed to open');
        } else {
            console.log('Popup opened successfully');
        }
    }
};
</script>

<template>
    <Button
        :variant="variant"
        :size="size"
        :class="customClass"
        type="button"
        @click.stop="handleClick"
    >
        <OhVueIcon
            v-if="providerIcons[provider]"
            :name="providerIcons[provider]"
            class="size-4"
        />
        <span v-if="intent === 'link'">{{ t('auth.social.connect') }} {{ providerNames[provider] }}</span>
        <span v-else>{{ t('auth.social.continue_with') }} {{ providerNames[provider] }}</span>
    </Button>
</template>
