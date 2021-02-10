import { IMapType } from 'store/types';
import { IMatchAction } from 'store/states';
import { IAppState } from 'store';
import { getUsers } from 'store/reducers';
import omit from 'lodash/omit';
import mapValues from 'lodash/mapValues';
import merge from 'lodash/merge';

import {
    USERS_LOAD,
    BOOKMARKS_LOAD,
    COMPATIBLE_USERS_SET,
    GUESTS_SET, 
    USERS_LOGOUT, 
    APPLICATION_RESET, 
    MATCH_ACTIONS_BEFORE_ADD,
    MATCH_ACTIONS_AFTER_ADD,
    MATCH_ACTIONS_ERROR_ADD,
    MATCH_ACTIONS_AFTER_DELETE
} from 'store/actions';

// payloads
import {
    IWrappedEntitiesPayload,
    IEntitiesPayload,
    IMatchActionDataPayload
} from 'store/payloads';

/**
 * Match actions initial state
 */
export const matchActionsInitialState: IMapType<IMatchAction> = {};

/**
 * Match actions reducer
 */
export const matchActions = (currentState: IMapType<IMatchAction>, action: any): IMapType<IMatchAction> => {
    // add initial state
    if (!currentState) {
        currentState = matchActionsInitialState;
    }

    switch(action.type) {
        case MATCH_ACTIONS_BEFORE_ADD : 
            const matchActionsBeforeAddPayload: IMatchActionDataPayload = action.payload;

            return merge({}, currentState, {
                [matchActionsBeforeAddPayload.id]: {
                    ...action.payload
                }
            });

        case MATCH_ACTIONS_AFTER_ADD :
            const matchActionsAfterAddPayload: IWrappedEntitiesPayload = action.payload;

            // delete the unnecessary relation
            const match = mapValues(matchActionsAfterAddPayload.data.entities.matchAction, matchData => {
                return omit(matchData, ['user']);
            });

            return omit(merge({}, currentState, match), [
                matchActionsAfterAddPayload.id // delete the fake match
            ]);

        case MATCH_ACTIONS_AFTER_DELETE :
            const matchActionsAfterDeletePayload: IMatchActionDataPayload = action.payload;

            return omit(currentState, [
                matchActionsAfterDeletePayload.id
            ]);

        case MATCH_ACTIONS_ERROR_ADD :
            const matchActionsErrorAddPayload: IMatchActionDataPayload = action.payload;

            return omit(currentState, [ // delete fake match
                matchActionsErrorAddPayload.id
            ]);

        case USERS_LOAD :
            const usersLoadPayload: IEntitiesPayload = action.payload;

            if (usersLoadPayload.entities.matchAction) {
                return merge({}, currentState, usersLoadPayload.entities.matchAction);
            }

            return currentState;

        case BOOKMARKS_LOAD :
        case COMPATIBLE_USERS_SET :
        case GUESTS_SET :
            const bookmarksLoadPayload: IEntitiesPayload = action.payload;

            if (bookmarksLoadPayload.entities.matchActions) {
                return merge({}, currentState, bookmarksLoadPayload.entities.matchActions);
            }

            return currentState;

        case USERS_LOGOUT :
        case APPLICATION_RESET :
            return matchActionsInitialState;
    }

    return currentState; 
};

// selectors

export const getMatchActions = (appState: IAppState) => appState.matchActions;

/**
 * Get match
 */
export function getMatch(appState: IAppState, userId: number): IMatchAction | undefined {
    const user = getUsers(appState)[userId];

    if (user && user.matchAction) {
        return getMatchActions(appState)[user.matchAction];
    }
}
