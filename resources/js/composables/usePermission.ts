import { usePage } from '@inertiajs/vue3';

interface PageProps {
    auth: {
        user: any
        permissions: string[]
        roles: string[]
    }
}
 const page = usePage<PageProps>()

export function usePermission() {
    const hasPermission = (name: string) => {
        const permissions = usePage().props.auth.permissions as string[];
        return permissions.includes(name);
    };

    const hasRole = (name: string) => {
        const roles = usePage().props.auth.roles as string[];
        return roles.includes(name);
    };

    return { hasPermission, hasRole };
}
