<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { useTranslations } from '@/composables/useTranslations';
import AdminLayout from '@/layouts/AdminLayout.vue';
import AppHead from '@/components/AppHead.vue';
import { useForm, usePage } from '@inertiajs/vue3';
import { Trash2 } from 'lucide-vue-next';
import { computed, ref, watch } from 'vue';

const SOCIAL_PLATFORMS = [
    'facebook',
    'instagram',
    'twitter',
    'linkedin',
    'youtube',
] as const;

type SocialLinkRow = { platform: string; url: string };

function parseFooterSocialLinks(raw: string | undefined): SocialLinkRow[] {
    if (!raw || typeof raw !== 'string') {
        return [];
    }
    try {
        const parsed = JSON.parse(raw) as unknown;
        if (!Array.isArray(parsed)) {
            return [];
        }
        return parsed.filter(
            (item): item is SocialLinkRow =>
                item &&
                typeof item === 'object' &&
                'platform' in item &&
                'url' in item &&
                typeof (item as SocialLinkRow).url === 'string',
        );
    } catch {
        return [];
    }
}

const { t } = useTranslations();
const canManageSettings = computed(
    () => usePage().props.auth.can_manage_settings,
);

type Props = {
    settings: Record<string, string>;
};

const props = defineProps<Props>();

const breadcrumbs = computed(() => [
    { title: t('admin.breadcrumb'), href: '/admin' },
    { title: t('admin.settings.title') },
]);

const socialLinks = ref<SocialLinkRow[]>(
    parseFooterSocialLinks(props.settings.footer_social_links),
);

watch(
    () => props.settings.footer_social_links,
    (raw) => {
        socialLinks.value = parseFooterSocialLinks(raw);
    },
);

const form = useForm({
    settings: {
        site_name: props.settings.site_name || '',
        site_description: props.settings.site_description || '',
        contact_email: props.settings.contact_email || '',
        support_phone: props.settings.support_phone || '',
        maintenance_mode: props.settings.maintenance_mode || 'false',
        footer_social_links: props.settings.footer_social_links || '[]',
    },
});

function addSocialLink(): void {
    socialLinks.value.push({ platform: SOCIAL_PLATFORMS[0], url: '' });
}

function removeSocialLink(index: number): void {
    socialLinks.value.splice(index, 1);
}

function submit(): void {
    form.settings.footer_social_links = JSON.stringify(socialLinks.value);
    form.post('/admin/settings');
}
</script>

