import { application, applicationInitialState } from './';
import isEqual from 'lodash/isEqual';
import cloneDeep from 'lodash/cloneDeep';

// payloads
import { IByIdPayload, ILocationPayload } from 'store/payloads';

// store
import { IAppState } from 'store';
import { IApplication, IApplicationLocation } from 'store/states';

import { 
    APPLICATION_SET_LANGUAGE, 
    APPLICATION_SET_LOCATION, 
    APPLICATION_SET_LANGUAGE_DIRECTION 
} from 'store/actions';

// selectors
import { 
    getApplicationLanguage, 
    getApplicationLanguageDirection,
    getApplicationLocation
} from 'store/reducers';

// fakes
import {
    ReduxFake
} from 'test/fake';

describe('Application reducer', () => {
    // register service's fakes
    let fakeRedux: ReduxFake;

    beforeEach(() => { 
        // init service's fakes
        fakeRedux = new ReduxFake();
    });

    it('should handle initial state', () => {
        expect(application(undefined, '')).toEqual(applicationInitialState);
    });

    it('should handle APPLICATION_SET_LANGUAGE', () => {
        const language: string = 'ru';
        const payload: IByIdPayload = {
            id: language
        };

        expect(application(undefined, {
            type: APPLICATION_SET_LANGUAGE,
            payload: payload
        })).toEqual({
            ...applicationInitialState,
            language: language
        })
    });

    it('should handle APPLICATION_SET_LANGUAGE and do not mutate a previous state', () => {
        const state: IApplication = { // fake state
            language: 'ru'
        };

        const newLanguage: string = 'en';
        const payload: IByIdPayload = {
            id: newLanguage
        };

        const controlState: IApplication = cloneDeep(state);

        expect(application(state, {
            type: APPLICATION_SET_LANGUAGE,
            payload: payload
        })).toEqual({
            language: newLanguage
        });

        // check for mutations
        expect(isEqual(state, controlState)).toBeTruthy();
    });

    it('should handle APPLICATION_SET_LOCATION', () => {
        const latitude: number = 42.82;
        const longitude: number = 56.74;

        const payload: ILocationPayload = {
            latitude: latitude,
            longitude: longitude
        };

        expect(application(undefined, {
            type: APPLICATION_SET_LOCATION,
            payload: payload
        })).toEqual({
            ...applicationInitialState,
            location: {
                latitude: latitude.toFixed(4),
                longitude: longitude.toFixed(4)
            }
        })
    });

    it('should handle APPLICATION_SET_LOCATION and do not mutate a previous state', () => {
        const state: IApplication = { // fake state
            location: {
                latitude: '42.82',
                longitude: '56.74'
            }
        };

        const controlState: IApplication = cloneDeep(state);

        const latitude: number = 43.82;
        const longitude: number = 57.74;

        const payload: ILocationPayload = {
            latitude: latitude,
            longitude: longitude
        };

        expect(application(state, {
            type: APPLICATION_SET_LOCATION,
            payload: payload
        })).toEqual({
            location: {
                latitude: latitude.toFixed(4),
                longitude: longitude.toFixed(4)
            }
        });

        // check for mutations
        expect(isEqual(state, controlState)).toBeTruthy();
    });

    it('should handle APPLICATION_SET_LANGUAGE_DIRECTION', () => {
        const direction: string = 'rtl';
        const payload: IByIdPayload = {
            id: direction
        };

        expect(application(undefined, {
            type: APPLICATION_SET_LANGUAGE_DIRECTION,
            payload: payload
        })).toEqual({
            ...applicationInitialState,
            languageDirection: direction
        })
    });

    it('should handle APPLICATION_SET_LANGUAGE_DIRECTION and do not mutate a previous state', () => {
        const state: IApplication = { // fake state
            languageDirection: 'rtl'
        };

        const direction: string = 'ltr';
        const payload: IByIdPayload = {
            id: direction
        };
        const controlState: IApplication = cloneDeep(state);

        expect(application(state, {
            type: APPLICATION_SET_LANGUAGE_DIRECTION,
            payload: payload
        })).toEqual({
            languageDirection: direction
        });

        // check for mutations
        expect(isEqual(state, controlState)).toBeTruthy();
    });

    it('getApplicationLanguage should return correct value', () => {
        const language: string = 'en';
        const state: IAppState = { // fake state
            application: {
                language: language
            }
        };

        // fake the method
        spyOn(fakeRedux, 'getState').and.returnValue(state);

        expect(getApplicationLanguage(fakeRedux.getState())).toEqual(language);
    });

    it('getApplicationLanguageDirection should return correct value', () => {
        const languageDirection: string = 'rtl';
        const state: IAppState = { // fake state
            application: {
                languageDirection: languageDirection
            }
        };

        // fake the method
        spyOn(fakeRedux, 'getState').and.returnValue(state);

        expect(getApplicationLanguageDirection(fakeRedux.getState())).toEqual(languageDirection);
    });

    it('getApplicationLocation should return correct value', () => {
        const location: IApplicationLocation = {
            latitude: '42.82',
            longitude: '56.74'
        };

        const state: IAppState = { // fake state
            application: {
                location: location
            }
        };

        // fake the method
        spyOn(fakeRedux, 'getState').and.returnValue(state);

        expect(getApplicationLocation(fakeRedux.getState())).toEqual(location);
    });
    
});
