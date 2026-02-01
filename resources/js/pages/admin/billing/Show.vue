<script setup lang="ts">
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
import AdminLayout from '@/layouts/AdminLayout.vue';
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
    status: string;
    onTrial: boolean;
    trialEndsAt: string | null;
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

const { getInitials } = useInitials();

const breadcrumbs = computed(() => [
    { title: 'Admin', href: '/admin' },
    { title: 'Billing', href: '/admin/billing' },
    { title: 'Users', href: '/admin/billing/users' },
    { title: 'User Details' },
]);

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
    return status.replace(/_/g, ' ').replace(/\b\w/g, (l) => l.toUpperCase());
}

function formatDate(dateString: string): string {
    return new Date(dateString).toLocaleDateString('en-US', {
        month: 'short',
        day: 'numeric',
        year: 'numeric',
    });
}
</script>

<template>
    <Head :title="`Billing - ${user.name}`" />

    <AdminLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 p-4 md:p-6">
            <div class="flex items-center gap-4">
                <Button variant="ghost" size="icon" as-child>
                    <Link href="/admin/billing/users">
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
                            User Information
                        </CardTitle>
                    </CardHeader>
                    <CardContent class="space-y-3">
                        <div class="flex justify-between">
                            <span class="text-muted-foreground">User ID</span>
                            <span class="font-mono">{{ user.id }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-muted-foreground">Stripe ID</span>
                            <span class="font-mono text-sm">{{
                                user.stripeId || 'Not connected'
                            }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-muted-foreground">Joined</span>
                            <span>{{ formatDate(user.createdAt) }}</span>
                        </div>
                    </CardContent>
                </Card>

                <!-- Payment Method -->
                <Card>
                    <CardHeader>
                        <CardTitle class="flex items-center gap-2">
                            <CreditCard class="h-5 w-5" />
                            Payment Method
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
                                <span class="text-sm font-medium uppercase">{{
                                    paymentMethod.brand
                                }}</span>
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
                        <p v-else class="text-muted-foreground">
                            No payment method on file.
                        </p>
                    </CardContent>
                </Card>
            </div>

            <!-- Subscriptions -->
            <Card>
                <CardHeader>
                    <CardTitle>Subscriptions</CardTitle>
                    <CardDescription>
                        All subscription history for this user.
                    </CardDescription>
                </CardHeader>
                <CardContent>
                    <Table v-if="subscriptions.length > 0">
                        <TableHeader>
                            <TableRow>
                                <TableHead>Type</TableHead>
                                <TableHead>Status</TableHead>
                                <TableHead>Trial Ends</TableHead>
                                <TableHead>Ends At</TableHead>
                                <TableHead>Created</TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            <TableRow
                                v-for="subscription in subscriptions"
                                :key="subscription.id"
                            >
                                <TableCell class="font-medium">
                                    {{ subscription.type }}
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
                                            (Trial)
                                        </span>
                                        <span
                                            v-if="subscription.onGracePeriod"
                                            class="text-xs text-muted-foreground"
                                        >
                                            (Grace)
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
                        No subscriptions found.
                    </p>
                </CardContent>
            </Card>

            <!-- Invoices -->
            <Card>
                <CardHeader>
                    <CardTitle class="flex items-center gap-2">
                        <FileText class="h-5 w-5" />
                        Invoices
                    </CardTitle>
                    <CardDescription>
                        Payment history and invoice downloads.
                    </CardDescription>
                </CardHeader>
                <CardContent>
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
                        No invoices found.
                    </p>
                </CardContent>
            </Card>
        </div>
    </AdminLayout>
</template>
