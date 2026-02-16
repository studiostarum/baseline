<script lang="ts">
const DEFAULT_FAQ_QUESTIONS: { title: string; answer: string }[] = [
    {
        title: 'Question text goes here',
        answer:
            'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Suspendisse varius enim in eros elementum tristique. Duis cursus, mi quis viverra ornare, eros dolor interdum nulla, ut commodo diam libero vitae erat. Aenean faucibus nibh et justo cursus id rutrum lorem imperdiet. Nunc ut sem vitae risus tristique posuere.',
    },
    {
        title: 'Question text goes here',
        answer:
            'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Suspendisse varius enim in eros elementum tristique. Duis cursus, mi quis viverra ornare, eros dolor interdum nulla, ut commodo diam libero vitae erat. Aenean faucibus nibh et justo cursus id rutrum lorem imperdiet. Nunc ut sem vitae risus tristique posuere.',
    },
    {
        title: 'Question text goes here',
        answer:
            'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Suspendisse varius enim in eros elementum tristique. Duis cursus, mi quis viverra ornare, eros dolor interdum nulla, ut commodo diam libero vitae erat. Aenean faucibus nibh et justo cursus id rutrum lorem imperdiet. Nunc ut sem vitae risus tristique posuere.',
    },
    {
        title: 'Question text goes here',
        answer:
            'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Suspendisse varius enim in eros elementum tristique. Duis cursus, mi quis viverra ornare, eros dolor interdum nulla, ut commodo diam libero vitae erat. Aenean faucibus nibh et justo cursus id rutrum lorem imperdiet. Nunc ut sem vitae risus tristique posuere.',
    },
    {
        title: 'Question text goes here',
        answer:
            'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Suspendisse varius enim in eros elementum tristique. Duis cursus, mi quis viverra ornare, eros dolor interdum nulla, ut commodo diam libero vitae erat. Aenean faucibus nibh et justo cursus id rutrum lorem imperdiet. Nunc ut sem vitae risus tristique posuere.',
    },
];
</script>

<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { computed } from 'vue';
import {
    Accordion,
    AccordionContent,
    AccordionItem,
    AccordionTrigger,
} from '@/components/ui/accordion';
import { Button } from '@/components/ui/button';
import { useTranslations } from '@/composables/useTranslations';
import { contact } from '@/routes';

export type FaqQuestion = {
    title: string;
    answer: string;
};

type Props = {
    heading?: string;
    description?: string;
    questions?: FaqQuestion[];
};

const { t } = useTranslations();

const props = withDefaults(defineProps<Props>(), {
    heading: undefined,
    description: undefined,
    questions: () => DEFAULT_FAQ_QUESTIONS,
});

const leftHeading = computed(() => props.heading ?? t('website.faq.heading'));
const leftDescription = computed(
    () => props.description ?? t('website.faq.description'),
);
</script>

<template>
    <section id="faq" class="w-full bg-muted/30 py-16 md:py-20 lg:py-28">
        <div
            class="container flex flex-col gap-8 md:gap-12 lg:gap-20"
        >
            <div class="grid grid-cols-1 gap-y-12 md:grid-cols-2 md:gap-x-12 lg:grid-cols-[.75fr,1fr] lg:gap-x-20 lg:items-start">
                <div class="flex flex-col gap-4">
                    <h2
                        class="text-3xl font-bold tracking-tight sm:text-4xl md:text-5xl"
                    >
                        {{ leftHeading }}
                    </h2>
                    <p
                        class="max-w-xl text-base leading-relaxed text-muted-foreground md:text-lg"
                    >
                        {{ leftDescription }}
                    </p>
                    <Button as-child variant="outline" class="w-fit">
                        <Link :href="contact()">
                            {{ t('website.faq.contact_button') }}
                        </Link>
                    </Button>
                </div>
                <Accordion
                    type="multiple"
                    class="grid w-full items-start gap-4"
                >
                    <AccordionItem
                        v-for="(question, index) in props.questions"
                        :key="index"
                        :value="`item-${index}`"
                        class="rounded-xl border border-border bg-card px-5 shadow-sm last:border-b md:px-6"
                    >
                        <AccordionTrigger>
                            {{ question.title }}
                        </AccordionTrigger>
                        <AccordionContent class="pb-4 md:pb-6">
                            {{ question.answer }}
                        </AccordionContent>
                    </AccordionItem>
                </Accordion>
            </div>
        </div>
    </section>
</template>
