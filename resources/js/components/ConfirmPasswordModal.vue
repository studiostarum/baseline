<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';
import { useTranslations } from '@/composables/useTranslations';
import { store as confirmPasswordStore } from '@/routes/password/confirm';
import { router, usePage } from '@inertiajs/vue3';
import { Form } from '@inertiajs/vue3';
import { computed } from 'vue';

const { t } = useTranslations();
const page = usePage();

const open = computed(() => Boolean(page.props.showPasswordConfirmModal));

function cancel(): void {
    router.visit('/settings');
}
</script>

<template>
    <Dialog :open="open" modal>
        <DialogContent
            :show-close-button="false"
            class="sm:max-w-md"
            @pointer-down-outside="(e: Event) => e.preventDefault()"
            @escape-key-down="(e: Event) => e.preventDefault()"
        >
            <DialogHeader>
                <DialogTitle>{{ t('auth.confirm_password.title') }}</DialogTitle>
                <DialogDescription>
                    {{ t('auth.confirm_password.description') }}
                </DialogDescription>
            </DialogHeader>
            <Form
                v-bind="confirmPasswordStore.form()"
                reset-on-success
                v-slot="{ errors, processing }"
            >
                <div class="grid gap-4 py-4">
                    <div class="grid gap-2">
                        <Label for="confirm-password-modal-password">
                            {{ t('fields.password') }}
                        </Label>
                        <Input
                            id="confirm-password-modal-password"
                            type="password"
                            name="password"
                            required
                            autocomplete="current-password"
                            autofocus
                        />
                        <InputError :message="errors.password" />
                    </div>
                </div>
                <DialogFooter>
                    <Button
                        type="button"
                        variant="outline"
                        @click="cancel"
                    >
                        {{ t('common.cancel') }}
                    </Button>
                    <Button
                        type="submit"
                        :disabled="processing"
                        data-test="confirm-password-button"
                    >
                        <Spinner v-if="processing" />
                        {{ t('auth.confirm_password.button') }}
                    </Button>
                </DialogFooter>
            </Form>
        </DialogContent>
    </Dialog>
</template>
