import { IApplication, IApplicationLocation } from 'store/states';
import { APPLICATION_SET_LANGUAGE, APPLICATION_SET_LANGUAGE_DIRECTION, APPLICATION_SET_LOCATION } from 'store/actions';
import { IAppState } from 'store';
import merge from 'lodash/merge';

// payloads
import { IByIdPayload, ILocationPayload } from 'store/payloads';

/**
 * Application initial state
 */
export const applicationInitialState: IApplication = {
    language: 'en',
    languageDirection: 'ltr',
    location: {
        latitude: null,
        longitude: null
    }
};

/**
 * Application reducer
 */
export const application = (currentState: IApplication, action: any): IApplication => {
    // add initial state
    if (!currentState) {
        currentState = applicationInitialState;
    }

    switch(action.type) {
        case APPLICATION_SET_LANGUAGE :
            const langPayload: IByIdPayload = action.payload;

            return merge({}, currentState, {
                language: langPayload.id
            });

        case APPLICATION_SET_LANGUAGE_DIRECTION :
            const dirPayload: IByIdPayload = action.payload;

            return merge({}, currentState, {
                languageDirection: dirPayload.id
            });

        case APPLICATION_SET_LOCATION :
            const locationPayload: ILocationPayload = action.payload;

            return merge({}, currentState, {
                location: {
                    latitude: locationPayload.latitude.toFixed(4),
                    longitude: locationPayload.longitude.toFixed(4)
                }
            });
    }

    return currentState; 
};

// selectors

export const getApplication = (appState: IAppState) => appState.application;

/**
 * Get application language
 */
export function getApplicationLanguage(appState: IAppState): string {
    return getApplication(appState).language;
}

/**
 * Get application language direction
 */
export function getApplicationLanguageDirection(appState: IAppState): string {
    return getApplication(appState).languageDirection;
}

/**
 * Get application location
 */
export function getApplicationLocation(appState: IAppState): IApplicationLocation {
    return getApplication(appState).location;
}
