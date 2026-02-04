<script setup lang="ts">
import { Head, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = withDefaults(
    defineProps<{
        title?: string;
        description?: string;
    }>(),
    {
        title: undefined,
        description: undefined,
    },
);

const page = usePage();
const name = computed(() => (page.props.name as string) ?? 'Laravel');
const siteDescription = computed(
    () => (page.props.siteDescription as string | undefined) ?? '',
);

const fullTitle = computed(() => {
    if (props.title) {
        return `${props.title} - ${name.value}`;
    }
    return siteDescription.value
        ? `${name.value} - ${siteDescription.value}`
        : name.value;
});

const metaDescription = computed(
    () => props.description ?? siteDescription.value,
);
</script>

<template>
    <Head :title="fullTitle">
        <meta
            v-if="metaDescription"
            head-key="description"
            name="description"
            :content="metaDescription"
        />
        <slot />
    </Head>
</template>
