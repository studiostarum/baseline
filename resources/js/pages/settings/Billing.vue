<script setup lang="ts">
import { usePage } from '@inertiajs/vue3';
import {
    AlertCircle,
    CreditCard,
    Download,
    ExternalLink,
    FileText,
} from 'lucide-vue-next';
import { computed, ref } from 'vue';
import AppHead from '@/components/shared/AppHead.vue';
import CardBrandIcon from '@/components/app/CardBrandIcon.vue';
import Heading from '@/components/shared/Heading.vue';
import { Badge } from '@/components/shared/ui/badge';
import { Button } from '@/components/shared/ui/button';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/shared/ui/card';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/shared/ui/table';
import { useFormatDate } from '@/composables/useFormatDate';
import { useTranslations } from '@/composables/useTranslations';
import AppLayout from '@/layouts/AppLayout.vue';
import SettingsLayout from '@/layouts/settings/Layout.vue';
import { type BreadcrumbItem } from '@/types';

type Subscription = {
    name: string;
    status: string;
    planType: 'monthly' | 'annual' | null;
    onTrial: boolean;
    trialEndsAt: string | null;
    endsAt: string | null;
    canceledAt: string | null;
    onGracePeriod: boolean;
};

type PaymentMethod = {
    brand: string | null;
    last4: string | null;
    expMonth: number | null;
    expYear: number | null;
};

type Invoice = {
    id: string;
    number: string | null;
    date: string;
    total: number;
    currency?: string;
    status: string;
    planType: 'monthly' | 'annual' | null;
    invoicePdfUrl: string | null;
};

type BillingInterval = 'monthly' | 'annual';

type Props = {
    subscription: Subscription | null;
    paymentMethod: PaymentMethod | null;
    invoices: Invoice[];
    hasStripeCustomer: boolean;
    stripeConfigured?: boolean;
    defaultPriceId?: string | null;
    defaultPriceIdAnnual?: string | null;
    currency?: string;
    currencyLocale?: string;
    error?: string | null;
};

const props = withDefaults(defineProps<Props>(), {
    stripeConfigured: true,
    defaultPriceId: null,
    defaultPriceIdAnnual: null,
    currency: 'USD',
    currencyLocale: 'en',
    error: null,
});

const page = usePage();
const { formatLongDate, formatShortDate } = useFormatDate();
const isLoading = ref(false);
const billingInterval = ref<BillingInterval>('monthly');

const showBillingIntervalToggle = computed(
    () => Boolean(props.defaultPriceId && props.defaultPriceIdAnnual),
);

const selectedPriceId = computed(() =>
    billingInterval.value === 'annual'
        ? props.defaultPriceIdAnnual
        : props.defaultPriceId,
);
const { t } = useTranslations();

const errorMessage = computed(() => {
    return props.error || page.props.flash?.error || null;
});

const breadcrumbItems = computed<BreadcrumbItem[]>(() => [
    {
        title: t('settings.billing.title'),
        href: '/settings/billing',
    },
]);

function getStatusVariant(
    status: string,
): 'default' | 'secondary' | 'destructive' | 'outline' {
    switch (status) {
        case 'active':
        case 'trialing':
            return 'default';
        case 'past_due':
        case 'unpaid':
            return 'destructive';
        case 'canceled':
            return 'secondary';
        default:
            return 'outline';
    }
}

function formatStatus(status: string): string {
    return status.replace(/_/g, ' ').replace(/\b\w/g, (l) => l.toUpperCase());
}

function translatedStatus(status: string): string {
    const key = `settings.billing.status_${status}`;
    const translated = t(key);
    return translated !== key ? translated : formatStatus(status);
}

function submitPortalForm(): void {
    if (!props.stripeConfigured) {
        return;
    }
    isLoading.value = true;
    (
        document.getElementById('billing-portal-form') as HTMLFormElement
    )?.submit();
}

