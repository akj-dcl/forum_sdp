import { usePage } from '@inertiajs/vue3';

export function can(permission: string): boolean {
    const page = usePage();
    const permissions = (page.props.auth as any).user?.permissions || [];
    return permissions.includes(permission);
}