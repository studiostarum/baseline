<script setup lang="ts">
import WebsiteLayout from '@/layouts/WebsiteLayout.vue';
import HeroMockupDesktop from '@/components/home/HeroMockupDesktop.vue';
import HeroMockupMobile from '@/components/home/HeroMockupMobile.vue';
import HomeFaq from '@/components/home/HomeFaq.vue';
import HomePricing from '@/components/home/HomePricing.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { dashboard, login, register } from '@/routes';
import { Link } from '@inertiajs/vue3';
import { useTranslations } from '@/composables/useTranslations';

const { t } = useTranslations();

withDefaults(
    defineProps<{
        canRegister: boolean;
        stripeConfigured?: boolean;
        defaultPriceId?: string | null;
        defaultPriceIdAnnual?: string | null;
        hasActiveSubscription?: boolean;
    }>(),
    {
        canRegister: true,
        stripeConfigured: false,
        defaultPriceId: null,
        defaultPriceIdAnnual: null,
        hasActiveSubscription: false,
    },
);
</script>

<template>
    <WebsiteLayout title="Home">
        <template #default>
            <section class="w-full">
                <div
                    class="container mx-auto flex flex-col items-center justify-center gap-6 md:gap-8">
                    <div class="mx-auto flex max-w-3xl flex-col items-center justify-center gap-4">
                        <Badge variant="outline">
                            {{ t('website.hero.badge') }}
                        </Badge>
                        <h1 class="font-bold leading-tight tracking-tight text-4xl sm:text-5xl md:text-6xl">
                            {{ t('website.hero.heading') }}
                        </h1>
                        <p class="max-w-xl text-lg text-muted-foreground md:text-xl">
                            {{ t('website.hero.description') }}
                        </p>
                    </div>
                    <div class="flex flex-col items-center justify-center gap-3 sm:flex-row">
                        <template v-if="$page.props.auth.user">
                            <Button as-child size="lg">
                                <Link :href="dashboard()">
                                    {{ t('navigation.dashboard') }}
                                </Link>
                            </Button>
                        </template>
                        <template v-else>
                            <Button v-if="canRegister" as-child size="lg">
                                <Link :href="register()">
                                    {{ t('website.get_started') }}
                                </Link>
                            </Button>
                            <Button as-child variant="outline" size="lg">
                                <Link :href="login()">
                                    {{ t('website.sign_in') }}
                                </Link>
                            </Button>
                        </template>
                    </div>
                    <HeroMockupDesktop />
                    <HeroMockupMobile />
                </div>
            </section>
        </template>
        <template #below>
            <HomePricing
                :can-register="canRegister"
                :stripe-configured="stripeConfigured ?? false"
                :default-price-id="defaultPriceId"
                :default-price-id-annual="defaultPriceIdAnnual"
                :has-active-subscription="hasActiveSubscription ?? false"
            />
            <div class="h-px w-full shrink-0 bg-border" />
            <HomeFaq />
        </template>
    </WebsiteLayout>
</template>
