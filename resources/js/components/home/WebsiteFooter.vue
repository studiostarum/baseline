<script setup lang="ts">
import AppLogoIcon from '@/components/AppLogoIcon.vue';
import LanguageDropdown from '@/components/LanguageDropdown.vue';
import { Button } from '@/components/ui/button';
import {
    Facebook,
    Instagram,
    Linkedin,
    Twitter,
    Youtube,
} from 'lucide-vue-next';
import { Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import { dashboard, home, login, register } from '@/routes';
import { useTranslations } from '@/composables/useTranslations';
import { cn } from '@/lib/utils';

type ImageProps = {
    url?: string;
    src: string;
    alt?: string;
};

type LinkItem = {
    title: string;
    url: string;
};

type ColumnLinks = {
    title: string;
    links: LinkItem[];
};

type SocialLink = {
    url: string;
    icon: typeof Facebook;
};

type Props = {
    heading?: string;
    description?: string;
    columnLinks?: ColumnLinks[];
    socialMediaLinks?: SocialLink[];
    footerText?: string;
};

const props = withDefaults(defineProps<Props>(), {
    heading: undefined,
    description: undefined,
    columnLinks: undefined,
    socialMediaLinks: undefined,
    footerText: undefined,
});

const { t } = useTranslations();
const page = usePage();
const appName = (page.props.name as string) ?? 'Laravel';

const heading = computed(() => props.heading ?? t('website.footer.heading'));
const description = computed(
    () => props.description ?? t('website.footer.description'),
);
const footerText = computed(() => {
    if (props.footerText) {
        return props.footerText;
    }
    return t('website.footer.copyright')
        .replace(':year', String(new Date().getFullYear()))
        .replace(':name', appName);
});

const defaultColumnLinks = computed<ColumnLinks[]>(() => [
    {
        title: t('website.footer.product'),
        links: [
            { title: t('website.pricing'), url: '#pricing' },
            { title: t('website.footer.features'), url: '#' },
        ],
    },
    {
        title: t('website.footer.company'),
        links: [
            { title: t('website.footer.about'), url: '#' },
            { title: t('website.footer.contact'), url: '#' },
        ],
    },
    {
        title: t('website.footer.legal'),
        links: [
            { title: t('website.footer.privacy'), url: '#' },
            { title: t('website.footer.terms'), url: '#' },
        ],
    },
]);

const columnLinks = computed(
    () => props.columnLinks ?? defaultColumnLinks.value,
);

const defaultSocialLinks = computed<SocialLink[]>(() => [
    { url: '#', icon: Facebook },
    { url: '#', icon: Instagram },
    { url: '#', icon: Twitter },
    { url: '#', icon: Linkedin },
    { url: '#', icon: Youtube },
]);

const socialMediaLinks = computed(
    () => props.socialMediaLinks ?? defaultSocialLinks.value,
);
</script>

<template>
    <footer class="border-t px-[5%] py-12 md:py-16 lg:py-20">
        <div class="container">
            <!-- CTA Section -->
            <div
                class="rounded-xl border bg-muted/50 px-6 py-10 md:px-10 md:py-12 lg:flex lg:items-center lg:justify-between lg:gap-8"
            >
                <div class="max-w-xl">
                    <h2
                        class="text-2xl font-bold tracking-tight md:text-3xl lg:text-4xl"
                    >
                        {{ heading }}
                    </h2>
                    <p class="mt-2 text-muted-foreground md:mt-3">
                        {{ description }}
                    </p>
                </div>
                <div class="mt-6 flex flex-col gap-3 sm:flex-row sm:items-center sm:gap-4 lg:mt-0 lg:shrink-0">
                    <template v-if="!$page.props.auth.user">
                        <Link :href="register()">
                            <Button size="lg" class="w-full sm:w-auto">
                                {{ t('website.get_started') }}
                            </Button>
                        </Link>
                        <Link :href="login()">
                            <Button variant="outline" size="lg" class="w-full sm:w-auto">
                                {{ t('website.sign_in') }}
                            </Button>
                        </Link>
                        <a
                            href="#pricing"
                            :class="
                                cn(
                                    'text-center text-sm font-medium text-muted-foreground underline-offset-4 transition-colors hover:text-foreground hover:underline',
                                )
                            "
                        >
                            {{ t('website.footer.cta.view_pricing') }}
                        </a>
                    </template>
                    <Link v-else :href="dashboard()">
                        <Button size="lg" class="w-full sm:w-auto">
                            {{ t('navigation.dashboard') }}
                        </Button>
                    </Link>
                </div>
            </div>

            <div class="my-12 h-px w-full bg-border md:my-16 lg:my-20" />

            <div class="mb-12 grid grid-cols-1 gap-x-8 gap-y-10 sm:grid-cols-2 md:mb-16 md:gap-y-12 lg:mb-20 lg:grid-cols-3">
                <div
                    v-for="(column, index) in columnLinks"
                    :key="index"
                    class="flex flex-col items-start justify-start"
                >
                    <h3 class="mb-2 font-semibold">{{ column.title }}</h3>
                    <ul class="flex flex-col gap-2">
                        <li
                            v-for="(link, linkIndex) in column.links"
                            :key="linkIndex"
                        >
                            <a
                                :href="link.url"
                                :class="
                                    cn(
                                        'text-sm text-muted-foreground transition-colors hover:text-foreground',
                                    )
                                "
                            >
                                {{ link.title }}
                            </a>
                        </li>
                    </ul>
                </div>
            </div>

            <div
                class="mb-6 flex flex-col items-start justify-between gap-4 pb-6 md:pb-8 sm:flex-row sm:items-center"
            >
                <Link :href="home()" class="flex items-center gap-2">
                    <AppLogoIcon class="size-6 text-primary dark:text-foreground" />
                    <span class="text-xl font-bold tracking-tight">{{ appName }}</span>
                </Link>
            </div>

            <div class="h-px w-full bg-border" />

            <div
                class="flex flex-col-reverse items-start gap-4 pb-4 pt-6 text-sm sm:flex-row sm:items-center sm:justify-between md:pb-0 md:pt-8"
            >
                <p class="text-muted-foreground">{{ footerText }}</p>
                <div class="flex items-center gap-3">
                    <LanguageDropdown variant="button" side="top" />
                    <a
                        v-for="(link, index) in socialMediaLinks"
                        :key="index"
                        :href="link.url"
                        class="text-muted-foreground transition-colors hover:text-foreground"
                        :aria-label="link.url"
                    >
                        <component
                            :is="link.icon"
                            class="size-5"
                        />
                    </a>
                </div>
            </div>
        </div>
    </footer>
</template>
