<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { ChevronsLeft, ChevronsRight } from 'lucide-vue-next';
import { computed } from 'vue';
import { Button } from '@/components/shared/ui/button';
import { useTranslations } from '@/composables/useTranslations';

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

const props = defineProps<Props>();
const { t } = useTranslations();

const showingText = computed(() =>
    t('pagination.showing')
        .replace('{from}', String(props.from))
        .replace('{to}', String(props.to))
        .replace('{total}', String(props.total)),
);
</script>

<template>
    <div class="flex items-center justify-between px-2">
        <div class="text-sm text-muted-foreground">
            {{ showingText }}
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
                    <Link :href="link.url" preserve-scroll>
                        <span v-html="link.label" />
                    </Link>
                </Button>
                <Button v-else variant="outline" size="icon" disabled>
                    <span v-html="link.label" />
                </Button>
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
