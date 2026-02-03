<script setup lang="ts">
import CardBrandIcon from '@/components/CardBrandIcon.vue';
import { Avatar, AvatarFallback } from '@/components/ui/avatar';
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
import { useInitials } from '@/composables/useInitials';
import { useTranslations } from '@/composables/useTranslations';
import AdminLayout from '@/layouts/AdminLayout.vue';
import { users as billingUsers } from '@/routes/admin/billing';
import { Head, Link } from '@inertiajs/vue3';
import {
    ArrowLeft,
    CreditCard,
    Download,
    FileText,
    User as UserIcon,
} from 'lucide-vue-next';
import { computed } from 'vue';

type Subscription = {
    id: number;
    type: string;
    typeDisplayName?: string;
    status: string;
    onTrial: boolean;
    trialEndsAt: string | null;
    renewsAt: string | null;
    endsAt: string | null;
    onGracePeriod: boolean;
    createdAt: string;
};

type Invoice = {
    id: string;
    number: string | null;
    date: string;
    total: string;
    status: string;
    invoicePdfUrl: string | null;
};

type PaymentMethod = {
    brand: string | null;
    last4: string | null;
    expMonth: number | null;
    expYear: number | null;
};

type User = {
    id: number;
    name: string;
    email: string;
    stripeId: string | null;
    createdAt: string;
};

type Props = {
    user: User;
    subscriptions: Subscription[];
    invoices: Invoice[];
    paymentMethod: PaymentMethod | null;
};

defineProps<Props>();

const { t } = useTranslations();
const { getInitials } = useInitials();

const breadcrumbs = computed(() => [
    { title: t('admin.breadcrumb'), href: '/admin' },
    { title: t('admin.navigation.billing'), href: '/admin/billing' },
    { title: t('admin.billing.users.title'), href: billingUsers.url() },
    { title: t('admin.billing.show.breadcrumb_details') },
]);

const statusKeyMap: Record<string, string> = {
    active: 'admin.billing.status_active',
    trialing: 'admin.billing.status_trialing',
    canceled: 'admin.billing.status_canceled',
    past_due: 'admin.billing.status_past_due',
    unpaid: 'admin.billing.status_unpaid',
    paid: 'admin.billing.status_paid',
    open: 'admin.billing.status_open',
    void: 'admin.billing.status_void',
};

function getStatusVariant(
    status: string,
): 'default' | 'secondary' | 'destructive' | 'outline' {
    switch (status) {
        case 'active':
        case 'trialing':
        case 'paid':
            return 'default';
        case 'past_due':
        case 'unpaid':
        case 'open':
            return 'destructive';
        case 'canceled':
        case 'void':
            return 'secondary';
        default:
            return 'outline';
    }
}

function formatStatus(status: string): string {
    const key = statusKeyMap[status];
    return key ? t(key) : status.replace(/_/g, ' ').replace(/\b\w/g, (l) => l.toUpperCase());
}

function formatDate(dateString: string): string {
    return new Date(dateString).toLocaleDateString(undefined, {
        month: 'short',
        day: 'numeric',
        year: 'numeric',
    });
}
</script>

