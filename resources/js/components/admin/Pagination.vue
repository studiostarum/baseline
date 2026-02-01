<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Link } from '@inertiajs/vue3';
import { ChevronsLeft, ChevronsRight } from 'lucide-vue-next';

type PaginationLink = {
    url: string | null;
    label: string;
    active: boolean;
};

type Props = {
    links: PaginationLink[];
    from: number;
    to: number;
    total: number;
};

defineProps<Props>();
</script>

<template>
    <div class="flex items-center justify-between px-2">
        <div class="text-sm text-muted-foreground">
            Showing {{ from }} to {{ to }} of {{ total }} results
        </div>
        <div class="flex items-center space-x-2">
            <Button v-if="links[0]?.url" variant="outline" size="icon" as-child>
                <Link :href="links[0].url" preserve-scroll>
                    <ChevronsLeft class="h-4 w-4" />
                </Link>
            </Button>
            <Button v-else variant="outline" size="icon" disabled>
                <ChevronsLeft class="h-4 w-4" />
            </Button>

            <template v-for="(link, index) in links.slice(1, -1)" :key="index">
                <Button
                    v-if="link.url"
                    :variant="link.active ? 'default' : 'outline'"
                    size="icon"
                    as-child
                >
                    <!-- eslint-disable-next-line vue/no-v-text-v-html-on-component -->
                    <Link
                        :href="link.url"
                        preserve-scroll
                        v-html="link.label"
                    />
                </Button>
                <!-- eslint-disable-next-line vue/no-v-text-v-html-on-component -->
                <Button
                    v-else
                    variant="outline"
                    size="icon"
                    disabled
                    v-html="link.label"
                />
            </template>

            <Button
                v-if="links[links.length - 1]?.url"
                variant="outline"
                size="icon"
                as-child
            >
                <Link :href="links[links.length - 1].url" preserve-scroll>
                    <ChevronsRight class="h-4 w-4" />
                </Link>
            </Button>
            <Button v-else variant="outline" size="icon" disabled>
                <ChevronsRight class="h-4 w-4" />
            </Button>
        </div>
    </div>
</template>
