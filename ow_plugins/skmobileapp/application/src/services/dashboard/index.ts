import { Injectable } from '@angular/core';

// services
import { SiteConfigsService } from 'services/site-configs';
import { PersistentStorageService } from 'services/persistent-storage';

@Injectable()
export class DashboardService {
    public profilePage: string = 'profile';
    public searchPage: string = 'search';
    public conversationsPage: string = 'conversations';
    public hotListPage: string = 'hot-list';
    public tinderPage: string = 'tinder';
    public browsePage: string = 'search';
    public components: string[] = [
        this.profilePage,
        this.searchPage,
        this.conversationsPage
    ];

    /**
     * Constructor
     */
    constructor(
        private siteConfigs: SiteConfigsService,
        private persistentStorage: PersistentStorageService) {}

    /**
     * Get component index by name 
     */
    getComponentIndexByName(component: string): number {
        return this.components.indexOf(component);
    }
 
    /**
     * Get active component
     */
    getActiveComponent(): string {
        return this.persistentStorage.getValue('active_component') !== null
            ? this.persistentStorage.getValue('active_component')
            : this.components[0];
    }

    /**
     * Get active sub component
     */
    getActiveSubComponent(): string {
        return this.persistentStorage.getValue('active_sub_component') !== null
            ? this.persistentStorage.getValue('active_sub_component')
            : this.defaultSubComponent();
    }

    /**
     * Set active sub component
     */
    setActiveSubComponent(subComponentName: string): void {
        this.persistentStorage.setValue('active_sub_component', subComponentName);
    }

    /**
     * Set active component
     */
    setActiveComponent(componentName: string, subComponentName?: string): void { 
        const componentIndex = this.getComponentIndexByName(componentName);

        if (componentIndex === -1) {
            throw new TypeError(`Component not found`);
        }

        this.persistentStorage.setValue('active_component', componentName);

        if (subComponentName) {
            this.persistentStorage.setValue('active_sub_component', subComponentName);
        }
    }

    /**
     * Set active component by index
     */
    setActiveComponentByIndex(index: number): void {
        if (index > this.components.length - 1 || index < 0) {
            throw new RangeError(`The argument must be between 0 and ${this.components.length - 1}`);
        }
 
        this.persistentStorage.setValue('active_component', this.components[index]);
    }

    /**
     * Is active sub component
     */
    isActiveSubComponent(name: string): boolean {
        if (this.getActiveSubComponent() == name) {
            return true;
        }

        return false;
    }

    /**
     * Get default sub component
     */
    defaultSubComponent(): string {
        if (this.siteConfigs.isPluginActive('hotlist')) {
            return this.hotListPage;
        }

        if (this.siteConfigs.isTinderSearchAllowed()) {
            return this.tinderPage;
        }

        return this.browsePage;
    }
}
