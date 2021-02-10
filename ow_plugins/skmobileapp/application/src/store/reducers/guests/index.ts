import { IGuests, IGuestData, IAvatarData, IUser, IMatchAction } from 'store/states';
import { IAppState } from 'store';
import { getActiveAvatars, getUsers, getMatchActions, isAvatarVisible } from 'store/reducers';
import { createSelector } from 'reselect'
import merge from 'lodash/merge';
import omit from 'lodash/omit';
import pick from 'lodash/pick';
import mapValues from 'lodash/mapValues';

import { 
    USERS_LOGOUT, 
    APPLICATION_RESET, 
    GUESTS_SET,
    GUESTS_BEFORE_DELETE,
    GUESTS_AFTER_DELETE,
    GUESTS_ERROR_DELETE,
    GUESTS_BEFORE_MARK_ALL_READ,
    GUESTS_ERROR_MARK_ALL_READ,
    GUESTS_MARK_READ,
    GUESTS_MARK_ALL_NOTIFIED
} from 'store/actions';

// payloads
import {
    IByIdPayload,
    IEntitiesPayload
} from 'store/payloads';

/**
 * Guests initial state
 */
export const guestsInitialState: IGuests = {
    isFetched: false,
    byId: {},
    allIds: []
};

/**
 * Guests reducer
 */
export const guests = (currentState: IGuests, action: any): IGuests => {
    // add initial state
    if (!currentState) {
        currentState = guestsInitialState;
    }

    switch(action.type) {
        case GUESTS_MARK_ALL_NOTIFIED :
            return {
                ...currentState,
                byId: mapValues(currentState.byId, guest => {
                    return {
                        ...guest,
                        _isNotified: true
                    }
                })
            };

        case GUESTS_BEFORE_MARK_ALL_READ :
            return {
                ...currentState,
                byId: mapValues(currentState.byId, guest => {
                    return {
                        ...guest,
                        _isRead: true
                    }
                })
            };

        case GUESTS_ERROR_MARK_ALL_READ :
            return {
                ...currentState,
                byId: mapValues(currentState.byId, guest => {
                    return {
                        ...guest,
                        _isRead: false
                    }
                })
            };

        case GUESTS_MARK_READ :
            const guestsMarkReadPayload: IByIdPayload = action.payload;

            return {
                ...currentState,
                byId: merge({}, currentState.byId, {
                    [guestsMarkReadPayload.id]: {
                        _isRead: true
                    }
                })
            };

        case GUESTS_BEFORE_DELETE : // mark guest as hidden
            const guestsBeforeDeletePayload: IByIdPayload = action.payload;

            return {
                ...currentState,
                byId: merge({}, currentState.byId, {
                    [guestsBeforeDeletePayload.id]: {
                        _isHidden: true
                    }
                })
            };

        case GUESTS_AFTER_DELETE :
            const guestsAfterDeletePayload: IByIdPayload = action.payload;

            return {
                ...currentState,
                byId: omit(currentState.byId, [
                    guestsAfterDeletePayload.id
                ]),
                allIds: currentState.allIds.filter((guestId: number) => guestId !== guestsAfterDeletePayload.id)
            };

        case GUESTS_ERROR_DELETE : // mark guest as visible
            const guestsErrorDeletePayload: IByIdPayload = action.payload;

            return {
                ...currentState,
                byId: merge({}, currentState.byId, {
                    [guestsErrorDeletePayload.id]: {
                        _isHidden: false
                    }
                })
            };

        case GUESTS_SET :
            const guestsSetPayload: IEntitiesPayload = action.payload;

            const updatable = guestsSetPayload.result.length && guestsSetPayload.entities.guests
                ? pick(currentState.byId, guestsSetPayload.result)
                : {};

            const newGuests = guestsSetPayload.result.length && guestsSetPayload.entities.guests
                ? mapValues(guestsSetPayload.entities.guests, guest => {
                    // reset both _isRead and _isNotified flags
                    if (updatable[guest.id] && guest.visitTimestamp !== updatable[guest.id].visitTimestamp) {
                        return {
                            ...omit(guest, ['avatar', 'matchAction']),
                            _isRead: false,
                            _isNotified: false
                        };
                    }

                    return omit(guest, ['avatar', 'matchAction']);
                })
                : {};

            return {
                isFetched: true,
                byId: guestsSetPayload.result.length && guestsSetPayload.entities.guests
                    ? merge({}, updatable, newGuests)
                    : {},
                allIds: guestsSetPayload.result.length 
                    ? guestsSetPayload.result
                    : []
            };

        case APPLICATION_RESET : // clear all guests data
        case USERS_LOGOUT :  
            return guestsInitialState;
    }

    return currentState; 
};

// selectors

export interface IGuestListItem {
    guest: IGuestData;
    user: IUser;
    avatar: IAvatarData;
    matchAction: IMatchAction
}

export const getGuests = (appState: IAppState) => appState.guests;

/**
 * Is guest new
 */
export function isGuestNew(guestData: IGuestListItem): boolean {
    if (guestData.guest._isRead !== undefined) {
        return !guestData.guest._isRead;
    }

    return !guestData.guest.viewed;
}

/**
 * Get new guests count
 */
export function getNewGuestsCount(): Function {
    return createSelector(
        [getGuests],
        (guests): number => {
            let newGuestCount = 0;

            guests.allIds.forEach((userId: number) => {
                if (!guests.byId[userId].viewed && !guests.byId[userId]._isRead) {
                    newGuestCount++;
                }
            });

            return newGuestCount;
    });
}

/**
 * Get not notified guests count
 */
export function getNotNotifiedGuestsCount(): Function {
    return createSelector(
        [getGuests],
        (guests): number => {
            let notNotifiedCount = 0;

            guests.allIds.forEach((userId: number) => {
                if (!guests.byId[userId].viewed && !guests.byId[userId]._isRead && !guests.byId[userId]._isNotified) {
                    notNotifiedCount++;
                }
            });

            return notNotifiedCount;
    });
}

/**
 * Is guest list fetched
 */
export function isGuestListFetched(appState: IAppState): boolean {   
    return getGuests(appState).isFetched;
}

/**
 * Get guest list
 */
export function getGuestList(): Function {
    return createSelector(
        [getGuests, getActiveAvatars, getUsers, getMatchActions],
        (guests, activeAvatars, users, matchActions): Array<IGuestListItem> | undefined => {
            if (guests.allIds.length) {
                const guestList = [];

                guests.allIds.forEach((guestId: number) => {
                    const guest = guests.byId[guestId];

                    // skip hidden guests
                    if (guest._isHidden === true) {
                        return;
                    }

                    const user = guest.user ? users[guest.user] : undefined;
                    const matchAction = user && user.matchAction ? matchActions[user.matchAction] : undefined;
                    const avatar = user && user.avatar && activeAvatars[user.avatar] && isAvatarVisible(activeAvatars[user.avatar])
                        ? activeAvatars[user.avatar] 
                        : undefined;

                    guestList.push({
                        guest: guest,
                        avatar: avatar,
                        user: user,
                        matchAction: matchAction
                    });
                });

                return guestList;
            }
    });
}
