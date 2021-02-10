import { configs, configsInitialState } from './';

// payloads
import { IMapPayload } from 'store/payloads';

// store
import { IAppState } from 'store';
import { CONFIGS_SET, APPLICATION_RESET } from 'store/actions';

// selectors
import { 
    getConfigById,
    isPluginActive,
    isTinderSearchMode,
    isBrowseSearchMode,
    isBothSearchMode,
    isTinderSearchAllowed,
    isBrowseSearchAllowed,
    TINDER_SEARCH_MODE,
    BROWSE_SEARCH_MODE,
    BOTH_SEARCH_MODE
} from 'store/reducers';

// fakes
import {
    ReduxFake
} from 'test/fake';

describe('Configs reducer', () => {
    // register service's fakes
    let fakeRedux: ReduxFake;

    beforeEach(() => { 
        // init service's fakes
        fakeRedux = new ReduxFake();
    });

    it('should handle initial state', () => {
        expect(configs(undefined, '')).toEqual(configsInitialState);
    });

    it('should handle CONFIGS_SET', () => {
        const payload: IMapPayload = {
            test: 'test'
        };

        expect(configs(undefined, {
            type: CONFIGS_SET,
            payload: payload
        })).toEqual(payload)
    });

    it('should handle APPLICATION_RESET', () => {
        expect(configs(undefined, {
            type: APPLICATION_RESET
        })).toEqual(configsInitialState)
    });

    it('getConfigById should return correct value', () => {
        const configId: string = 'test';
        const configValue: string = 'value';

        const state: IAppState = { // fake state
            configs: { 
                [configId]: configValue
            }
        };

        // fake the method
        spyOn(fakeRedux, 'getState').and.returnValue(state);

        expect(getConfigById(fakeRedux.getState(), 'test')).toEqual(configValue);
    });

    it('getConfigById should return undefined for the absent configs', () => {
        const state: IAppState = { // fake state
            configs: {}
        };

        // fake the method
        spyOn(fakeRedux, 'getState').and.returnValue(state);

        expect(getConfigById(fakeRedux.getState(), 'test')).toBeUndefined();
    });

    it('isPluginActive should return a negative boolean value if plugins list is empty in the config list', () => {
        const pluginKey: string = 'test';
        const state: IAppState = { // fake state
            configs: {
            }
        };

        // fake the method
        spyOn(fakeRedux, 'getState').and.returnValue(state);

        const isActive: boolean = isPluginActive(fakeRedux.getState(), pluginKey);

        expect(fakeRedux.getState).toHaveBeenCalled();
        expect(isActive).toBeFalsy();
    });

    it('isPluginActive should return a negative boolean value if a plugin is not registered in the config list', () => {
        const pluginKey: string = 'test';
        const state: IAppState = { // fake state
            configs: {
                activePlugins: [
                    'test2',
                    'test3'
                ]
            }
        };

        // fake the method
        spyOn(fakeRedux, 'getState').and.returnValue(state);

        const isActive: boolean = isPluginActive(fakeRedux.getState(), pluginKey);

        expect(fakeRedux.getState).toHaveBeenCalled();
        expect(isActive).toBeFalsy();
    });

    it('isPluginActive should return a positive boolean value if a plugin is registered in the config list', () => {
        const pluginKey: string = 'test';
        const state: IAppState = { // fake state
            configs: {
                activePlugins: [
                    'test'
                ]
            }
        };

        // fake the method
        spyOn(fakeRedux, 'getState').and.returnValue(state);

        const isActive: boolean = isPluginActive(fakeRedux.getState(), pluginKey);

        expect(fakeRedux.getState).toHaveBeenCalled();
        expect(isActive).toBeTruthy();
    });

    it('isPluginActive should return a positive boolean value if some of plugins are registered in the config list', () => {
        const pluginKeys: Array<string> = [
            'test2',
            'test'
        ];

        const state: IAppState = { // fake state
            configs: {
                activePlugins: [
                    'test',
                    'test2',
                    'test3'
                ]
            }
        };

        // fake the method
        spyOn(fakeRedux, 'getState').and.returnValue(state);

        const isActive: boolean = isPluginActive(fakeRedux.getState(), pluginKeys);

        expect(fakeRedux.getState).toHaveBeenCalled();
        expect(isActive).toBeTruthy();
    });

    it('isTinderSearchMode should return a positive boolean value if the tinder mode activated', () => {
        const state: IAppState = { // fake state
            configs: {
                searchMode: TINDER_SEARCH_MODE
            }
        };

        // fake the method
        spyOn(fakeRedux, 'getState').and.returnValue(state);

        expect(isTinderSearchMode(fakeRedux.getState())).toBeTruthy();
    });

    it('isBrowseSearchMode should return a positive boolean value if the browse mode activated', () => {
        const state: IAppState = { // fake state
            configs: {
                searchMode: BROWSE_SEARCH_MODE
            }
        };

        // fake the method
        spyOn(fakeRedux, 'getState').and.returnValue(state);

        expect(isBrowseSearchMode(fakeRedux.getState())).toBeTruthy();
    });

    it('isBothSearchMode should return a positive boolean value if the both mode activated', () => {
        const state: IAppState = { // fake state
            configs: {
                searchMode: BOTH_SEARCH_MODE
            }
        };

        // fake the method
        spyOn(fakeRedux, 'getState').and.returnValue(state);

        expect(isBothSearchMode(fakeRedux.getState())).toBeTruthy();
    });

    it('isTinderSearchAllowed should return a positive boolean value if the tinder mode is activated', () => {
        const state: IAppState = { // fake state
            configs: {
                searchMode: TINDER_SEARCH_MODE
            }
        };

        // fake the method
        spyOn(fakeRedux, 'getState').and.returnValue(state);

        expect(isTinderSearchAllowed(fakeRedux.getState())).toBeTruthy();
    });

    it('isTinderSearchAllowed should return a positive boolean value if the both mode is activated', () => {
        const state: IAppState = { // fake state
            configs: {
                searchMode: BOTH_SEARCH_MODE
            }
        };

        // fake the method
        spyOn(fakeRedux, 'getState').and.returnValue(state);

        expect(isTinderSearchAllowed(fakeRedux.getState())).toBeTruthy();
    });

    it('isTinderSearchAllowed should return a negative boolean value if the browse mode is activated', () => {
        const state: IAppState = { // fake state
            configs: {
                searchMode: BROWSE_SEARCH_MODE
            }
        };

        // fake the method
        spyOn(fakeRedux, 'getState').and.returnValue(state);

        expect(isTinderSearchAllowed(fakeRedux.getState())).toBeFalsy();
    });

    it('isBrowseSearchAllowed should return a positive boolean value if the browse mode is activated', () => {
        const state: IAppState = { // fake state
            configs: {
                searchMode: BROWSE_SEARCH_MODE
            }
        };

        // fake the method
        spyOn(fakeRedux, 'getState').and.returnValue(state);

        expect(isBrowseSearchAllowed(fakeRedux.getState())).toBeTruthy();
    });

    it('isBrowseSearchAllowed should return a positive boolean value if the both mode is activated', () => {
        const state: IAppState = { // fake state
            configs: {
                searchMode: BOTH_SEARCH_MODE
            }
        };

        // fake the method
        spyOn(fakeRedux, 'getState').and.returnValue(state);

        expect(isBrowseSearchAllowed(fakeRedux.getState())).toBeTruthy();
    });

    it('isBrowseSearchAllowed should return a negative boolean value if the tinder mode is activated', () => {
        const state: IAppState = { // fake state
            configs: {
                searchMode: TINDER_SEARCH_MODE
            }
        };

        // fake the method
        spyOn(fakeRedux, 'getState').and.returnValue(state);

        expect(isBrowseSearchAllowed(fakeRedux.getState())).toBeFalsy();
    });
});
