import {defineStore} from "pinia";

export const useBreadcrumbStore = defineStore('breadcrumb', {
    state: () => ({ breadcrumbs: [] as IBreadcrumb[] }),
    getters: {
        getBreadcrumbs: (state) => state.breadcrumbs,
    },
    actions: {
        addBreadcrumb(name: string, path: string) {
            this.breadcrumbs.push({ name, path });
        },
        removeBreadcrumb(path: string) {
            this.breadcrumbs = this.breadcrumbs.filter(breadcrumb => breadcrumb.path !== path);
        },
        clearBreadcrumbs() {
            this.breadcrumbs = [];
        },
        replaceBreadcrumbs(breadcrumbs: IBreadcrumb[]) {
            this.breadcrumbs = breadcrumbs;
        }
    }
});

export interface IBreadcrumb {
    name: string;
    path: string;
}