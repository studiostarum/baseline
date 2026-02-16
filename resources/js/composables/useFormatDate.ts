import { usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

const defaultDateOptions: Intl.DateTimeFormatOptions = {
    month: 'short',
    day: 'numeric',
    year: 'numeric',
};

const longDateOptions: Intl.DateTimeFormatOptions = {
    month: 'long',
    day: 'numeric',
    year: 'numeric',
};

const shortDateOptions: Intl.DateTimeFormatOptions = {
    month: 'numeric',
    day: 'numeric',
    year: 'numeric',
};

export function useFormatDate() {
    const page = usePage();
    const locale = computed(() => (page.props.locale as string) ?? 'en');

    function formatDate(
        dateString: string | null,
        options: Intl.DateTimeFormatOptions = defaultDateOptions,
    ): string {
        if (!dateString) {
            return '';
        }
        return new Date(dateString).toLocaleDateString(locale.value, options);
    }

    function formatLongDate(dateString: string | null): string {
        return formatDate(dateString, longDateOptions);
    }

    function formatShortDate(dateString: string | null): string {
        return formatDate(dateString, shortDateOptions);
    }

    function formatDateTime(dateString: string | null): string {
        if (!dateString) {
            return '';
        }
        return new Date(dateString).toLocaleString(locale.value, {
            ...defaultDateOptions,
            hour: 'numeric',
            minute: '2-digit',
        });
    }

    return {
        formatDate,
        formatLongDate,
        formatShortDate,
        formatDateTime,
        locale,
    };
}
