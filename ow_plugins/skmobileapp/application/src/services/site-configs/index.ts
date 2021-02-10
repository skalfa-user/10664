import { Injectable } from '@angular/core';
import { Observable } from 'rxjs/Observable';
import { NgRedux } from '@angular-redux/store';
import { IMapType } from 'store/types';
import isEqual from 'lodash/isEqual';

// services
import { SecureHttpService } from 'services/http';

// payloads
import { IMapPayload } from 'store/payloads';

// store
import { IAppState } from 'store';
import { CONFIGS_SET } from 'store/actions';

import { 
    getConfigById, 
    isPluginActive, 
    isTinderSearchMode,
    isBrowseSearchMode,
    isBothSearchMode,
    isTinderSearchAllowed,
    isBrowseSearchAllowed
} from 'store/reducers';

@Injectable()
export class SiteConfigsService { 
    /**
     * Constructor
     */
    constructor (
        private ngRedux: NgRedux<IAppState>, 
        private http: SecureHttpService) {}

    /**
     * Load configs
     */
    loadConfigs(): Observable<IMapType<any>> {
        const loadConfigs: Observable<IMapType<any>> = this.http.get('/configs');
 
        loadConfigs.subscribe(response => {
            this.setConfigs(response);
        }, () => {});

        return loadConfigs;
    }

    /**
     * Set configs
     */
    setConfigs(configs: IMapType<any>): void {
        const configsPayload: IMapPayload = configs;

        this.ngRedux.dispatch({
            type: CONFIGS_SET,
            payload: configsPayload
        });
    }

    /**
     * Watch config group
     */
    watchConfigGroup(configIds: Array<string | number>): Observable<Array<any>> {
        const observableConfigs = [];

        configIds.forEach(configId => observableConfigs.push(this.watchConfig(configId)));

        return Observable.combineLatest(observableConfigs);
    }

    /**
     * Watch config
     */
    watchConfig(configId: string | number): Observable<any> {
        return this.ngRedux.select((appState: IAppState) => getConfigById(appState, configId), isEqual);
    }

    /**
     * Get config
     */
    getConfig(configId: string | number): any {
        return getConfigById(this.ngRedux.getState(), configId);
    }

    /**
     * Watch is plugin active
     */
    watchIsPluginActive(pluginKey: string | Array<string>): Observable<boolean> {
        return this.ngRedux.select((appState: IAppState) => isPluginActive(appState, pluginKey));
    }

    /**
     * Is plugin active
     */
    isPluginActive(pluginKey: string | Array<string>): boolean {
        return isPluginActive(this.ngRedux.getState(), pluginKey);
    }

    /**
     * Watch is tinder search mode
     */
    watchIsTinderSearchMode(): Observable<boolean> {
        return this.ngRedux.select((appState: IAppState) => isTinderSearchMode(appState));
    }

    /**
     * Is tinder search mode
     */
    isTinderSearchMode(): boolean {
        return isTinderSearchMode(this.ngRedux.getState());
    }

    /**
     * Is browse search mode
     */
    isBrowseSearchMode(): boolean {
        return isBrowseSearchMode(this.ngRedux.getState());
    }

    /**
     * Is both search mode
     */
    isBothSearchMode(): boolean {
        return isBothSearchMode(this.ngRedux.getState());
    }

    /**
     * Is tinder search allowed
     */
    isTinderSearchAllowed(): boolean {
        return isTinderSearchAllowed(this.ngRedux.getState());
    }

    /**
     * Is browse search allowed
     */
    isBrowseSearchAllowed(): boolean {
        return isBrowseSearchAllowed(this.ngRedux.getState());
    }
}
