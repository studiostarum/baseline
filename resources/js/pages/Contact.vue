<script setup lang="ts">
import WebsiteLayout from '@/layouts/WebsiteLayout.vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { Spinner } from '@/components/ui/spinner';
import { store } from '@/actions/App/Http/Controllers/ContactController';
import { useTranslations } from '@/composables/useTranslations';
import { Form, usePage } from '@inertiajs/vue3';
import { Mail, MapPin, Phone } from 'lucide-vue-next';
import { ref } from 'vue';

const { t } = useTranslations();
const page = usePage();
const status = (page.props.status as string | undefined) ?? null;
const error = (page.props.error as string | undefined) ?? null;
const acceptTerms = ref<boolean | 'indeterminate'>(false);
</script>

<template>
    <WebsiteLayout :title="t('website.contact.page_title')">
        <section id="contact" class="w-full container mx-auto py-16 text-left md:py-24 lg:py-28">
            <div
                class="grid grid-cols-1 items-start gap-y-12 md:grid-flow-row md:grid-cols-2 md:gap-x-12 lg:grid-flow-col lg:gap-x-20 lg:gap-y-16">
                <div>
                    <p class="mb-3 font-semibold md:mb-4">
                        {{ t('website.contact.tagline') }}
                    </p>
                    <div class="mb-6 md:mb-8">
                        <h2 class="mb-5 font-bold text-4xl tracking-tight md:mb-6 md:text-5xl lg:text-6xl">
                            {{ t('website.contact.heading') }}
                        </h2>
                        <p class="text-muted-foreground md:text-base">
                            {{ t('website.contact.description') }}
                        </p>
                    </div>

                    <div class="grid grid-cols-1 gap-4 py-2">
                        <div class="flex items-center gap-4">
                            <Mail class="size-6 shrink-0 text-muted-foreground" aria-hidden />
                            <p class="text-muted-foreground">
                                {{ t('website.contact.email') }}
                            </p>
                        </div>
                        <div class="flex items-center gap-4">
                            <Phone class="size-6 shrink-0 text-muted-foreground" aria-hidden />
                            <p class="text-muted-foreground">
                                {{ t('website.contact.phone') }}
                            </p>
                        </div>
                        <div class="flex items-center gap-4">
                            <MapPin class="size-6 shrink-0 text-muted-foreground" aria-hidden />
                            <p class="text-muted-foreground">
                                {{ t('website.contact.address') }}
                            </p>
                        </div>
                    </div>
                </div>

                <Form :action="store()" v-slot="{ errors, processing }"
                    class="grid grid-cols-1 grid-rows-[auto_auto] gap-6">
                    <div v-if="error"
                        class="rounded-md bg-red-50 p-4 text-sm text-red-800 dark:bg-red-950 dark:text-red-200">
                        {{ error }}
                    </div>
                    <div v-if="status"
                        class="rounded-md bg-green-50 p-4 text-sm text-green-800 dark:bg-green-950 dark:text-green-200">
                        {{ status }}
                    </div>

                    <div class="grid w-full items-center gap-2">
                        <Label for="name">
                            {{ t('fields.name') }}
                        </Label>
                        <Input id="name" type="text" name="name" autocomplete="name" required />
                        <InputError :message="errors.name" />
                    </div>

                    <div class="grid w-full items-center gap-2">
                        <Label for="email">
                            {{ t('fields.email') }}
                        </Label>
                        <Input id="email" type="email" name="email" autocomplete="email" required />
                        <InputError :message="errors.email" />
                    </div>

                    <div class="grid w-full items-center gap-2">
                        <Label for="message">
                            {{ t('fields.message') }}
                        </Label>
                        <Textarea id="message" name="message" :placeholder="t('website.contact.message_placeholder')"
                            class="min-h-[11.25rem]" />
                        <InputError :message="errors.message" />
                    </div>

                    <input type="hidden" name="accept_terms" :value="acceptTerms === true ? '1' : '0'" />
                    <div class="flex items-center gap-2 text-sm md:mb-4">
                        <Checkbox id="accept_terms" v-model:checked="acceptTerms"
                            :aria-describedby="'accept_terms_desc'" />
                        <Label id="accept_terms_desc" for="accept_terms" class="cursor-pointer font-normal">
                            {{ t('website.contact.accept_terms') }}
                            <a href="#" class="text-primary underline underline-offset-4 hover:no-underline">
                                {{ t('website.contact.terms_link') }}
                            </a>
                        </Label>
                    </div>
                    <InputError :message="errors.accept_terms" />

                    <div>
                        <Button type="submit" :disabled="processing">
                            <Spinner v-if="processing" />
                            {{ t('website.contact.submit') }}
                        </Button>
                    </div>
                </Form>
            </div>
        </section>
    </WebsiteLayout>
</template>
