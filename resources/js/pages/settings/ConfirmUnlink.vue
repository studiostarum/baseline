<script setup lang="ts">
import { Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import AppHead from '@/components/shared/AppHead.vue';
import Heading from '@/components/shared/Heading.vue';
import { Button } from '@/components/shared/ui/button';
import AppLayout from '@/layouts/AppLayout.vue';
import SettingsLayout from '@/layouts/settings/Layout.vue';
import { edit } from '@/routes/profile';
import { unlink } from '@/routes/social';
import { type BreadcrumbItem } from '@/types';

type Props = {
    provider: string;
};

const props = defineProps<Props>();

const breadcrumbItems: BreadcrumbItem[] = [
    { title: 'Profile', href: edit().url },
    { title: 'Unlink account', href: '#' },
];

const isSubmitting = ref(false);

function confirmUnlink(): void {
    if (isSubmitting.value) {
        return;
    }
    isSubmitting.value = true;
    router.delete(unlink({ provider: props.provider }).url, {
        preserveScroll: true,
        onFinish: () => {
            isSubmitting.value = false;
        },
    });
}
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbItems">
        <AppHead :title="`Unlink ${provider}`" />

        <h1 class="sr-only">Unlink {{ provider }}</h1>

        <SettingsLayout>
            <div class="space-y-6">
                <Heading
                    variant="small"
                    :title="`Unlink ${provider}`"
                    :description="`You will no longer be able to sign in with ${provider}. You can link it again later from Profile.`"
                />

                <div class="flex gap-4">
                    <Button
                        type="button"
                        variant="destructive"
                        :disabled="isSubmitting"
                        @click="confirmUnlink"
                    >
                        {{ isSubmitting ? 'Unlinking...' : 'Unlink' }}
                    </Button>
                    <Button variant="outline" as-child>
                        <Link :href="edit().url">Cancel</Link>
                    </Button>
                </div>
            </div>
        </SettingsLayout>
    </AppLayout>
</template>