<template>
    <Head :title="t('admin.billing.show.head_title').replace(':name', user.name)" />

    <AdminLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 p-4 md:p-6">
            <div class="flex items-center gap-4">
                <Button variant="ghost" size="icon" as-child>
                    <Link :href="billingUsers.url()">
                        <ArrowLeft class="h-4 w-4" />
                    </Link>
                </Button>
                <div class="flex items-center gap-4">
                    <Avatar class="h-12 w-12">
                        <AvatarFallback>{{
                            getInitials(user.name)
                        }}</AvatarFallback>
                    </Avatar>
                    <div>
                        <h1 class="text-2xl font-bold tracking-tight">
                            {{ user.name }}
                        </h1>
                        <p class="text-muted-foreground">{{ user.email }}</p>
                    </div>
                </div>
            </div>

            <div class="grid gap-6 md:grid-cols-2">
                <!-- User Info -->
                <Card>
                    <CardHeader>
                        <CardTitle class="flex items-center gap-2">
                            <UserIcon class="h-5 w-5" />
                            {{ t('admin.billing.show.user_information') }}
                        </CardTitle>
                    </CardHeader>
                    <CardContent class="space-y-3">
                        <div class="flex justify-between">
                            <span class="text-muted-foreground">{{ t('admin.billing.show.user_id') }}</span>
                            <span class="font-mono">{{ user.id }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-muted-foreground">{{ t('admin.billing.show.stripe_id') }}</span>
                            <span class="font-mono text-sm">{{
                                user.stripeId || t('admin.billing.show.not_connected')
                            }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-muted-foreground">{{ t('admin.billing.show.joined') }}</span>
                            <span>{{ formatDate(user.createdAt) }}</span>
                        </div>
                    </CardContent>
                </Card>

                <!-- Payment Method -->
                <Card>
                    <CardHeader>
                        <CardTitle class="flex items-center gap-2">
                            <CreditCard class="h-5 w-5" />
                            {{ t('admin.billing.show.payment_method') }}
                        </CardTitle>
                    </CardHeader>
                    <CardContent>
                        <div
                            v-if="paymentMethod && paymentMethod.last4"
                            class="flex items-center gap-4"
                        >
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
                                    {{
                                        t('admin.billing.show.expires')
                                            .replace(':month', String(paymentMethod.expMonth))
                                            .replace(':year', String(paymentMethod.expYear))
                                    }}
                                </p>
                            </div>
                        </div>
                        <p v-else class="text-muted-foreground">
                            {{ t('admin.billing.show.no_payment_method') }}
                        </p>
                    </CardContent>
                </Card>
            </div>

            <!-- Subscriptions -->
            <Card>
                <CardHeader>
                    <CardTitle>{{ t('admin.billing.show.subscriptions') }}</CardTitle>
                    <CardDescription>
                        {{ t('admin.billing.show.subscriptions_description') }}
                    </CardDescription>
                </CardHeader>
                <CardContent>
                    <Table v-if="subscriptions.length > 0">
                        <TableHeader>
                            <TableRow>
                                <TableHead>{{ t('admin.billing.show.table_type') }}</TableHead>
                                <TableHead>{{ t('admin.billing.show.table_status') }}</TableHead>
                                <TableHead>{{ t('admin.billing.show.table_trial_ends') }}</TableHead>
                                <TableHead>{{ t('admin.billing.show.table_renews_at') }}</TableHead>
                                <TableHead>{{ t('admin.billing.show.table_ends_at') }}</TableHead>
                                <TableHead>{{ t('admin.billing.show.table_created') }}</TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            <TableRow
                                v-for="subscription in subscriptions"
                                :key="subscription.id"
                            >
                                <TableCell class="font-medium">
                                    {{ subscription.typeDisplayName ?? subscription.type }}
                                </TableCell>
                                <TableCell>
                                    <div class="flex items-center gap-2">
                                        <Badge
                                            :variant="
                                                getStatusVariant(
                                                    subscription.status,
                                                )
                                            "
                                        >
                                            {{
                                                formatStatus(
                                                    subscription.status,
                                                )
                                            }}
                                        </Badge>
                                        <span
                                            v-if="subscription.onTrial"
                                            class="text-xs text-muted-foreground"
                                        >
                                            ({{ t('admin.billing.users.trial') }})
                                        </span>
                                        <span
                                            v-if="subscription.onGracePeriod"
                                            class="text-xs text-muted-foreground"
                                        >
                                            ({{ t('admin.billing.show.grace') }})
                                        </span>
                                    </div>
                                </TableCell>
                                <TableCell>
                                    {{
                                        subscription.trialEndsAt
                                            ? formatDate(
                                                  subscription.trialEndsAt,
                                              )
                                            : '-'
                                    }}
                                </TableCell>
                                <TableCell>
                                    {{
                                        subscription.renewsAt
                                            ? formatDate(subscription.renewsAt)
                                            : '-'
                                    }}
                                </TableCell>
                                <TableCell>
                                    {{
                                        subscription.endsAt
                                            ? formatDate(subscription.endsAt)
                                            : '-'
                                    }}
                                </TableCell>
                                <TableCell>
                                    {{ formatDate(subscription.createdAt) }}
                                </TableCell>
                            </TableRow>
                        </TableBody>
                    </Table>
                    <p v-else class="text-muted-foreground">
                        {{ t('admin.billing.show.no_subscriptions') }}
                    </p>
                </CardContent>
            </Card>

            <!-- Invoices -->
            <Card>
                <CardHeader>
                    <CardTitle class="flex items-center gap-2">
                        <FileText class="h-5 w-5" />
                        {{ t('admin.billing.show.invoices') }}
                    </CardTitle>
                    <CardDescription>
                        {{ t('admin.billing.show.invoices_description') }}
                    </CardDescription>
                </CardHeader>
                <CardContent>
                    <Table v-if="invoices.length > 0">
                        <TableHeader>
                            <TableRow>
                                <TableHead>{{ t('admin.billing.show.table_invoice') }}</TableHead>
                                <TableHead>{{ t('admin.billing.show.table_date') }}</TableHead>
                                <TableHead>{{ t('admin.billing.show.table_amount') }}</TableHead>
                                <TableHead>{{ t('admin.billing.show.table_status') }}</TableHead>
                                <TableHead class="text-right">
                                    {{ t('admin.billing.show.table_actions') }}
                                </TableHead>
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
                                    {{ formatDate(invoice.date) }}
                                </TableCell>
                                <TableCell class="font-medium">
                                    {{ invoice.total }}
                                </TableCell>
                                <TableCell>
                                    <Badge
                                        :variant="
                                            getStatusVariant(invoice.status)
                                        "
                                    >
                                        {{ formatStatus(invoice.status) }}
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
                                            :href="invoice.invoicePdfUrl"
                                            target="_blank"
                                            rel="noopener noreferrer"
                                        >
                                            <Download class="h-4 w-4" />
                                        </a>
                                    </Button>
                                </TableCell>
                            </TableRow>
                        </TableBody>
                    </Table>
                    <p v-else class="text-muted-foreground">
                        {{ t('admin.billing.show.no_invoices') }}
                    </p>
                </CardContent>
            </Card>
        </div>
    </AdminLayout>
</template>
