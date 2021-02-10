import { videoImNotifications, videoImNotificationsInitialState } from './';
import cloneDeep from 'lodash/cloneDeep';
import isEqual from 'lodash/isEqual';

// payloads
import { 
    IByIdPayload, 
    IEntitiesPayload,
    IVideoImActiveInterlocutorDataPayload
} from 'store/payloads';

// store
import { IAppState } from 'store';
import { IMapType } from 'store/types';

import {
    IVideoImActiveInterlocutorData,
    IVideoImNotificationData,
    IVideoImNotifications
} from 'store/states';

import {
    VIDEO_IM_ADD_NOTIFICATION,
    VIDEO_IM_BEFORE_MARK_NOTIFICATION,
    VIDEO_IM_AFTER_MARK_NOTIFICATION,
    VIDEO_IM_ERROR_MARK_NOTIFICATION,
    VIDEO_IM_SET_ACTIVE_INTERLOCUTOR_DATA,
    USERS_LOGOUT,
    APPLICATION_RESET
} from 'store/actions';

// selectors
import {
    getFirstCallData,
    getActiveInterlocutorData,
    getSessionId,
    getActiveInterlocutorNotifications
} from 'store/reducers';

// fakes
import {
    ReduxFake
} from 'test/fake';

describe('Video im notifications reducer', () => {
// register service's fakes
    let fakeRedux: ReduxFake;

    beforeEach(() => {
        // init service's fakes
        fakeRedux = new ReduxFake();
    });

    it('should handle initial state', () => {
        expect(videoImNotifications(undefined, '')).toEqual(videoImNotificationsInitialState);
    });

    it('should handle VIDEO_IM_ADD_NOTIFICATION', () => {
        const notificationId: number = 1;

        const payload: IEntitiesPayload = {
            entities: {
                notifications: {
                    [notificationId]: {
                        id: notificationId
                    }
                }
            },
            result: [notificationId]
        };

        expect(videoImNotifications(undefined, {
            type: VIDEO_IM_ADD_NOTIFICATION,
            payload: payload
        })).toEqual({
            byId: {
                [notificationId]: {
                    id: notificationId
                }
            },
            allIds: [notificationId],
            activeSessionIds: {},
            activeInterlocutorData: {
                userId: 0,
                isMeInitiator: false
            }
        });
    });

    it('should handle VIDEO_IM_ADD_NOTIFICATION with unique notification ids', () => {
        const notificationId: number = 1;

        const videoImNotificationData1: IVideoImNotificationData = {
            id: notificationId,
            _isMarked: true
        };

        const videoImNotificationData2: IVideoImNotificationData = {
            id: notificationId
        };

        const state: IVideoImNotifications = { // fake state
            byId: {
                [notificationId]: videoImNotificationData1
            },
            allIds: [notificationId]
        };

        const payload: IEntitiesPayload = {
            entities: {
                notifications: {
                    [notificationId]: videoImNotificationData2
                }
            },
            result: [notificationId]
        };

        expect(videoImNotifications(state, {
            type: VIDEO_IM_ADD_NOTIFICATION,
            payload: payload
        })).toEqual({
            byId: {
                [notificationId]: videoImNotificationData1
            },
            allIds: [notificationId]
        });
    });

    it('should handle VIDEO_IM_BEFORE_MARK_NOTIFICATION and do not mutate a previous state', () => {
        const notificationId: number = 1;
        const userId: number = 2;

        const videoImNotificationData: IVideoImNotificationData = {
            id: notificationId,
            user: userId
        };

        const state: IVideoImNotifications = { // fake state
            byId: {
                [notificationId]: videoImNotificationData
            },
            allIds: [notificationId]
        };

        const controlState: IVideoImNotifications = cloneDeep(state);

        const payload: IByIdPayload = {
            id: userId
        };

        expect(videoImNotifications(state, {
            type: VIDEO_IM_BEFORE_MARK_NOTIFICATION,
            payload: payload
        })).toEqual({
            byId: {
                [notificationId]: {
                    ...videoImNotificationData,
                    _isMarked: true
                }
            },
            allIds: [notificationId]
        });

        // check for mutations
        expect(isEqual(state, controlState)).toBeTruthy();
    });

    it('should handle VIDEO_IM_AFTER_MARK_NOTIFICATION and do not mutate a previous state', () => {
        const notificationId: number = 1;
        const userId: number = 1;

        const videoImNotificationData: IVideoImNotificationData = {
            id: notificationId,
            user: userId,
            _isMarked: true
        };

        const state: IVideoImNotifications = { // fake state
            byId: {
                [notificationId]: videoImNotificationData
            },
            allIds: [notificationId]

        };

        const controlState: IVideoImNotifications = cloneDeep(state);

        const payload: IByIdPayload = {
            id: userId
        };

        expect(videoImNotifications(state, {
            type: VIDEO_IM_AFTER_MARK_NOTIFICATION,
            payload: payload
        })).toEqual({
            byId: {},
            allIds: []
        });

        // check for mutations
        expect(isEqual(state, controlState)).toBeTruthy();
    });

    it('should handle VIDEO_IM_ERROR_MARK_NOTIFICATION and do not mutate a previous state', () => {
        const notificationId: number = 1;
        const userId: number = 2;

        const videoImNotificationData: IVideoImNotificationData = {
            id: notificationId,
            user: userId,
            _isMarked: false
        };

        const state: IVideoImNotifications = { // fake state
            byId: {
                [notificationId]: videoImNotificationData
            },
            allIds: [notificationId]
        };

        const controlState: IVideoImNotifications = cloneDeep(state);

        const payload: IByIdPayload = {
            id: userId
        };

        expect(videoImNotifications(state, {
            type: VIDEO_IM_ERROR_MARK_NOTIFICATION,
            payload: payload
        })).toEqual({
            byId: {
                [notificationId]: {
                    ...videoImNotificationData,
                    _isMarked: false
                }
            },
            allIds: [notificationId]
        });

        // check for mutations
        expect(isEqual(state, controlState)).toBeTruthy();
    });

    it('should handle VIDEO_IM_SET_ACTIVE_INTERLOCUTOR_DATA and do not mutate a previous state', () => {
        const activeInterlocutorData: IVideoImActiveInterlocutorData = {
            userId: 1,
            isMeInitiator: true
        };

        const state: IVideoImNotifications = { // fake state
            byId: {
            },
            allIds: [],
            activeInterlocutorData: {
                userId: 0,
                isMeInitiator: false
            }
        };

        const controlState: IVideoImNotifications = cloneDeep(state);
        const payload: IVideoImActiveInterlocutorDataPayload = activeInterlocutorData;

        expect(videoImNotifications(state, {
            type: VIDEO_IM_SET_ACTIVE_INTERLOCUTOR_DATA,
            payload: payload
        })).toEqual({
            byId: {
            },
            allIds: [],
            activeInterlocutorData: activeInterlocutorData
        });

        // check for mutations
        expect(isEqual(state, controlState)).toBeTruthy();
    });

    it('should handle USERS_LOGOUT', () => {
        expect(videoImNotifications(undefined, {
            type: USERS_LOGOUT
        })).toEqual(videoImNotificationsInitialState)
    });

    it('should handle APPLICATION_RESET', () => {
        expect(videoImNotifications(undefined, {
            type: APPLICATION_RESET
        })).toEqual(videoImNotificationsInitialState)
    });

    it('getFirstCallData should return first calling user id if it is not active caller one', () => {
        const userId1: number = 1;
        const userId2: number = 2;
        const sessionId1: string = 'abcde';
        const sessionId2: string = 'qwerty';

        const notificationId1: number = 1;
        const notificationId2: number = 2;
        const notificationId3: number = 3;
        const notificationId4: number = 4;

        const notification1: IVideoImNotificationData = {
            id: notificationId1,
            user: userId1,
            sessionId: sessionId1,
            type: 'offer',
        };

        const notification2: IVideoImNotificationData = {
            id: notificationId2,
            user: userId1,
            sessionId: sessionId1,
            type: 'candidate'
        };

        // this notification should be used
        const notification3: IVideoImNotificationData = {
            id: notificationId3,
            user: userId2,
            sessionId: sessionId2,
            type: 'offer'
        };

        const notification4: IVideoImNotificationData = {
            id: notificationId4,
            user: userId2,
            sessionId: sessionId2,
            type: 'candidate'
        };

        const activeInterlocutorData: IVideoImActiveInterlocutorData = {
            userId: userId1,
            isMeInitiator: false
        };

        const state: IAppState = { // fake state
            videoImNotifications: {
                byId: {
                    [notificationId1]: notification1,
                    [notificationId2]: notification2,
                    [notificationId3]: notification3,
                    [notificationId4]: notification4
                },
                allIds: [
                    notificationId1,
                    notificationId2,
                    notificationId3,
                    notificationId4,
                ],
                activeSessionIds: {},
                activeInterlocutorData: activeInterlocutorData
            }
        };

        // fake the method
        spyOn(fakeRedux, 'getState').and.returnValue(state);

        expect(getFirstCallData()(fakeRedux.getState())).toEqual({
            userId: userId2,
            sessionId: sessionId2
        });
    });

    it('getFirstCallData should return first calling user id and skipping accepted ones', () => {
        const userId1: number = 1;
        const userId2: number = 2;
        const userId3: number = 3;
        const sessionId1: string = 'abcde';
        const sessionId2: string = 'qwerty';
        const sessionId3: string = 'dsfghrd';

        const notificationId1: number = 1;
        const notificationId2: number = 2;
        const notificationId3: number = 3;
        const notificationId4: number = 4;
        const notificationId5: number = 5;
        const notificationId6: number = 6;

        const notification1: IVideoImNotificationData = {
            id: notificationId1,
            user: userId1,
            sessionId: sessionId1,
            type: 'offer',
            _isMarked: true
        };

        const notification2: IVideoImNotificationData = {
            id: notificationId2,
            user: userId1,
            sessionId: sessionId1,
            type: 'candidate',
            _isMarked: true
        };

        // this notification should be used
        const notification3: IVideoImNotificationData = {
            id: notificationId3,
            user: userId2,
            sessionId: sessionId2,
            type: 'offer',
            _isMarked: false
        };

        const notification4: IVideoImNotificationData = {
            id: notificationId4,
            user: userId3,
            sessionId: sessionId3,
            type: 'candidate',
            _isMarked: false
        };

        const notification5: IVideoImNotificationData = {
            id: notificationId5,
            user: userId2,
            sessionId: sessionId2,
            type: 'offer',
            _isMarked: false
        };

        const notification6: IVideoImNotificationData = {
            id: notificationId6,
            user: userId3,
            sessionId: sessionId3,
            type: 'candidate',
            _isMarked: false
        };

        const activeInterlocutorData: IVideoImActiveInterlocutorData = {
            userId: userId1,
            isMeInitiator: false
        };

        const state: IAppState = { // fake state
            videoImNotifications: {
                byId: {
                    [notificationId1]: notification1,
                    [notificationId2]: notification2,
                    [notificationId3]: notification3,
                    [notificationId4]: notification4,
                    [notificationId5]: notification5,
                    [notificationId6]: notification6
                },
                allIds: [
                    notificationId1,
                    notificationId2,
                    notificationId3,
                    notificationId4,
                    notificationId5,
                    notificationId6
                ],
                activeSessionIds: {},
                activeInterlocutorData: activeInterlocutorData
            }
        };

        // fake the method
        spyOn(fakeRedux, 'getState').and.returnValue(state);

        expect(getFirstCallData()(fakeRedux.getState())).toEqual({
            userId: userId2,
            sessionId: sessionId2
        });
    });

    it('getFirstCallData should return an undefined value if notification list empty', () => {
        const state: IAppState = { // fake state
            videoImNotifications: {
                byId: {
                },
                allIds: [],
                activeSessionIds: {},
                activeInterlocutorData: {
                    userId: 0,
                    isMeInitiator: false
                }
            }
        };

        // fake the method
        spyOn(fakeRedux, 'getState').and.returnValue(state);

        expect(getFirstCallData()(fakeRedux.getState())).toBeUndefined();
    });

    it('getActiveInterlocutorData should return correct value', () => {
        const state: IAppState = { // fake state
            videoImNotifications: {
                byId: {},
                allIds: [],
                activeSessionIds: {},
                activeInterlocutorData: {
                    userId: 1,
                    isMeInitiator: true
                }
            }
        };

        // fake the method
        spyOn(fakeRedux, 'getState').and.returnValue(state);

        expect(getActiveInterlocutorData(fakeRedux.getState())).toEqual({
            userId: 1,
            isMeInitiator: true
        });
    });

    it('getSessionId should return correct value', () => {
        const userId: number = 1;
        const sessionId: string = 'qwerty';
        const state: IAppState = { // fake state
            videoImNotifications: {
                byId: {},
                allIds: [],
                activeSessionIds: {
                    1: 'qwerty'
                },
                activeInterlocutorData: {
                    userId: 1,
                    isMeInitiator: true
                }
            }
        };

        // fake the method
        spyOn(fakeRedux, 'getState').and.returnValue(state);

        expect(getSessionId(fakeRedux.getState(), userId)).toEqual(sessionId);
    });

    it('getSessionId should return an undefined value if session for user doesnt exist', () => {
        const userId: number = 2;
        const state: IAppState = { // fake state
            videoImNotifications: {
                byId: {},
                allIds: [],
                activeSessionIds: {
                    1: 'qwerty'
                },
                activeInterlocutorData: {
                    userId: 1,
                    isMeInitiator: true
                }
            }
        };

        // fake the method
        spyOn(fakeRedux, 'getState').and.returnValue(state);

        expect(getSessionId(fakeRedux.getState(), userId)).toBeUndefined();
    });

    it('getActiveInterlocutorNotifications should return only active caller notifications by active session id', () => {
        const userId1: number = 1;
        const userId2: number = 2;
        const userId3: number = 3;
        const sessionId1: string = 'werwere';
        const sessionId2: string = 'fsgdsfbsd';
        const sessionId3: string = 'sdafsadf';

        const notificationId1: number = 1;
        const notificationId2: number = 2;
        const notificationId3: number = 3;
        const notificationId4: number = 4;

        const notification1: IVideoImNotificationData = {
            id: notificationId1,
            user: userId1,
            sessionId: sessionId1,
            type: 'candidate'
        };

        const notification2: IVideoImNotificationData = {
            id: notificationId2,
            user: userId2,
            sessionId: sessionId2,
            type: 'candidate'
        };

        // next two notifications are valid for the test case
        const notification3: IVideoImNotificationData = {
            id: notificationId3,
            user: userId3,
            sessionId: sessionId3,
            type: 'candidate'
        };

        const notification4: IVideoImNotificationData = {
            id: notificationId4,
            user: userId3,
            sessionId: sessionId3,
            type: 'candidate'
        };

        const activeSessionIds: IMapType<string> = {};
        activeSessionIds[userId3] = sessionId3;

        const activeInterlocutorData: IVideoImActiveInterlocutorData = {
            userId: userId3,
            isMeInitiator: false
        };

        const state: IAppState = { // fake state
            videoImNotifications: {
                byId: {
                    [notificationId1]: notification1,
                    [notificationId2]: notification2,
                    [notificationId3]: notification3,
                    [notificationId4]: notification4
                },
                allIds: [
                    notificationId1,
                    notificationId2,
                    notificationId3,
                    notificationId4,
                ],
                activeSessionIds: activeSessionIds,
                activeInterlocutorData: activeInterlocutorData
            }
        };

        // fake the method
        spyOn(fakeRedux, 'getState').and.returnValue(state);

        expect(getActiveInterlocutorNotifications()(fakeRedux.getState())).toEqual([notification3, notification4]);
    });

    it('getActiveInterlocutorNotifications should return both "offer" and "candidate" notifications by inactive session id', () => {
        const userId1: number = 1;
        const userId2: number = 2;
        const sessionId1: string = 'werwere';
        const sessionId2: string = 'fsgdsfbsd';

        const notificationId1: number = 1;
        const notificationId2: number = 2;
        const notificationId3: number = 3;
        const notificationId4: number = 4;

        const notification1: IVideoImNotificationData = {
            id: notificationId1,
            user: userId1,
            sessionId: sessionId1,
            type: 'candidate'
        };

        const notification2: IVideoImNotificationData = {
            id: notificationId2,
            user: userId1,
            sessionId: sessionId1,
            type: 'candidate'
        };

        // next two notifications are valid for the test case
        const notification3: IVideoImNotificationData = {
            id: notificationId3,
            user: userId2,
            sessionId: sessionId2,
            type: 'offer'
        };

        const notification4: IVideoImNotificationData = {
            id: notificationId4,
            user: userId2,
            sessionId: sessionId2,
            type: 'candidate'
        };

        const activeSessionIds: IMapType<string> = {};
        activeSessionIds[userId2] = sessionId2;

        const activeInterlocutorData: IVideoImActiveInterlocutorData = {
            userId: userId2,
            isMeInitiator: false
        };

        const state: IAppState = { // fake state
            videoImNotifications: {
                byId: {
                    [notificationId1]: notification1,
                    [notificationId2]: notification2,
                    [notificationId3]: notification3,
                    [notificationId4]: notification4
                },
                allIds: [
                    notificationId1,
                    notificationId2,
                    notificationId3,
                    notificationId4,
                ],
                activeSessionIds: activeSessionIds,
                activeInterlocutorData: activeInterlocutorData
            }
        };

        // fake the method
        spyOn(fakeRedux, 'getState').and.returnValue(state);

        expect(getActiveInterlocutorNotifications()(fakeRedux.getState())).toEqual([notification3, notification4]);
    });

    it('getActiveInterlocutorNotifications should return undefined if there are no notifications for active caller', () => {
        const userId1: number = 1;
        const userId2: number = 2;
        const sessionId1: string = 'werwere';
        const sessionId2: string = 'fsgdsfbsd';

        const notificationId1: number = 1;

        const notification1: IVideoImNotificationData = {
            id: notificationId1,
            user: userId1,
            sessionId: sessionId1,
            type: 'candidate'
        };

        const activeSessionIds: IMapType<string> = {};
        activeSessionIds[sessionId2] = sessionId2;

        const activeInterlocutorData: IVideoImActiveInterlocutorData = {
            userId: userId2,
            isMeInitiator: false
        };

        const state: IAppState = { // fake state
            videoImNotifications: {
                byId: {
                    [notificationId1]: notification1
                },
                allIds: [
                    notificationId1
                ],
                activeSessionIds: activeSessionIds,
                activeInterlocutorData: activeInterlocutorData
            }
        };

        // fake the method
        spyOn(fakeRedux, 'getState').and.returnValue(state);

        expect(getActiveInterlocutorNotifications()(fakeRedux.getState())).toBeUndefined();
    });
});
