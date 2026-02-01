<script setup lang="ts">
import { Head, usePage } from '@inertiajs/vue3';
import {
    AlertCircle,
    CreditCard,
    Download,
    ExternalLink,
    FileText,
} from 'lucide-vue-next';
import { computed, ref } from 'vue';
import CardBrandIcon from '@/components/CardBrandIcon.vue';
import Heading from '@/components/Heading.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';
import { useTranslations } from '@/composables/useTranslations';
import AppLayout from '@/layouts/AppLayout.vue';
import SettingsLayout from '@/layouts/settings/Layout.vue';
import { type BreadcrumbItem } from '@/types';

type Subscription = {
    name: string;
    status: string;
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
    status: string;
    invoicePdfUrl: string | null;
};

type Props = {
    subscription: Subscription | null;
    paymentMethod: PaymentMethod | null;
    invoices: Invoice[];
    hasStripeCustomer: boolean;
    stripeConfigured?: boolean;
    defaultPriceId?: string | null;
    currency?: string;
    currencyLocale?: string;
    error?: string | null;
};

const props = withDefaults(defineProps<Props>(), {
    stripeConfigured: true,
    defaultPriceId: null,
    currency: 'USD',
    currencyLocale: 'en',
    error: null,
});

const page = usePage();
const isLoading = ref(false);
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

function formatDate(dateString: string | null): string {
    if (!dateString) {
        return '';
    }
    return new Date(dateString).toLocaleDateString('en-US', {
        month: 'long',
        day: 'numeric',
        year: 'numeric',
    });
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
    if (!props.stripeConfigured || !props.defaultPriceId) {
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
        priceInput.value = props.defaultPriceId;
    }
    form?.submit();
}

function formatCurrency(amount: number): string {
    return new Intl.NumberFormat(props.currencyLocale, {
        style: 'currency',
        currency: props.currency,
    }).format(amount / 100);
}

function formatShortDate(dateString: string): string {
    return new Date(dateString).toLocaleDateString('en-US', {
        month: 'short',
        day: 'numeric',
        year: 'numeric',
    });
}
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbItems">
        <Head :title="t('settings.billing.title')" />

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
                            Unable to process billing request
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
                                    Subscription
                                </CardTitle>
                                <CardDescription>
                                    Your current subscription status and plan
                                    details.
                                </CardDescription>
                            </div>
                            <Button
                                v-if="!subscription"
                                :disabled="
                                    isLoading ||
                                    !stripeConfigured ||
                                    !defaultPriceId
                                "
                                @click="submitCheckoutForm"
                            >
                                <ExternalLink class="mr-2 h-4 w-4" />
                                {{ isLoading ? 'Loading...' : 'Subscribe Now' }}
                            </Button>
                            <Button
                                v-else
                                :disabled="isLoading || !stripeConfigured"
                                @click="submitPortalForm"
                            >
                                <ExternalLink class="mr-2 h-4 w-4" />
                                {{
                                    isLoading
                                        ? 'Loading...'
                                        : 'Manage Subscription'
                                }}
                            </Button>
                        </div>
                    </CardHeader>
                    <CardContent>
                        <div v-if="subscription" class="space-y-1">
                            <div class="flex items-center gap-2">
                                <span class="font-medium">Status:</span>
                                <Badge
                                    :variant="
                                        getStatusVariant(subscription.status)
                                    "
                                >
                                    {{ formatStatus(subscription.status) }}
                                </Badge>
                            </div>

                            <p
                                v-if="
                                    subscription.onTrial &&
                                    subscription.trialEndsAt
                                "
                                class="text-sm text-muted-foreground"
                            >
                                Trial ends on
                                {{ formatDate(subscription.trialEndsAt) }}
                            </p>

                            <p
                                v-if="subscription.onGracePeriod"
                                class="text-sm text-muted-foreground"
                            >
                                Access until
                                {{ formatDate(subscription.endsAt) }}
                            </p>

                            <p
                                v-if="
                                    subscription.canceledAt &&
                                    !subscription.onGracePeriod
                                "
                                class="text-sm text-muted-foreground"
                            >
                                Canceled on
                                {{ formatDate(subscription.canceledAt) }}
                            </p>
                        </div>

                        <Card v-else class="p-4 text-sm text-muted-foreground">
                            You don't have an active subscription.
                        </Card>
                    </CardContent>
                </Card>

                <!-- Payment Method -->
                <Card>
                    <CardHeader>
                        <CardTitle>Payment Method</CardTitle>
                        <CardDescription>
                            Your default payment method for subscriptions.
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
                                        Expires {{ paymentMethod.expMonth }}/{{
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
                                {{ isLoading ? 'Loading...' : 'Manage' }}
                            </Button>
                        </div>
                        <Card v-else class="p-4 text-sm text-muted-foreground">
                            No payment method on file.

                            <Button
                                variant="outline"
                                :disabled="isLoading || !stripeConfigured"
                                @click="submitPortalForm"
                            >
                                {{
                                    isLoading
                                        ? 'Loading...'
                                        : hasStripeCustomer
                                          ? 'Update Payment Method'
                                          : 'Add Payment Method'
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
                            Payment History
                        </CardTitle>
                        <CardDescription>
                            View and download your invoices.
                        </CardDescription>
                    </CardHeader>
                    <CardContent>
                        <Card
                            v-if="!stripeConfigured"
                            class="p-4 text-sm text-muted-foreground"
                        >
                            Payment processing is not yet configured.
                        </Card>
                        <template v-else>
                            <Table v-if="invoices.length > 0">
                                <TableHeader>
                                    <TableRow>
                                        <TableHead>Invoice</TableHead>
                                        <TableHead>Date</TableHead>
                                        <TableHead>Amount</TableHead>
                                        <TableHead>Status</TableHead>
                                        <TableHead class="text-right"
                                            >Actions</TableHead
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
                                        <TableCell class="font-medium">
                                            {{ formatCurrency(invoice.total) }}
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
                                                    formatStatus(invoice.status)
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
                                                    title="Download invoice"
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
                                No invoices found. Your payment history will
                                appear here once you have active subscriptions.
                            </Card>
                        </template>
                    </CardContent>
                </Card>
            </div>
        </SettingsLayout>
    </AppLayout>
</template>
