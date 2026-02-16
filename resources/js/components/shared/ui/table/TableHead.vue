<script setup lang="ts">
import { cn } from '@/lib/utils';
import { type HTMLAttributes, computed } from 'vue';

const props = defineProps<{
    class?: HTMLAttributes['class'];
}>();

const delegatedProps = computed(() => {
    const { class: _, ...delegated } = props;

    return delegated;
});
</script>

<template>
    <th
        :class="
            cn(
                'h-10 px-2 text-left align-middle font-medium text-muted-foreground [&:has([role=checkbox])]:pr-0 [&>[role=checkbox]]:translate-y-0.5',
                props.class,
            )
        "
        v-bind="delegatedProps"
    >
        <slot />
    </th>
</template>
