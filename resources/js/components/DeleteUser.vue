<script setup lang="ts">
import ProfileController from '@/actions/App/Http/Controllers/Settings/ProfileController';
import InputError from '@/components/InputError.vue';
import { useTranslations } from '@/composables/useTranslations';
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import {
    Dialog,
    DialogClose,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
    DialogTrigger,
} from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Form } from '@inertiajs/vue3';
import { Trash2 } from 'lucide-vue-next';
import { useTemplateRef } from 'vue';

const passwordInput = useTemplateRef('passwordInput');
const { t } = useTranslations();
</script>

<template>
    <Card class="border-destructive/50">
        <CardHeader>
            <CardTitle class="flex items-center gap-2 text-destructive">
                <Trash2 class="h-5 w-5" />
                {{ t('settings.delete_account.title') }}
            </CardTitle>
            <CardDescription>
                {{ t('settings.delete_account.description') }}
            </CardDescription>
        </CardHeader>
        <CardContent>
            <div
                class="space-y-4 rounded-lg border border-red-100 bg-red-50 p-4 dark:border-red-200/10 dark:bg-red-700/10"
            >
                <div
                    class="relative space-y-0.5 text-red-600 dark:text-red-100"
                >
                    <p class="font-medium">{{ t('common.warning') }}</p>
                    <p class="text-sm">
                        {{ t('settings.delete_account.warning') }}
                    </p>
                </div>
                <Dialog>
                    <DialogTrigger as-child>
                        <Button
                            variant="destructive"
                            data-test="delete-user-button"
                        >
                            {{ t('settings.delete_account.button') }}
                        </Button>
                    </DialogTrigger>
                    <DialogContent>
                        <Form
                            :action="ProfileController.destroy.url()"
                            method="delete"
                            reset-on-success
                            @error="() => passwordInput?.$el?.focus()"
                            :options="{
                                preserveScroll: true,
                            }"
                            class="space-y-6"
                            v-slot="{ errors, processing, reset, clearErrors }"
                        >
                            <DialogHeader class="space-y-3">
                                <DialogTitle>
                                    {{ t('settings.delete_account.confirm_title') }}
                                </DialogTitle>
                                <DialogDescription>
                                    {{ t('settings.delete_account.confirm_description') }}
                                </DialogDescription>
                            </DialogHeader>

                            <div class="grid gap-2">
                                <Label for="password" class="sr-only">{{
                                    t('fields.password')
                                }}</Label>
                                <Input
                                    id="password"
                                    type="password"
                                    name="password"
                                    ref="passwordInput"
                                    :placeholder="t('fields.password_placeholder')"
                                />
                                <InputError :message="errors.password" />
                            </div>

                            <DialogFooter class="gap-2">
                                <DialogClose as-child>
                                    <Button
                                        variant="secondary"
                                        @click="
                                            () => {
                                                clearErrors();
                                                reset();
                                            }
                                        "
                                    >
                                        {{ t('common.cancel') }}
                                    </Button>
                                </DialogClose>

                                <Button
                                    type="submit"
                                    variant="destructive"
                                    :disabled="processing"
                                    data-test="confirm-delete-user-button"
                                >
                                    {{ t('settings.delete_account.button') }}
                                </Button>
                            </DialogFooter>
                        </Form>
                    </DialogContent>
                </Dialog>
            </div>
        </CardContent>
    </Card>
</template>
