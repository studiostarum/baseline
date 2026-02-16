<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import { ArrowRight, Check, Info } from 'lucide-vue-next';
import { computed, ref } from 'vue';
import { Badge } from '@/components/shared/ui/badge';
import { Button } from '@/components/shared/ui/button';
import {
    Card,
    CardContent,
    CardDescription,
    CardFooter,
    CardHeader,
    CardTitle,
} from '@/components/shared/ui/card';
import {
    Tooltip,
    TooltipContent,
    TooltipProvider,
    TooltipTrigger,
} from '@/components/shared/ui/tooltip';
import { useTranslations } from '@/composables/useTranslations';
import { dashboard, register } from '@/routes';

type BillingInterval = 'monthly' | 'annual';

type Props = {
    stripeConfigured?: boolean;
    defaultPriceId?: string | null;
    defaultPriceIdAnnual?: string | null;
    hasActiveSubscription?: boolean;
    canRegister?: boolean;
};

const props = withDefaults(defineProps<Props>(), {
    stripeConfigured: false,
    defaultPriceId: null,
    defaultPriceIdAnnual: null,
    hasActiveSubscription: false,
    canRegister: true,
});

const { t } = useTranslations();
const page = usePage();
const billingInterval = ref<BillingInterval>('monthly');

const csrfToken = computed(() => (page.props as { csrf_token?: string }).csrf_token ?? '');
const billingEnabled = computed(() => page.props.features?.billing !== false);

const showBillingIntervalToggle = computed(
    () => Boolean(props.defaultPriceId && props.defaultPriceIdAnnual),
);

const selectedPriceId = computed(() =>
    billingInterval.value === 'annual'
        ? props.defaultPriceIdAnnual
        : props.defaultPriceId,
);

const canCheckout = computed(
    () =>
        props.stripeConfigured &&
        selectedPriceId.value &&
        billingEnabled.value &&
        !props.hasActiveSubscription,
);

const proPrice = computed(() =>
    billingInterval.value === 'annual'
        ? t('website.pricing.pro.price_annual')
        : t('website.pricing.pro.price'),
);

const proPeriod = computed(() =>
    billingInterval.value === 'annual'
        ? t('website.pricing.pro.period_annual')
        : t('website.pricing.pro.period'),
);

const freePlanFeatures = computed(() => [
    t('website.pricing.free.feature_1'),
    t('website.pricing.free.feature_2'),
    t('website.pricing.free.feature_3'),
    t('website.pricing.free.feature_4'),
    t('website.pricing.free.feature_5'),
]);

const proPlanFeatures = computed(() => [
    t('website.pricing.pro.feature_1'),
    t('website.pricing.pro.feature_2'),
    t('website.pricing.pro.feature_3'),
    t('website.pricing.pro.feature_4'),
    t('website.pricing.pro.feature_5'),
    t('website.pricing.pro.feature_6'),
    t('website.pricing.pro.feature_7'),
]);

function submitCheckout(e: Event) {
    const form = e.target as HTMLFormElement;
    const priceInput = form.querySelector<HTMLInputElement>('input[name="price_id"]');
    if (priceInput && selectedPriceId.value) {
        priceInput.value = selectedPriceId.value;
    }
    form.submit();
}
</script>

