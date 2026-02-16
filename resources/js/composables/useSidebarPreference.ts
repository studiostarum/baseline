import type { Ref } from 'vue';
import { useSidebar } from '@/components/shared/ui/sidebar';
import {
    SIDEBAR_COOKIE_MAX_AGE,
    SIDEBAR_COOKIE_NAME,
} from '@/components/shared/ui/sidebar/utils';

export type UseSidebarPreferenceReturn = {
    sidebarOpen: Ref<boolean>;
    updateSidebarPreference: (open: boolean) => void;
};

export function useSidebarPreference(): UseSidebarPreferenceReturn {
    const { open: sidebarOpen, setOpen } = useSidebar();

    function updateSidebarPreference(open: boolean): void {
        document.cookie = `${SIDEBAR_COOKIE_NAME}=${open}; path=/; max-age=${SIDEBAR_COOKIE_MAX_AGE}`;
        setOpen(open);
    }

    return {
        sidebarOpen,
        updateSidebarPreference,
    };
}
