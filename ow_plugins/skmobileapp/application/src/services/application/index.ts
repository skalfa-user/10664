import { Injectable, Inject } from '@angular/core';
import { IAppState } from 'store';
import { NgRedux } from '@angular-redux/store';
import { Observable } from 'rxjs/Observable';
import { Device } from '@ionic-native/device';
import { Platform } from 'ionic-angular';
import isEqual from 'lodash/isEqual';

// services
import { PersistentStorageService } from 'services/persistent-storage';
import { StringUtilsService } from 'services/string-utils';

// load app config
import { APPLICATION_CONFIG_PROVIDER, IApplicationConfig } from 'app/app.config';

// payloads
import { IByIdPayload, ILocationPayload } from 'store/payloads';

// store
import { 
    APPLICATION_SET_LANGUAGE, 
    APPLICATION_SET_LANGUAGE_DIRECTION, 
    APPLICATION_SET_LOCATION, 
    APPLICATION_RESET 
} from 'store/actions';

import { IApplicationLocation } from 'store/states';

import { 
    getApplicationLanguage, 
    getApplicationLanguageDirection, 
    getApplicationLocation 
} from 'store/reducers';

@Injectable()
export class ApplicationService {
    /**
     * Ltr language direction
     */
    ltrDirection: string = 'ltr';

    /**
     * Rtl language direction
     */
    rtlDirection: string = 'rtl';

    /**
     * Base uri
     */
    protected baseUri: string;

    /**
     * Base api uri
     */
    protected baseApiUri: string;

    /**
     * Constructor
     */
    constructor (
        @Inject(APPLICATION_CONFIG_PROVIDER) private config: IApplicationConfig,
        private ngRedux: NgRedux<IAppState>,
        private persistentStorage: PersistentStorageService,
        private device: Device,
        private stringUtils: StringUtilsService, 
        private platform: Platform)
    {
        this.baseUri = '/skmobileapp/';
        this.baseApiUri = this.baseUri + 'api';
    }

    /**
     * Get config
     */
    getConfig(configName: string): string {
        return this.config[configName];
    }

    /**
     * Get generic api url
     */
    getGenericApiUrl(): string {
        if (!this.getConfig('serverUrl')) { // check a custom server url
            return this.persistentStorage.getValue('server-url');
        }
    }

    /**
     * Set generic api url
     */
    setGenericApiUrl(url: string): void {
        this.persistentStorage.setValue('server-url', url);
    }

    /**
     * Get api uri
     */
    getApiUri(): string {
        return this.baseApiUri;
    }

    /**
     * Get api url
     */
    getApiUrl(): string {
        let serverUrl: string = this.config.serverUrl // check custom server url
            ? this.config.serverUrl
            : this.getGenericApiUrl();

        if (serverUrl) {
            return serverUrl + this.getApiUri();
        }
    }

    /**
     * Watch language
     */
    watchLanguage(): Observable<string> {
        return this.ngRedux.select((appState: IAppState) => getApplicationLanguage(appState));
    }

    /**
     * Get language
     */
    getLanguage(): string {
        return getApplicationLanguage(this.ngRedux.getState());
    }
 
    /**
     * Set language
     */
    setLanguage(language: string): void {
        const payload: IByIdPayload = {
            id: language.split('-')[0]
        };

        this.ngRedux.dispatch({
            type: APPLICATION_SET_LANGUAGE,
            payload: payload
        });
    }

    /**
     * Set language direction
     */
    setLanguageDirection(direction: string): void {
        const payload: IByIdPayload = {
            id: direction
        };

        this.ngRedux.dispatch({
            type: APPLICATION_SET_LANGUAGE_DIRECTION,
            payload: payload
        });
    }
 
    /**
     * Watch language direction
     */
    watchLanguageDirection(): Observable<string> {
        return this.ngRedux.select((appState: IAppState) => getApplicationLanguageDirection(appState));
    }

    /**
     * Get language direction
     */
    getLanguageDirection(): string {
        return getApplicationLanguageDirection(this.ngRedux.getState());
    }

    /**
     * Is language direction ltr
     */
    isLanguageDirectionLtr(): boolean {
        return this.getLanguageDirection() === this.ltrDirection;
    }
 
    /**
     * Watch location
     */
    watchLocation(): Observable<IApplicationLocation> {
        return this.ngRedux.select((appState: IAppState) => getApplicationLocation(appState), isEqual);
    }

    /**
     * Get location
     */
    getLocation(): IApplicationLocation {
        return getApplicationLocation(this.ngRedux.getState());
    }
 
    /**
     * Set location
     */
    setLocation(latitude: number, longitude: number): void {
        const payload: ILocationPayload = {
            latitude: latitude,
            longitude: longitude
        };

        this.ngRedux.dispatch({
            type: APPLICATION_SET_LOCATION,
            payload: payload
        });
    }

    /**
     * Reset application
     */
    resetApplication(): void {
        this.ngRedux.dispatch({
            type: APPLICATION_RESET,
            payload: {}
        });
    }

    /**
     * Is app running in external browser
     */
    isAppRunningInExternalBrowser(): boolean {
        return this.getAppUrl().startsWith('http') && !this.getAppUrl().startsWith('http://localhost:8080')
    }

    /**
     * Is app ready for download
     */
    isAppReadyForDownload(): boolean {
        if (this.isAppRunningInExternalBrowser() && !this.isAppRunningInPwaMode()) {
            return true;
        }

        return false;
    }

    /**
     * Is app running in pwa mode
     */
    isAppRunningInPwaMode(): boolean {
        return this.platform.getQueryParam('pwa') !== undefined;
    }

    /**
     * Is app running in mobile safari
     */
    isAppRunningInMobileSafari(): boolean {
        const userAgent: string = this.getAppUserAgent();

        return /iP(ad|od|hone)/i.test(userAgent) && /WebKit/i.test(userAgent) && !(/(CriOS|FxiOS|OPiOS|mercury)/i.test(userAgent));
    }

    /**
     * Get app user agent
     */
    getAppUserAgent(): string {
        return navigator.userAgent;
    }

    /**
     * Get document url
     */
    getAppUrl(isIncludeParams: boolean = true): string {
        // return the full address
        if (isIncludeParams) {
            return document.URL;
        }

        return document.URL.split('?')[0];
    }

    /**
     * Get app url params
     */
    getAppUrlParams(): {[key: string]: string} {
        const url = this.getAppUrl().split('#');
        const urlParams = url[0].indexOf('?') > -1 ? url[0] : null;
        const urlHash = url[1] ? url[1] : null;
        const result: { [key: string]: string } = {};

        // parse url
        if (urlParams) {
            const urlParamList = urlParams.slice(urlParams.indexOf('?') + 1).split('&');

            urlParamList.map(param => {
                let [key, val] = param.split('=');
                result[key] = decodeURIComponent(val);
            });
        }

        // parse hash
        if (urlHash) {
            const urlHashParams = urlHash.split('&');

            urlHashParams.map(hash => {
                let [key, val] = hash.split('=');
                result[key] = decodeURIComponent(val);
            });
        }

        return result;
    }

    /**
     * Get app uuid
     */
    getAppUuid(): string {
        const uuid: string = this.device.uuid
            ? this.device.uuid
            : this.persistentStorage.getValue('app_uuid');

        if (uuid) {
            return uuid;
        }

        // generate a custom uuid
        const customUuid: string = this.stringUtils.getRandomString();
        this.persistentStorage.setValue('app_uuid', customUuid);

        return customUuid;
    }
}