<template>
    <section id="pricing" class="w-full bg-muted/30 py-16 md:py-20 lg:py-28">
        <div class="container flex flex-col items-center justify-center gap-8 md:gap-12 lg:gap-20">
            <!-- Section Header -->
            <div class="flex flex-col items-center justify-center gap-4 text-center">
                <div class="flex flex-col items-center justify-center gap-4">
                    <Badge variant="outline">
                        {{ t('website.pricing.badge') }}
                    </Badge>
                    <h2 class="text-3xl font-bold tracking-tight sm:text-4xl md:text-5xl">
                        {{ t('website.pricing.title') }}
                    </h2>
                </div>
                <p
                    class="mx-auto max-w-3xl text-base leading-relaxed text-muted-foreground md:text-lg">
                    {{ t('website.pricing.subtitle') }}
                </p>
            </div>

            <!-- Pricing Cards -->
            <div class="mx-auto grid w-full max-w-6xl gap-4 md:grid-cols-2">
                <!-- Free Plan -->
                <Card class="flex flex-col">
                    <CardHeader class="flex flex-col gap-4 pb-2 text-center">
                        <CardTitle class="text-xl">
                            {{ t('website.pricing.free.name') }}
                        </CardTitle>
                        <div class="flex items-baseline justify-center gap-1">
                            <span class="text-5xl font-black">{{ t('website.pricing.free.price') }}</span>
                            <span class="text-lg font-medium text-muted-foreground">
                                {{ t('website.pricing.free.period') }}
                            </span>
                        </div>
                        <CardDescription>
                            {{ t('website.pricing.free.description') }}
                        </CardDescription>
                    </CardHeader>
                    <CardContent class="flex-1">
                        <ul class="flex flex-col gap-4">
                            <li
                                v-for="(feature, i) in freePlanFeatures"
                                :key="i"
                                class="flex items-start gap-3">
                                <div
                                    class="mt-0.5 flex size-6 shrink-0 items-center justify-center rounded-full border border-primary/20 bg-primary/10 shadow-sm">
                                    <Check class="size-3.5 text-primary" />
                                </div>
                                <span class="text-sm leading-relaxed">{{ feature }}</span>
                            </li>
                        </ul>
                    </CardContent>
                    <CardFooter>
                        <Link
                            v-if="$page.props.auth.user"
                            :href="dashboard()"
                            class="block w-full">
                            <Button
                                variant="outline"
                                class="w-full py-3 text-base font-semibold shadow-lg"
                            >
                                {{ t('website.pricing.free.cta') }}
                                <ArrowRight class="ml-2 size-4" />
                            </Button>
                        </Link>
                        <Link
                            v-else-if="canRegister"
                            :href="register()"
                            class="block w-full">
                            <Button
                                variant="outline"
                                class="w-full py-3 text-base font-semibold shadow-lg"
                            >
                                {{ t('website.pricing.free.cta') }}
                                <ArrowRight class="ml-2 size-4" />
                            </Button>
                        </Link>
                    </CardFooter>
                </Card>

                <!-- Pro Plan (Popular) -->
                <Card
                    class="relative flex flex-col overflow-hidden border-2 border-primary bg-primary text-primary-foreground"
                >
                    <div class="absolute right-4 top-4 size-20 rounded-full bg-primary-foreground/10 blur-xl" />
                    <div class="absolute bottom-4 left-4 size-16 rounded-full bg-primary-foreground/10 blur-lg" />
                    <div class="relative z-10 flex flex-col gap-6">
                        <CardHeader class="flex flex-col gap-4 pb-2 text-center">
                            <Badge
                                variant="secondary"
                                class="mx-auto border-secondary bg-secondary text-secondary-foreground"
                            >
                                {{ t('website.pricing.pro.badge') }}
                            </Badge>
                            <CardTitle class="text-xl text-primary-foreground">
                                {{ t('website.pricing.pro.name') }}
                            </CardTitle>
                            <div
                                v-if="showBillingIntervalToggle"
                                class="flex justify-center gap-2"
                            >
                                <button
                                    type="button"
                                    class="rounded-md px-3 py-1.5 text-sm font-medium transition-colors"
                                    :class="
                                        billingInterval === 'monthly'
                                            ? 'bg-primary-foreground/20 text-primary-foreground'
                                            : 'text-primary-foreground/70 hover:text-primary-foreground'
                                    "
                                    @click="billingInterval = 'monthly'"
                                >
                                    {{ t('settings.billing.plan_monthly') }}
                                </button>
                                <button
                                    type="button"
                                    class="rounded-md px-3 py-1.5 text-sm font-medium transition-colors"
                                    :class="
                                        billingInterval === 'annual'
                                            ? 'bg-primary-foreground/20 text-primary-foreground'
                                            : 'text-primary-foreground/70 hover:text-primary-foreground'
                                    "
                                    @click="billingInterval = 'annual'"
                                >
                                    {{ t('settings.billing.plan_annual') }}
                                </button>
                            </div>
                            <div class="flex items-baseline justify-center gap-1.5">
                                <span class="text-5xl font-black text-primary-foreground">
                                    {{ proPrice }}
                                </span>
                                <span class="text-lg font-medium text-primary-foreground/80">
                                    {{ proPeriod }}
                                </span>
                                <TooltipProvider v-if="billingEnabled">
                                    <Tooltip>
                                        <TooltipTrigger as-child>
                                            <Info class="size-4 cursor-help text-primary-foreground/70 hover:text-primary-foreground" />
                                        </TooltipTrigger>
                                        <TooltipContent side="bottom" class="max-w-64">
                                            {{ t('website.pricing.pro.trial_info') }}
                                        </TooltipContent>
                                    </Tooltip>
                                </TooltipProvider>
                            </div>
                            <CardDescription class="text-primary-foreground/70">
                                {{ t('website.pricing.pro.description') }}
                            </CardDescription>
                        </CardHeader>
                        <CardContent class="flex-1">
                            <ul class="flex flex-col gap-4">
                                <li
                                    v-for="(feature, i) in proPlanFeatures"
                                    :key="i"
                                    class="flex items-start gap-3">
                                    <div
                                        class="mt-0.5 flex size-6 shrink-0 items-center justify-center rounded-full border border-primary-foreground/30 bg-primary-foreground/20 shadow-sm">
                                        <Check class="size-3.5 text-primary-foreground" />
                                    </div>
                                    <span class="text-sm leading-relaxed text-primary-foreground/90">
                                        {{ feature }}
                                    </span>
                                </li>
                            </ul>
                        </CardContent>
                        <CardFooter>
                            <template v-if="$page.props.auth.user">
                                <form
                                    v-if="canCheckout"
                                    method="POST"
                                    action="/settings/billing/checkout"
                                    class="w-full"
                                    @submit="submitCheckout"
                                >
                                    <input type="hidden" name="_token" :value="csrfToken" />
                                    <input type="hidden" name="price_id" :value="selectedPriceId ?? ''" />
                                    <Button
                                        type="submit"
                                        variant="secondary"
                                        class="w-full py-3 text-base font-semibold shadow-lg"
                                    >
                                        {{ t('website.pricing.pro.cta') }}
                                        <ArrowRight class="ml-2 size-4" />
                                    </Button>
                                </form>
                                <Link
                                    v-else
                                    :href="dashboard()"
                                    class="block w-full"
                                >
                                    <Button
                                        variant="secondary"
                                        :disabled="!stripeConfigured"
                                        class="w-full py-3 text-base font-semibold shadow-lg"
                                    >
                                        {{ hasActiveSubscription ? t('navigation.dashboard') : t('website.pricing.pro.cta') }}
                                        <ArrowRight class="ml-2 size-4" />
                                    </Button>
                                </Link>
                            </template>
                            <Link
                                v-else-if="canRegister"
                                :href="register()"
                                class="block w-full"
                            >
                                <Button
                                    variant="secondary"
                                    class="w-full py-3 text-base font-semibold shadow-lg"
                                >
                                    {{ t('website.pricing.pro.cta') }}
                                    <ArrowRight class="ml-2 size-4" />
                                </Button>
                            </Link>
                        </CardFooter>
                    </div>
                </Card>
            </div>

            <!-- Bottom Section -->
            <div class="flex max-w-2xl flex-col items-center justify-center gap-3 text-center">
                <p class="text-lg leading-relaxed text-muted-foreground">
                    {{ t('website.pricing.bottom_text') }}
                </p>
                <a :href="`mailto:${t('website.pricing.contact_email')}`">
                    <Button variant="outline">
                        {{ t('website.pricing.contact_link') }}
                    </Button>
                </a>
            </div>
        </div>
    </section>
</template>
