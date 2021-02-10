import { IAppState } from 'store';
import { createSelector } from 'reselect'

import omit from 'lodash/omit';
import omitBy from 'lodash/omitBy';
import uniq from 'lodash/uniq';
import merge from 'lodash/merge';
import forOwn from 'lodash/forOwn';
import mapValues from 'lodash/mapValues';

// states
import {
    IVideoImActiveInterlocutorData,
    IVideoImNotificationData,
    IVideoImNotifications
} from 'store/states';

// actions
import {
    USERS_LOGOUT,
    APPLICATION_RESET,
    VIDEO_IM_ADD_NOTIFICATION,
    VIDEO_IM_BEFORE_MARK_NOTIFICATION,
    VIDEO_IM_AFTER_MARK_NOTIFICATION,
    VIDEO_IM_ERROR_MARK_NOTIFICATION,
    VIDEO_IM_SET_ACTIVE_INTERLOCUTOR_DATA,
    VIDEO_IM_SET_INTERLOCUTOR_SESSION_ID,
    VIDEO_IM_REMOVE_INTERLOCUTOR_SESSION_ID
} from 'store/actions';

// payloads
import { 
    IByIdPayload, 
    IEntitiesPayload,
    IVideoImActiveInterlocutorDataPayload,
    IVideoImCallDataPayload
} from 'store/payloads';

/**
 * Video im notifications initial state
 */
export const videoImNotificationsInitialState: IVideoImNotifications = {
    byId: {},
    allIds: [],
    activeSessionIds: {},
    activeInterlocutorData: { userId: 0, isMeInitiator: false }
};

/**
 * Video im notifications reducer
 */
export const videoImNotifications = (currentState: IVideoImNotifications, action: any): IVideoImNotifications => {
    // add initial state
    if (!currentState) {
        currentState = videoImNotificationsInitialState;
    }

    switch(action.type) {
        case VIDEO_IM_ADD_NOTIFICATION :
            const videoImNotificationPayload: IEntitiesPayload = action.payload;

            return {
                ...currentState,
                byId: videoImNotificationPayload.result.length && videoImNotificationPayload.entities.notifications
                    ? merge({}, currentState.byId, mapValues(videoImNotificationPayload.entities.notifications, notification => omit(notification, ['avatar'])))
                    : currentState.byId,
                allIds: videoImNotificationPayload.result.length
                    ? uniq([...currentState.allIds, ...videoImNotificationPayload.result])
                    : currentState.allIds
            };

        case VIDEO_IM_BEFORE_MARK_NOTIFICATION :
            const videoImBeforeMarkNotificationPayload: IByIdPayload = action.payload;
            const beforeMarkedNotifications = {};

            // add the additional flag
            forOwn(currentState.byId, (value, key) => {
                if (value.user == videoImBeforeMarkNotificationPayload.id) {
                    beforeMarkedNotifications[key] = {
                        ...value,
                        _isMarked: true
                    };
                }
            });
            
            return {
                ...currentState,
                byId:  merge({}, currentState.byId, beforeMarkedNotifications)
            };

        case VIDEO_IM_AFTER_MARK_NOTIFICATION :
            const videoImAfterMarkNotificationPayload: IByIdPayload = action.payload;

            return {
                ...currentState,
                byId: omitBy(currentState.byId, (value: IVideoImNotificationData, key: string) => {
                    return value.user == videoImAfterMarkNotificationPayload.id && value._isMarked === true;
                }),
                allIds: currentState.allIds.filter((notificationId: number) => {
                    const notification: IVideoImNotificationData = currentState.byId[notificationId];

                    return notification && notification._isMarked !== true;
                })
            };

        case VIDEO_IM_ERROR_MARK_NOTIFICATION :
            const videoImErrorMarkNotificationPayload: IByIdPayload = action.payload;
            const errorMarkedNotifications = {};

            // add the additional flag
            forOwn(currentState.byId, (value, key) => {
                if (value.user == videoImErrorMarkNotificationPayload.id) {
                    errorMarkedNotifications[key] = {
                        ...value,
                        _isMarked: false
                    };
                }
            });
            
            return {
                ...currentState,
                byId:  merge({}, currentState.byId, errorMarkedNotifications)
            };

        case VIDEO_IM_SET_ACTIVE_INTERLOCUTOR_DATA :
            const videoImSetActiveDataPayload: IVideoImActiveInterlocutorDataPayload = action.payload;

            return {
                ...currentState,
                activeInterlocutorData: videoImSetActiveDataPayload
            };

        case VIDEO_IM_SET_INTERLOCUTOR_SESSION_ID :
            const videoImSetInterlocutorDataPayload: IVideoImCallDataPayload = action.payload;

            currentState.activeSessionIds[videoImSetInterlocutorDataPayload.userId] = videoImSetInterlocutorDataPayload.sessionId;

            return {
                ...currentState
            };

        case VIDEO_IM_REMOVE_INTERLOCUTOR_SESSION_ID :
            const videoImRemoveInterlocutorDataPayload: IByIdPayload = action.payload;

            return {
                ...currentState,
                activeSessionIds: omit(currentState.activeSessionIds, [videoImRemoveInterlocutorDataPayload.id])
            };

        case USERS_LOGOUT :
        case APPLICATION_RESET :
            return videoImNotificationsInitialState;
    }
 
    return currentState; 
};

// selectors

export const getVideoImNotifications = (appState: IAppState) => appState.videoImNotifications;

/**
 * Get first calli data
 */
export function getFirstCallData(): Function {
    return createSelector(
        [getVideoImNotifications],
        (notifications): { userId: number, sessionId: string } => {
            if (notifications.allIds.length) {
                const notificationId: number = notifications.allIds.find((notificationId) => {
                    const notification = notifications.byId[notificationId];

                    if (notification) {
                        // return only offer notification id if it's not accepted
                        if (notification.type === 'offer' &&
                            notification.user !== notifications.activeInterlocutorData.userId &&
                            notification._isMarked !== true) {

                            return true;
                        }
                    }
                });

                if (notificationId) {
                    return {
                        userId: notifications.byId[notificationId].user,
                        sessionId: notifications.byId[notificationId].sessionId
                    };
                }
            }
        });
}

/**
 * Get active interlocutor data
 */
export function getActiveInterlocutorData(appState: IAppState): IVideoImActiveInterlocutorData {
    return getVideoImNotifications(appState).activeInterlocutorData;
}

/**
 * Get session id
 */
export function getSessionId(appState: IAppState, userId: number): string | undefined {
    return getVideoImNotifications(appState).activeSessionIds[userId];
}

/**
 * Get active interlocutor notifications
 */
export function getActiveInterlocutorNotifications(): Function {
    return createSelector(
        [getVideoImNotifications],
        (notifications): Array<IVideoImNotificationData> | undefined => {
            if (notifications.allIds.length) {
                const notificationData: Array<IVideoImNotificationData> = [];

                notifications.allIds.forEach((notificationId) => {
                    const notification = notifications.byId[notificationId];
                    const activeSessionId = notifications.activeSessionIds[notification.user];

                    if (notification) {
                        // We pass only notification whose session ID is equal to active caller's one OR it is not, but its type are 'offer' or 'candidate'
                        if (notification.user === notifications.activeInterlocutorData.userId && notification._isMarked !== true &&
                            (notification.sessionId === activeSessionId || (notification.sessionId !== activeSessionId && (notification.type === 'offer' || notification.type === 'candidate')))) {

                            notificationData.push(notification);
                        }
                    }
                });

                if (notificationData.length) {
                    return notificationData;
                }
            }
        });
}