function submitCheckoutForm(): void {
    const priceId = selectedPriceId.value;
    if (!props.stripeConfigured || !priceId) {
        return;
    }
    isLoading.value = true;
    const form = document.getElementById(
        'billing-checkout-form',
    ) as HTMLFormElement;
    const priceInput = form?.querySelector(
        'input[name="price_id"]',
    ) as HTMLInputElement;
    if (priceInput) {
        priceInput.value = priceId;
    }
    form?.submit();
}

function formatCurrency(amount: number, currency?: string): string {
    return new Intl.NumberFormat(props.currencyLocale, {
        style: 'currency',
        currency: currency ?? props.currency,
    }).format(amount / 100);
}

</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbItems">
        <AppHead :title="t('settings.billing.title')" />

        <h1 class="sr-only">{{ t('settings.billing.title') }}</h1>

        <SettingsLayout>
            <form
                id="billing-portal-form"
                method="POST"
                action="/settings/billing/portal"
                class="hidden"
            >
                <input
                    type="hidden"
                    name="_token"
                    :value="(page.props as { csrf_token?: string }).csrf_token"
                />
            </form>
            <form
                id="billing-checkout-form"
                method="POST"
                action="/settings/billing/checkout"
                class="hidden"
            >
                <input
                    type="hidden"
                    name="_token"
                    :value="(page.props as { csrf_token?: string }).csrf_token"
                />
                <input type="hidden" name="price_id" value="" />
            </form>
            <div class="space-y-6">
                <Heading
                    variant="small"
                    :title="t('settings.billing.title')"
                    :description="t('settings.billing.description')"
                />

                <!-- Error Alert -->
                <Card v-if="errorMessage">
                    <CardHeader>
                        <CardTitle class="flex items-center gap-2">
                            <AlertCircle class="mt-0.75 size-4" />
                            {{ t('settings.billing.error_title') }}
                        </CardTitle>
                    </CardHeader>
                    <CardContent>
                        <Card class="p-4 text-sm text-muted-foreground">{{
                            errorMessage
                        }}</Card>
                    </CardContent>
                </Card>

                <!-- Subscription Status -->
                <Card>
                    <CardHeader>
                        <div class="flex items-start justify-between gap-4">
                            <div class="flex-1">
                                <CardTitle class="flex items-center gap-2">
                                    <CreditCard class="h-5 w-5" />
                                    {{ t('settings.billing.subscription') }}
                                </CardTitle>
                                <CardDescription>
                                    {{ t('settings.billing.subscription_description') }}
                                </CardDescription>
                            </div>
                            <Button
                                v-if="!subscription"
                                :disabled="
                                    isLoading ||
                                    !stripeConfigured ||
                                    !selectedPriceId
                                "
                                @click="submitCheckoutForm"
                            >
                                <ExternalLink class="mr-2 h-4 w-4" />
                                {{ isLoading ? t('settings.billing.loading') : t('settings.billing.subscribe_now') }}
                            </Button>
                            <Button
                                v-else
                                :disabled="isLoading || !stripeConfigured"
                                @click="submitPortalForm"
                            >
                                <ExternalLink class="mr-2 h-4 w-4" />
                                {{
                                    isLoading
                                        ? t('settings.billing.loading')
                                        : t('settings.billing.manage_subscription')
                                }}
                            </Button>
                        </div>
                    </CardHeader>
                    <CardContent>
                        <div
                            v-if="!subscription && showBillingIntervalToggle"
                            class="mb-4 flex gap-2"
                        >
                            <Button
                                type="button"
                                :variant="
                                    billingInterval === 'monthly'
                                        ? 'default'
                                        : 'outline'
                                "
                                size="sm"
                                @click="billingInterval = 'monthly'"
                            >
                                {{ t('settings.billing.plan_monthly') }}
                            </Button>
                            <Button
                                type="button"
                                :variant="
                                    billingInterval === 'annual'
                                        ? 'default'
                                        : 'outline'
                                "
                                size="sm"
                                @click="billingInterval = 'annual'"
                            >
                                {{ t('settings.billing.plan_annual') }}
                            </Button>
                        </div>
                        <div v-if="subscription" class="space-y-1">
                            <dl class="flex flex-wrap items-center gap-x-6 gap-y-2">
                                <div class="flex items-center gap-2">
                                    <dt class="text-sm font-medium text-muted-foreground">
                                        {{ t('settings.billing.status') }}
                                    </dt>
                                    <dd class="flex items-center">
                                        <Badge
                                            :variant="
                                                getStatusVariant(subscription.status)
                                            "
                                        >
                                            {{ translatedStatus(subscription.status) }}
                                        </Badge>
                                    </dd>
                                </div>
                                <div
                                    v-if="subscription.planType"
                                    class="flex items-center gap-2"
                                >
                                    <dt class="text-sm font-medium text-muted-foreground">
                                        {{ t('settings.billing.plan') }}
                                    </dt>
                                    <dd class="flex items-center">
                                        <Badge variant="secondary">
                                            {{
                                                subscription.planType === 'annual'
                                                    ? t('settings.billing.plan_annual')
                                                    : t('settings.billing.plan_monthly')
                                            }}
                                        </Badge>
                                    </dd>
                                </div>
                            </dl>

                            <p
                                v-if="
                                    subscription.onTrial &&
                                    subscription.trialEndsAt
                                "
                                class="text-sm text-muted-foreground"
                            >
                                {{ t('settings.billing.trial_ends_on') }}
                                {{ formatLongDate(subscription.trialEndsAt) }}
                            </p>

                            <p
                                v-if="subscription.onGracePeriod"
                                class="text-sm text-muted-foreground"
                            >
                                {{ t('settings.billing.access_until') }}
                                {{ formatLongDate(subscription.endsAt) }}
                            </p>

                            <p
                                v-if="
                                    subscription.canceledAt &&
                                    !subscription.onGracePeriod
                                "
                                class="text-sm text-muted-foreground"
                            >
                                {{ t('settings.billing.canceled_on') }}
                                {{ formatLongDate(subscription.canceledAt) }}
                            </p>
                        </div>

                        <Card v-else class="p-4 text-sm text-muted-foreground">
                            {{ t('settings.billing.no_subscription') }}
                        </Card>
                    </CardContent>
                </Card>

                <!-- Payment Method -->
                <Card>
                    <CardHeader>
                        <CardTitle>{{ t('settings.billing.payment_method') }}</CardTitle>
                        <CardDescription>
                            {{ t('settings.billing.payment_method_description') }}
                        </CardDescription>
                    </CardHeader>
                    <CardContent>
                        <div
                            v-if="paymentMethod && paymentMethod.last4"
                            class="flex items-center justify-between gap-4"
                        >
                            <div class="flex items-center gap-4">
                                <div
                                    class="flex h-12 w-16 items-center justify-center rounded bg-muted"
                                >
                                    <CardBrandIcon
                                        :brand="paymentMethod.brand"
                                    />
                                </div>
                                <div>
                                    <p class="font-medium">
                                        •••• •••• •••• {{ paymentMethod.last4 }}
                                    </p>
                                    <p class="text-sm text-muted-foreground">
                                        {{ t('settings.billing.expires') }} {{ paymentMethod.expMonth }}/{{
                                            paymentMethod.expYear
                                        }}
                                    </p>
                                </div>
                            </div>
                            <Button
                                variant="outline"
                                :disabled="isLoading || !stripeConfigured"
                                @click="submitPortalForm"
                            >
                                {{ isLoading ? t('settings.billing.loading') : t('settings.billing.manage') }}
                            </Button>
                        </div>
                        <Card v-else class="p-4 text-sm text-muted-foreground">
                            {{ t('settings.billing.no_payment_method') }}

                            <Button
                                variant="outline"
                                :disabled="isLoading || !stripeConfigured"
                                @click="submitPortalForm"
                            >
                                {{
                                    isLoading
                                        ? t('settings.billing.loading')
                                        : hasStripeCustomer
                                          ? t('settings.billing.update_payment_method')
                                          : t('settings.billing.add_payment_method')
                                }}
                            </Button>
                        </Card>
                    </CardContent>
                </Card>

                <!-- Payment History -->
                <Card>
                    <CardHeader>
                        <CardTitle class="flex items-center gap-2">
                            <FileText class="h-5 w-5" />
                            {{ t('settings.billing.payment_history') }}
                        </CardTitle>
                        <CardDescription>
                            {{ t('settings.billing.payment_history_description') }}
                        </CardDescription>
                    </CardHeader>
                    <CardContent>
                        <Card
                            v-if="!stripeConfigured"
                            class="p-4 text-sm text-muted-foreground"
                        >
                            {{ t('settings.billing.not_configured') }}
                        </Card>
                        <template v-else>
                            <Table v-if="invoices.length > 0">
                                <TableHeader>
                                    <TableRow>
                                        <TableHead>{{ t('settings.billing.invoice') }}</TableHead>
                                        <TableHead>{{ t('settings.billing.date') }}</TableHead>
                                        <TableHead>{{ t('settings.billing.plan_column') }}</TableHead>
                                        <TableHead>{{ t('settings.billing.amount') }}</TableHead>
                                        <TableHead>{{ t('settings.billing.status_column') }}</TableHead>
                                        <TableHead class="text-right"
                                            >{{ t('settings.billing.actions') }}</TableHead
                                        >
                                    </TableRow>
                                </TableHeader>
                                <TableBody>
                                    <TableRow
                                        v-for="invoice in invoices"
                                        :key="invoice.id"
                                    >
                                        <TableCell class="font-mono text-sm">
                                            {{ invoice.number || invoice.id }}
                                        </TableCell>
                                        <TableCell>
                                            {{ formatShortDate(invoice.date) }}
                                        </TableCell>
                                        <TableCell>
                                            <span
                                                v-if="invoice.planType"
                                                class="text-sm text-muted-foreground"
                                            >
                                                {{
                                                    invoice.planType === 'annual'
                                                        ? t('settings.billing.plan_annual')
                                                        : t('settings.billing.plan_monthly')
                                                }}
                                            </span>
                                            <span v-else class="text-sm text-muted-foreground">—</span>
                                        </TableCell>
                                        <TableCell class="font-medium">
                                            {{ formatCurrency(invoice.total, invoice.currency) }}
                                        </TableCell>
                                        <TableCell>
                                            <Badge
                                                :variant="
                                                    getStatusVariant(
                                                        invoice.status,
                                                    )
                                                "
                                            >
                                                {{
                                                    translatedStatus(invoice.status)
                                                }}
                                            </Badge>
                                        </TableCell>
                                        <TableCell class="text-right">
                                            <Button
                                                v-if="invoice.invoicePdfUrl"
                                                variant="ghost"
                                                size="icon"
                                                as-child
                                            >
                                                <a
                                                    :href="
                                                        invoice.invoicePdfUrl
                                                    "
                                                    target="_blank"
                                                    rel="noopener noreferrer"
                                                    :title="t('settings.billing.download_invoice')"
                                                >
                                                    <Download class="h-4 w-4" />
                                                </a>
                                            </Button>
                                        </TableCell>
                                    </TableRow>
                                </TableBody>
                            </Table>
                            <Card
                                v-else
                                class="p-4 text-sm text-muted-foreground"
                            >
                                {{ t('settings.billing.no_invoices') }}
                            </Card>
                        </template>
                    </CardContent>
                </Card>
            </div>
        </SettingsLayout>
    </AppLayout>
</template>
