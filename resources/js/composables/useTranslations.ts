import { usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import { translationAliases } from '@/lang/translation-aliases';

function getNested(
    obj: Record<string, unknown>,
    path: string,
): string | undefined {
    const value = path
        .split('.')
        .reduce(
            (acc: unknown, part) =>
                acc !== null && typeof acc === 'object' && part in (acc as object)
                    ? (acc as Record<string, unknown>)[part]
                    : undefined,
            obj as unknown,
        );
    if (typeof value === 'string') {
        return value;
    }
    if (
        value !== null &&
        typeof value === 'object' &&
        '_' in (value as object) &&
        typeof (value as Record<string, unknown>)._ === 'string'
    ) {
        return (value as Record<string, unknown>)._ as string;
    }
    return undefined;
}

export function useTranslations() {
    const page = usePage();
    const translations = computed(
        () => (page.props.translations ?? {}) as Record<string, unknown>,
    );

    const t = (key: string): string => {
        const canonical = translationAliases[key] ?? key;
        return getNested(translations.value, canonical) ?? key;
    };

    return { t, translations };
}