<template>
    <AppHead :title="t('admin.settings.title')" />

    <AdminLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 p-4 md:p-6">
            <div>
                <h1 class="text-2xl font-bold tracking-tight">
                    {{ t('admin.settings.title') }}
                </h1>
                <p class="text-muted-foreground">
                    {{ t('admin.settings.description') }}
                </p>
            </div>

            <form @submit.prevent="submit" class="space-y-6">
                <Card>
                    <CardHeader>
                        <CardTitle>{{ t('admin.settings.general') }}</CardTitle>
                        <CardDescription>
                            {{ t('admin.settings.general_description') }}
                        </CardDescription>
                    </CardHeader>
                    <CardContent class="space-y-4">
                        <div class="space-y-2">
                            <Label for="site_name">{{
                                t('admin.settings.site_name')
                            }}</Label>
                            <Input
                                id="site_name"
                                v-model="form.settings.site_name"
                                type="text"
                                :placeholder="t('admin.settings.site_name_placeholder')"
                                :disabled="!canManageSettings"
                            />
                            <InputError
                                :message="form.errors['settings.site_name']"
                            />
                        </div>

                        <div class="space-y-2">
                            <Label for="site_description">
                                {{ t('admin.settings.site_description') }}
                            </Label>
                            <Input
                                id="site_description"
                                v-model="form.settings.site_description"
                                type="text"
                                :placeholder="t('admin.settings.site_description_placeholder')"
                                :disabled="!canManageSettings"
                            />
                            <InputError
                                :message="
                                    form.errors['settings.site_description']
                                "
                            />
                        </div>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader>
                        <CardTitle>{{ t('admin.settings.contact') }}</CardTitle>
                        <CardDescription>
                            {{ t('admin.settings.contact_description') }}
                        </CardDescription>
                    </CardHeader>
                    <CardContent class="space-y-4">
                        <div class="space-y-2">
                            <Label for="contact_email">{{
                                t('admin.settings.contact_email')
                            }}</Label>
                            <Input
                                id="contact_email"
                                v-model="form.settings.contact_email"
                                type="email"
                                :placeholder="t('admin.settings.contact_email_placeholder')"
                                :disabled="!canManageSettings"
                            />
                            <InputError
                                :message="form.errors['settings.contact_email']"
                            />
                        </div>

                        <div class="space-y-2">
                            <Label for="support_phone">{{
                                t('admin.settings.support_phone')
                            }}</Label>
                            <Input
                                id="support_phone"
                                v-model="form.settings.support_phone"
                                type="tel"
                                :placeholder="t('admin.settings.support_phone_placeholder')"
                                :disabled="!canManageSettings"
                            />
                            <InputError
                                :message="form.errors['settings.support_phone']"
                            />
                        </div>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader>
                        <CardTitle>{{ t('admin.settings.social_links') }}</CardTitle>
                        <CardDescription>
                            {{ t('admin.settings.social_links_description') }}
                        </CardDescription>
                    </CardHeader>
                    <CardContent class="space-y-4">
                        <div
                            v-for="(link, index) in socialLinks"
                            :key="index"
                            class="flex flex-wrap items-end gap-2 rounded-lg border p-3"
                        >
                            <div class="min-w-32 flex-1 space-y-2 sm:flex-initial">
                                <Label :for="`social-platform-${index}`">
                                    {{ t('admin.settings.social_platform') }}
                                </Label>
                                <select
                                    :id="`social-platform-${index}`"
                                    v-model="link.platform"
                                    class="border-input bg-background h-9 w-full rounded-md border px-3 py-1 text-sm shadow-xs"
                                    :disabled="!canManageSettings"
                                >
                                    <option
                                        v-for="p in SOCIAL_PLATFORMS"
                                        :key="p"
                                        :value="p"
                                    >
                                        {{ t(`admin.settings.social_platform_${p}`) }}
                                    </option>
                                </select>
                            </div>
                            <div class="min-w-0 flex-1 space-y-2 sm:min-w-[200px]">
                                <Label :for="`social-url-${index}`">
                                    {{ t('admin.settings.social_url') }}
                                </Label>
                                <Input
                                    :id="`social-url-${index}`"
                                    v-model="link.url"
                                    type="url"
                                    :placeholder="t('admin.settings.social_url_placeholder')"
                                    :disabled="!canManageSettings"
                                    class="min-w-0"
                                />
                            </div>
                            <Button
                                v-if="canManageSettings"
                                type="button"
                                variant="ghost"
                                size="icon"
                                class="shrink-0"
                                :aria-label="t('common.delete')"
                                @click="removeSocialLink(index)"
                            >
                                <Trash2 class="size-4" aria-hidden />
                                <span class="sr-only">{{ t('common.delete') }}</span>
                            </Button>
                        </div>
                        <Button
                            v-if="canManageSettings"
                            type="button"
                            variant="outline"
                            size="sm"
                            @click="addSocialLink"
                        >
                            {{ t('admin.settings.social_add') }}
                        </Button>
                    </CardContent>
                </Card>

                <div class="flex items-center gap-4">
                    <Button
                        v-if="canManageSettings"
                        type="submit"
                        :disabled="form.processing"
                    >
                        {{
                            form.processing
                                ? t('admin.roles.saving')
                                : t('admin.settings.save_settings')
                        }}
                    </Button>
                    <span
                        v-if="form.recentlySuccessful"
                        class="text-sm text-green-600"
                    >
                        {{ t('admin.settings.saved_success') }}
                    </span>
                </div>
            </form>
        </div>
    </AdminLayout>
</template>
