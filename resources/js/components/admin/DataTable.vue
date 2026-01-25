<script setup lang="ts">
import { router } from '@inertiajs/vue3';
import { ArrowDown, ArrowUp, ArrowUpDown, Search } from 'lucide-vue-next';
import { ref, watch } from 'vue';
import { Input } from '@/components/ui/input';

export type Column<T> = {
    key: keyof T | string;
    label: string;
    sortable?: boolean;
    class?: string;
};

type Props<T> = {
    columns: Column<T>[];
    data: T[];
    searchable?: boolean;
    searchPlaceholder?: string;
    filters?: Record<string, string>;
    routeName?: string;
};

const props = withDefaults(defineProps<Props<unknown>>(), {
    searchable: true,
    searchPlaceholder: 'Search...',
    filters: () => ({}),
    routeName: undefined,
});

defineSlots<{
    [key: `cell-${string}`]: (props: { item: unknown; value: unknown }) => unknown;
    actions: (props: { item: unknown }) => unknown;
    empty: () => unknown;
}>();

const search = ref(props.filters?.search || '');
const sortField = ref(props.filters?.sort || '');
const sortDirection = ref<'asc' | 'desc'>((props.filters?.direction as 'asc' | 'desc') || 'asc');

const debounceTimeout = ref<ReturnType<typeof setTimeout> | null>(null);

function updateFilters(): void {
    if (!props.routeName) {
        return;
    }

    const params: Record<string, string> = {};

    if (search.value) {
        params.search = search.value;
    }
    if (sortField.value) {
        params.sort = sortField.value;
        params.direction = sortDirection.value;
    }

    router.get(props.routeName, params, {
        preserveState: true,
        preserveScroll: true,
    });
}

function handleSort(column: Column<unknown>): void {
    if (!column.sortable) {
        return;
    }

    if (sortField.value === column.key) {
        sortDirection.value = sortDirection.value === 'asc' ? 'desc' : 'asc';
    } else {
        sortField.value = column.key as string;
        sortDirection.value = 'asc';
    }

    updateFilters();
}

function handleSearch(): void {
    if (debounceTimeout.value) {
        clearTimeout(debounceTimeout.value);
    }

    debounceTimeout.value = setTimeout(() => {
        updateFilters();
    }, 300);
}

watch(search, handleSearch);

function getValue(item: unknown, key: string): unknown {
    const keys = key.split('.');
    let value: unknown = item;

    for (const k of keys) {
        if (value && typeof value === 'object' && k in value) {
            value = (value as Record<string, unknown>)[k];
        } else {
            return undefined;
        }
    }

    return value;
}
</script>

<template>
    <div class="space-y-4">
        <div v-if="searchable" class="flex items-center gap-4">
            <div class="relative flex-1 max-w-sm">
                <Search class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground" />
                <Input
                    v-model="search"
                    type="search"
                    :placeholder="searchPlaceholder"
                    class="pl-9"
                />
            </div>
        </div>

        <div class="rounded-md border">
            <table class="w-full">
                <thead>
                    <tr class="border-b bg-muted/50">
                        <th
                            v-for="column in columns"
                            :key="String(column.key)"
                            class="h-12 px-4 text-left align-middle font-medium text-muted-foreground"
                            :class="column.class"
                        >
                            <button
                                v-if="column.sortable"
                                type="button"
                                class="flex items-center gap-1 hover:text-foreground"
                                @click="handleSort(column)"
                            >
                                {{ column.label }}
                                <ArrowUp
                                    v-if="sortField === column.key && sortDirection === 'asc'"
                                    class="h-4 w-4"
                                />
                                <ArrowDown
                                    v-else-if="sortField === column.key && sortDirection === 'desc'"
                                    class="h-4 w-4"
                                />
                                <ArrowUpDown v-else class="h-4 w-4 opacity-50" />
                            </button>
                            <span v-else>{{ column.label }}</span>
                        </th>
                        <th class="h-12 w-24 px-4 text-right align-middle font-medium text-muted-foreground">
                            Actions
                        </th>
                    </tr>
                </thead>
                <tbody>
                    <tr
                        v-for="(item, index) in data"
                        :key="index"
                        class="border-b transition-colors hover:bg-muted/50"
                    >
                        <td
                            v-for="column in columns"
                            :key="String(column.key)"
                            class="p-4 align-middle"
                            :class="column.class"
                        >
                            <slot :name="`cell-${String(column.key)}`" :item="item" :value="getValue(item, String(column.key))">
                                {{ getValue(item, String(column.key)) }}
                            </slot>
                        </td>
                        <td class="p-4 text-right align-middle">
                            <slot name="actions" :item="item" />
                        </td>
                    </tr>
                    <tr v-if="data.length === 0">
                        <td :colspan="columns.length + 1" class="h-24 text-center">
                            <slot name="empty">
                                <span class="text-muted-foreground">No results found.</span>
                            </slot>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</template>
