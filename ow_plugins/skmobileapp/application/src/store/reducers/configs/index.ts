import { IMapType } from 'store/types';
import { CONFIGS_SET, APPLICATION_RESET } from 'store/actions';
import { IAppState } from 'store';

// payloads
import { IMapPayload } from 'store/payloads';

/**
 * Configs initial state
 */
export const configsInitialState: IMapType<any> = {};

// search modes
export const TINDER_SEARCH_MODE: string = 'tinder';
export const BROWSE_SEARCH_MODE: string = 'browse';
export const BOTH_SEARCH_MODE: string = 'both';

/**
 * Configs reducer
 */
export const configs = (currentState: IMapType<any>, action: any): IMapType<any> => {
    // add initial state
    if (!currentState) {
        currentState = configsInitialState;
    }

    switch(action.type) {
        case CONFIGS_SET :
            const configsPayload: IMapPayload = action.payload;

            return configsPayload;

        case APPLICATION_RESET : // clear all configs
            return configsInitialState;
    }

    return currentState; 
};

// selectors

export const getConfigs = (appState: IAppState) => appState.configs;

/**
 * Get config by id
 */
export function getConfigById(appState: IAppState, configId: string | number): any | undefined {
    return getConfigs(appState)[configId];
}

/**
 * Is plugin active
 */
export function isPluginActive(appState: IAppState, pluginKey: string | Array<string>): boolean {
    const allActivePlugins = getConfigById(appState, 'activePlugins');

    if (allActivePlugins) {
        if (Array.isArray(pluginKey)) {
            return pluginKey.some((currentPlugin: string) => 
                allActivePlugins.some(searchPlugin => currentPlugin === searchPlugin));
        }

        return allActivePlugins.some((plugin: string) => plugin === pluginKey);
    }

    return false;
}

/**
 * Is tinder search mode
 */
export function isTinderSearchMode(appState: IAppState): boolean {
    return getConfigById(appState, 'searchMode') === TINDER_SEARCH_MODE;
}

/**
 * Is browse search mode
 */
export function isBrowseSearchMode(appState: IAppState): boolean {
    return getConfigById(appState, 'searchMode') === BROWSE_SEARCH_MODE;
}

/**
 * Is both search mode
 */
export function isBothSearchMode(appState: IAppState): boolean {
    return getConfigById(appState, 'searchMode') === BOTH_SEARCH_MODE;
}

/**
 * Is tinder search allowed
 */
export function isTinderSearchAllowed(appState: IAppState): boolean {
    const searchMode = getConfigById(appState, 'searchMode');

    return searchMode === TINDER_SEARCH_MODE || searchMode === BOTH_SEARCH_MODE;
}

/**
 * Is browse search allowed
 */
export function isBrowseSearchAllowed(appState: IAppState): boolean {
    const searchMode = getConfigById(appState, 'searchMode');

    return searchMode === BROWSE_SEARCH_MODE || searchMode === BOTH_SEARCH_MODE;
}
