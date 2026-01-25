<script setup lang="ts">
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogClose,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';

type Props = {
    open: boolean;
    title?: string;
    description?: string;
    confirmLabel?: string;
    cancelLabel?: string;
    variant?: 'default' | 'destructive';
    loading?: boolean;
};

withDefaults(defineProps<Props>(), {
    title: 'Are you sure?',
    description: 'This action cannot be undone.',
    confirmLabel: 'Confirm',
    cancelLabel: 'Cancel',
    variant: 'destructive',
    loading: false,
});

const emit = defineEmits<{
    'update:open': [value: boolean];
    confirm: [];
    cancel: [];
}>();

function handleConfirm(): void {
    emit('confirm');
}

function handleCancel(): void {
    emit('cancel');
    emit('update:open', false);
}
</script>

<template>
    <Dialog :open="open" @update:open="$emit('update:open', $event)">
        <DialogContent>
            <DialogHeader>
                <DialogTitle>{{ title }}</DialogTitle>
                <DialogDescription>{{ description }}</DialogDescription>
            </DialogHeader>
            <DialogFooter>
                <DialogClose as-child>
                    <Button variant="outline" @click="handleCancel">
                        {{ cancelLabel }}
                    </Button>
                </DialogClose>
                <Button
                    :variant="variant"
                    :disabled="loading"
                    @click="handleConfirm"
                >
                    {{ loading ? 'Processing...' : confirmLabel }}
                </Button>
            </DialogFooter>
        </DialogContent>
    </Dialog>
</template>
