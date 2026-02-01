import { usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

export function useTranslations() {
    const page = usePage();
    const translations = computed(
        () => (page.props.translations ?? {}) as Record<string, string>,
    );

    const t = (key: string): string => translations.value[key] ?? key;

    return { t, translations };
}
