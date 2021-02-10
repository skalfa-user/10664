import { guests, guestsInitialState } from './';
import isEqual from 'lodash/isEqual';
import cloneDeep from 'lodash/cloneDeep';

// payloads
import {
    IEntitiesPayload,
    IByIdPayload
} from 'store/payloads';

// store
import { IAppState } from 'store';

import { 
    IGuestData, 
    IGuests,
    IUser, 
    IAvatarData, 
    IMatchAction 
} from 'store/states';

import { 
    GUESTS_SET, 
    USERS_LOGOUT, 
    APPLICATION_RESET, 
    GUESTS_BEFORE_DELETE,
    GUESTS_AFTER_DELETE,
    GUESTS_ERROR_DELETE,
    GUESTS_BEFORE_MARK_ALL_READ,
    GUESTS_ERROR_MARK_ALL_READ,
    GUESTS_MARK_READ,
    GUESTS_MARK_ALL_NOTIFIED
} from 'store/actions';

// selectors
import {
    isGuestNew,
    getGuestList,
    isGuestListFetched,
    getNewGuestsCount,
    getNotNotifiedGuestsCount,
    IGuestListItem
} from 'store/reducers';

// fakes
import {
    ReduxFake
} from 'test/fake';

describe('Guests reducer', () => {
    // register service's fakes
    let fakeRedux: ReduxFake;

    beforeEach(() => { 
        // init service's fakes
        fakeRedux = new ReduxFake();
    });

    it('should handle initial state', () => {
        expect(guests(undefined, '')).toEqual(guestsInitialState);
    });

    it('should handle GUESTS_MARK_ALL_NOTIFIED and do not mutate a previous state', () => {
        const guestId1: number = 1;
        const guestId2: number = 2;

        const guest1Data: IGuestData = {
            id: guestId1,
            viewed: false
        };

        const guest2Data: IGuestData = {
            id: guestId2,
            viewed: true
        };

        const state: IGuests = { // fake state
            isFetched: true,
            byId: {
                [guestId1]: guest1Data,
                [guestId2]: guest2Data
            },
            allIds: [guestId1, guestId2]
        };

        const controlState: IGuests = cloneDeep(state);;

        expect(guests(state, {
            type: GUESTS_MARK_ALL_NOTIFIED,
            payload: {}
        })).toEqual({
            isFetched: true,
            byId: {
                [guestId1]: {
                    ...guest1Data,
                    _isNotified: true
                },
                [guestId2]: {
                    ...guest2Data,
                    _isNotified: true
                }
            },
            allIds: [guestId1, guestId2]
        });

        // check for mutations
        expect(isEqual(state, controlState)).toBeTruthy();
    });

    it('should handle GUESTS_BEFORE_MARK_ALL_READ and do not mutate a previous state', () => {
        const guestId: number = 1;
        const guestData: IGuestData = {
            id: guestId,
            viewed: false
        };

        const state: IGuests = { // fake state
            isFetched: true,
            byId: {
                [guestId]: guestData
            },
            allIds: [guestId]
        };

        const controlState: IGuests = cloneDeep(state);

        expect(guests(state, {
            type: GUESTS_BEFORE_MARK_ALL_READ,
            payload: {}
        })).toEqual({
            isFetched: true,
            byId: {
                [guestId]: {
                    ...guestData,
                    _isRead: true
                }
            },
            allIds: [guestId]
        });

        // check for mutations
        expect(isEqual(state, controlState)).toBeTruthy();
    });

    it('should handle GUESTS_ERROR_MARK_ALL_READ and do not mutate a previous state', () => {
        const guestId: number = 1;
        const guestData: IGuestData = {
            id: guestId,
            viewed: false,
            _isRead: true
        };

        const state: IGuests = { // fake state
            isFetched: true,
            byId: {
                [guestId]: guestData
            },
            allIds: [guestId]
        };

        const controlState: IGuests = cloneDeep(state);

        expect(guests(state, {
            type: GUESTS_ERROR_MARK_ALL_READ,
            payload: {}
        })).toEqual({
            isFetched: true,
            byId: {
                [guestId]: {
                    ...guestData,
                    _isRead: false
                }
            },
            allIds: [guestId]
        });

        // check for mutations
        expect(isEqual(state, controlState)).toBeTruthy();
    });

    it('should handle GUESTS_SET', () => {
        const guestId: number = 1;

        const payload: IEntitiesPayload = {
            entities: {
                guests: {
                    [guestId]: {
                        id: guestId
                    }
                }
            },
            result: [guestId]
        };

        expect(guests(undefined, {
            type: GUESTS_SET,
            payload: payload
        })).toEqual({
            isFetched: true,
            byId: {
                [guestId]: {
                    id: guestId
                }
            },
            allIds: [guestId]
        });
    });

    it('should handle GUESTS_SET and do not mutate a previous state', () => {
        const guestId: number = 1;

        const guestData: IGuestData = {
            id: guestId,
            viewed: false
        };
    
        const state: IGuests = { // fake state
            isFetched: true,
            byId: {
                [guestId]: guestData
            },
            allIds: [guestId]
        };

        const controlState: IGuests = cloneDeep(state);;

        const payload: IEntitiesPayload = {
            entities: {
                guests: {
                    [guestId]: {
                        ...guestData,
                        viewed: true
                    }
                }
            },
            result: [guestId]
        };

        expect(guests(state, {
            type: GUESTS_SET,
            payload: payload
        })).toEqual({
            isFetched: true,
            byId: {
                [guestId]: {
                    ...guestData,
                    viewed: true
                }
            },
            allIds: [guestId]
        });

        // check for mutations
        expect(isEqual(state, controlState)).toBeTruthy();
    });

    it('GUESTS_SET should clear lost data and merge with old properties', () => {
        const guestId1: number = 1;
        const guestId2: number = 2;
        const guestId3: number = 3;

        const guest1Data: IGuestData = {
            id: guestId1,
            _isHidden: true,
            _isRead: false // this property should stayed after merging
        };

        const guest2Data: IGuestData = {
            id: guestId2
        };

        const guest3Data: IGuestData = {
            id: guestId3
        };

        const state: IGuests = { // fake state
            isFetched: true,
            byId: {
                [guestId1]: guest1Data,
                [guestId2]: guest2Data
            },
            allIds: [guestId1, guestId2]
        };

        const controlState: IGuests = cloneDeep(state);
    
        const payload: IEntitiesPayload = {
            entities: {
                guests: {
                    [guestId3]: guest3Data,
                    [guestId1]: {
                        id: guestId1,
                        viewed: true
                    }
                }
            },
            result: [guestId3, guestId1]
        };

        expect(guests(state, {
            type: GUESTS_SET,
            payload: payload
        })).toEqual({
            isFetched: true,
            byId: {
                [guestId3]: guest3Data,
                [guestId1]: {
                    ...guest1Data,
                    viewed: true
                }
            },
            allIds: [guestId3, guestId1]
        });

        // check for mutations
        expect(isEqual(state, controlState)).toBeTruthy();
    });

    it('GUESTS_SET should reset both _isRead and _isNotified flags if an old guest visit time different from a new one', () => {
        const guestId: number = 1;
        const visitTime: number = 1;

        const guestData: IGuestData = {
            id: guestId,
            visitTimestamp: visitTime,
            _isRead: true,
            _isNotified: true
        };

        const state: IGuests = { // fake state
            isFetched: true,
            byId: {
                [guestId]: guestData
            },
            allIds: [guestId]
        };

        const controlState: IGuests = cloneDeep(state);
    
        const payload: IEntitiesPayload = {
            entities: {
                guests: {
                    [guestId]: {
                        id: guestId,
                        visitTimestamp: visitTime + 1
                    }
                }
            },
            result: [guestId]
        };

        expect(guests(state, {
            type: GUESTS_SET,
            payload: payload
        })).toEqual({
            isFetched: true,
            byId: {
                [guestId]: {
                    ...guestData,
                    visitTimestamp: visitTime + 1,
                    _isRead: false,
                    _isNotified: false
                }
            },
            allIds: [guestId]
        });

        // check for mutations
        expect(isEqual(state, controlState)).toBeTruthy();
    });

    it('should handle GUESTS_MARK_READ and do not mutate a previous state', () => {
        const guestId: number = 1;
        const guestData: IGuestData = {
            id: guestId
        };

        const state: IGuests = { // fake state
            isFetched: true,
            byId: {
                [guestId]: guestData
            },
            allIds: [guestId]
        };

        const controlState: IGuests = cloneDeep(state);

        const payload: IByIdPayload = {
            id: guestId
        };

        expect(guests(state, {
            type: GUESTS_MARK_READ,
            payload: payload
        })).toEqual({
            isFetched: true,
            byId: {
                [guestId]: {
                    ...guestData,
                    _isRead: true
                }
            },
            allIds: [guestId]
        });

        // check for mutations
        expect(isEqual(state, controlState)).toBeTruthy();
    });

    it('should handle GUESTS_BEFORE_DELETE and do not mutate a previous state', () => {
        const guestId: number = 1;
        const guestData: IGuestData = {
            id: guestId
        };

        const state: IGuests = { // fake state
            isFetched: true,
            byId: {
                [guestId]: guestData
            },
            allIds: [guestId]
        };

        const controlState: IGuests = cloneDeep(state);

        const payload: IByIdPayload = {
            id: guestId
        };

        expect(guests(state, {
            type: GUESTS_BEFORE_DELETE,
            payload: payload
        })).toEqual({
            isFetched: true,
            byId: {
                [guestId]: {
                    ...guestData,
                    _isHidden: true
                }
            },
            allIds: [guestId]
        });

        // check for mutations
        expect(isEqual(state, controlState)).toBeTruthy();
    });

    it('should handle GUESTS_AFTER_DELETE and do not mutate a previous state', () => {
        const guestId: number = 1;
        const guestData: IGuestData = {
            id: guestId
        };

        const state: IGuests = { // fake state
            isFetched: true,
            byId: {
                [guestId]: guestData
            },
            allIds: [guestId]
        };

        const controlState: IGuests = cloneDeep(state);
    
        const payload: IByIdPayload = {
            id: guestId
        };

        expect(guests(state, {
            type: GUESTS_AFTER_DELETE,
            payload: payload
        })).toEqual({
            isFetched: true,
            byId: {},
            allIds: []
        });

        // check for mutations
        expect(isEqual(state, controlState)).toBeTruthy();
    });
 
    it('should handle GUESTS_ERROR_DELETE and do not mutate a previous state', () => {
        const guestId: number = 1;
        const guestData: IGuestData = {
            id: guestId
        };

        const state: IGuests = { // fake state
            isFetched: true,
            byId: {
                [guestId]: guestData
            },
            allIds: [guestId]
        };

        const controlState: IGuests = cloneDeep(state);

        const payload: IByIdPayload = {
            id: guestId
        };

        expect(guests(state, {
            type: GUESTS_ERROR_DELETE,
            payload: payload
        })).toEqual({
            isFetched: true,
            byId: {
                [guestId]: {
                    ...guestData,
                    _isHidden: false
                }
            },
            allIds: [guestId]
        });

        // check for mutations
        expect(isEqual(state, controlState)).toBeTruthy();
    });

    it('should handle USERS_LOGOUT', () => {
        expect(guests(undefined, {
            type: USERS_LOGOUT
        })).toEqual(guestsInitialState)
    });

    it('should handle APPLICATION_RESET', () => {
        expect(guests(undefined, {
            type: APPLICATION_RESET
        })).toEqual(guestsInitialState)
    });

    it('getNotNotifiedGuestsCount should return correct value', () => {
        const guestId: number = 1;
        const guestData: IGuestData = {
            id: guestId,
            viewed: false
        };

        const state: IAppState = { // fake state
            guests: {
                byId: {
                    [guestId]: guestData
                },
                allIds: [guestId]
            }
        };

        // fake the method
        spyOn(fakeRedux, 'getState').and.returnValue(state);

        expect(getNotNotifiedGuestsCount()(fakeRedux.getState())).toEqual(1);
    });

    it('getNotNotifiedGuestsCount should not return any value if guest list marked as notified', () => {
        const guestId: number = 1;
        const guestData: IGuestData = {
            id: guestId,
            viewed: false,
            _isNotified: true
        };

        const state: IAppState = { // fake state
            guests: {
                byId: {
                    [guestId]: guestData
                },
                allIds: [guestId]
            }
        };

        // fake the method
        spyOn(fakeRedux, 'getState').and.returnValue(state);

        expect(getNotNotifiedGuestsCount()(fakeRedux.getState())).toEqual(0);
    });

    it('getNotNotifiedGuestsCount should return 0 of guests list is empty', () => {
        const state: IAppState = { // fake state
            guests: {
                byId: {
                },
                allIds: []
            }
        };

        // fake the method
        spyOn(fakeRedux, 'getState').and.returnValue(state);

        expect(getNotNotifiedGuestsCount()(fakeRedux.getState())).toEqual(0);
    });
 
    it('getNewGuestsCount should return correct value', () => {
        const guestId: number = 1;
        const guestData: IGuestData = {
            id: guestId,
            viewed: false
        };

        const state: IAppState = { // fake state
            guests: {
                byId: {
                    [guestId]: guestData
                },
                allIds: [guestId]
            }
        };

        // fake the method
        spyOn(fakeRedux, 'getState').and.returnValue(state);

        expect(getNewGuestsCount()(fakeRedux.getState())).toEqual(1);
    });

    it('getNewGuestsCount should not return any value if guest list marked as read', () => {
        const guestId: number = 1;
        const guestData: IGuestData = {
            id: guestId,
            viewed: false,
            _isRead: true
        };

        const state: IAppState = { // fake state
            guests: {
                byId: {
                    [guestId]: guestData
                },
                allIds: [guestId]
            }
        };

        // fake the method
        spyOn(fakeRedux, 'getState').and.returnValue(state);

        expect(getNewGuestsCount()(fakeRedux.getState())).toEqual(0);
    });

    it('getNewGuestsCount should return 0 if guests list is empty', () => {
        const state: IAppState = { // fake state
            guests: {
                byId: {
                },
                allIds: []
            }
        };

        // fake the method
        spyOn(fakeRedux, 'getState').and.returnValue(state);

        expect(getNewGuestsCount()(fakeRedux.getState())).toEqual(0);
    });

    it('isGuestListFetched should return a positive boolean value if list loaded', () => {
        const state: IAppState = { // fake state
            guests: {
                isFetched: true
            }
        };

        // fake the method
        spyOn(fakeRedux, 'getState').and.returnValue(state);

        expect(isGuestListFetched(fakeRedux.getState())).toBeTruthy();
    });

    it('isGuestListFetched should return a negative boolean value if list not loaded', () => {
        const state: IAppState = { // fake state
            guests: {
                isFetched: false
            }
        };

        // fake the method
        spyOn(fakeRedux, 'getState').and.returnValue(state);

        expect(isGuestListFetched(fakeRedux.getState())).toBeFalsy();
    });

    it('getGuestList should return correct value and skipping hidden guests', () => {
        const guestId1: number = 1;
        const userId1: number = 1;
        const avatarId1: number = 1;
        const matchActionId1: number = 1;

        const user1: IUser = {
            id: userId1,
            avatar: avatarId1,
            matchAction: matchActionId1
        };

        const avatar1: IAvatarData = {
            id: avatarId1,
            userId: userId1
        };

        const matchAction1: IMatchAction = {
            id: matchActionId1
        };

        const guest1: IGuestData = {
            id: guestId1,
            user: userId1
        }

        const guestId2: number = 2;
        const userId2: number = 2;

        const guest2: IGuestData = {
            id: guestId2,
            user: userId2,
            _isHidden: true
        }

        const user2: IUser = {
            id: userId2
        };

        const state: IAppState = { // fake state
            users: { 
                [userId1] : user1,
                [userId2] : user2
            },
            avatars: {
                active: {
                    [avatarId1]: avatar1
                }
            },
            matchActions: {
                [matchActionId1]: matchAction1
            },
            guests: {
                byId: {
                    [guestId1]: guest1,
                    [guestId2]: guest2
                },
                allIds: [guestId1, guestId2]
            }
        };

        // fake the method
        spyOn(fakeRedux, 'getState').and.returnValue(state);

        expect(getGuestList()(fakeRedux.getState())).toEqual([{
            guest: guest1,
            avatar: avatar1,
            user: user1,
            matchAction: matchAction1
        }]);
    });

    it('getGuestList should return an undefined value if user list empty', () => {
        const state: IAppState = { // fake state
            users: { 
            },
            avatars: {
            },
            matchActions: {
            },
            guests: {
                byId: {
                },
                allIds: []
            }
        };

        // fake the method
        spyOn(fakeRedux, 'getState').and.returnValue(state);

        expect(getGuestList()(fakeRedux.getState())).toBeUndefined();
    });

    it('isGuestNew should return a positive boolean value if there is no local variable _isRead and the "viewed" property equals to false', () => {
        const guestId: number = 1;
 
        const guestData: IGuestData = {
            id: guestId,
            viewed: false
        };

        const guestItem: IGuestListItem = {
            guest: guestData,
            user: undefined, 
            avatar: undefined,
            matchAction: undefined
        };

        expect(isGuestNew(guestItem)).toBeTruthy();
    });

    it('isGuestNew should return a negative boolean value if there is no local variable _isRead and the "viewed" property equals to true', () => {
        const guestId: number = 1;
 
        const guestData: IGuestData = {
            id: guestId,
            viewed: true
        };

        const guestItem: IGuestListItem = {
            guest: guestData,
            user: undefined, 
            avatar: undefined,
            matchAction: undefined
        };

        expect(isGuestNew(guestItem)).toBeFalsy();
    });

    it('isGuestNew should return a negative boolean value if there is a local variable _isRead equals to true, using the "viewed" property should be avoided', () => {
        const guestId: number = 1;
 
        const guestData: IGuestData = {
            id: guestId,
            viewed: false,
            _isRead: true
        };

        const guestItem: IGuestListItem = {
            guest: guestData,
            user: undefined, 
            avatar: undefined,
            matchAction: undefined
        };

        expect(isGuestNew(guestItem)).toBeFalsy();
    });

    it('isGuestNew should return a positive boolean value if there is a local variable _isRead equals to false, using the "viewed" property should be avoided', () => {
        const guestId: number = 1;
 
        const guestData: IGuestData = {
            id: guestId,
            viewed: true,
            _isRead: false
        };

        const guestItem: IGuestListItem = {
            guest: guestData,
            user: undefined, 
            avatar: undefined,
            matchAction: undefined
        };

        expect(isGuestNew(guestItem)).toBeTruthy();
    });
});
