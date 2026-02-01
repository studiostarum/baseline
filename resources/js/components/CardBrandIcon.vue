<script setup lang="ts">
import { CreditCard } from 'lucide-vue-next';
import { computed } from 'vue';

const props = defineProps<{
    brand: string | null;
}>();

const normalizedBrand = computed(() => props.brand?.toLowerCase() ?? '');

const brandImageUrl = computed(() => {
    const baseUrl = 'https://cdn.jsdelivr.net/npm/payment-icons@1.1.0/min/flat';
    const brandMap: Record<string, string> = {
        visa: `${baseUrl}/visa.svg`,
        mastercard: `${baseUrl}/mastercard.svg`,
        amex: `${baseUrl}/amex.svg`,
        'american express': `${baseUrl}/amex.svg`,
        discover: `${baseUrl}/discover.svg`,
        diners: `${baseUrl}/diners.svg`,
        'diners club': `${baseUrl}/diners.svg`,
        jcb: `${baseUrl}/jcb.svg`,
        unionpay: `${baseUrl}/unionpay.svg`,
    };

    return brandMap[normalizedBrand.value] || null;
});
</script>

<template>
    <img
        v-if="brandImageUrl"
        :src="brandImageUrl"
        :alt="brand || 'Card'"
        class="h-8 w-auto rounded"
    />
    <CreditCard v-else class="h-6 w-6 text-muted-foreground" />
</template>
