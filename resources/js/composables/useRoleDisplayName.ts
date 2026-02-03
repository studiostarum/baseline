import { useTranslations } from '@/composables/useTranslations';

function roleLabelFromKey(name: string): string {
    return name
        .split(/[-_]/)
        .map((word) => word.charAt(0).toUpperCase() + word.slice(1).toLowerCase())
        .join(' ');
}

export function useRoleDisplayName() {
    const { t } = useTranslations();

    function roleDisplayName(name: string): string {
        const key = `admin.role_labels.${name.replace(/-/g, '_')}`;
        const translated = t(key);
        if (translated === key || translated.includes('admin.role_labels.')) {
            return roleLabelFromKey(name);
        }
        return translated;
    }

    return { roleDisplayName, roleLabelFromKey };
}
