import { normalize } from 'normalizr';
import { MockBackend } from '@angular/http/testing';
import { Http, BaseRequestOptions } from '@angular/http';
import { TestBed } from '@angular/core/testing';
import { Observable } from 'rxjs/Rx';
import { Platform } from 'ionic-angular';

// payloads
import {
    IEntitiesPayload,
    IByIdPayload
} from 'store/payloads';

// services
import { MatchedUsersService } from './'
import { SecureHttpService } from 'services/http';
import { ApplicationService } from 'services/application';
import { PersistentStorageService } from 'services/persistent-storage';
import { JwtService } from 'services/jwt';
import { AuthService } from 'services/auth';

import {
    MATCHED_USERS_BEFORE_MARK_READ,
    MATCHED_USERS_AFTER_MARK_READ,
    MATCHED_USERS_ERROR_MARK_READ,
    MATCHED_USERS_BEFORE_MARK_NOTIFIED,
    MATCHED_USERS_AFTER_MARK_NOTIFIED,
    MATCHED_USERS_ERROR_MARK_NOTIFIED,
    MATCHED_USERS_SET
} from 'store/actions'; 

import { 
    IUser, 
    IAvatarData, 
    IMatchedUserData 
} from 'store/states';

import { IMatchedUserListItem } from 'store/reducers';

// fakes
import { PlatformMock } from 'ionic-mocks';

import {
    AuthServiceFake,
    JwtFake,
    ReduxFake, 
    ApplicationServiceFake, 
    ApplicationConfigFake,
    StringUtilsFake,
    DeviceFake,
    PersistentStorageMemoryAdapterFake 
} from 'test/fake';

// responses
import { IMatchedUserResponse } from './responses';  

// schemas
import { matchedUserListSchema } from './schemas';

describe('Matched users service', () => {
    // register service's fakes
    let fakeRedux: ReduxFake;
    let fakeHttp: SecureHttpService;

    let matchedUsers: MatchedUsersService; // testable service

    beforeEach(() => {
        TestBed.configureTestingModule({
            providers: [{
                    provide: ApplicationService,
                    useFactory: (fakeStorage, fakePlatform) => new ApplicationServiceFake(ApplicationConfigFake, new ReduxFake(), fakeStorage, new DeviceFake, new StringUtilsFake, fakePlatform),
                    deps: [PersistentStorageService, Platform]
                }, {
                    provide: PersistentStorageService,
                    useFactory: () => new PersistentStorageService(new PersistentStorageMemoryAdapterFake),
                    deps: []
                }, {
                    provide: JwtService,
                    useFactory: () => new JwtFake(),
                    deps: []
                }, {
                    provide: AuthService,
                    useFactory: (fakeStorage, fakeJwt) => new AuthServiceFake(fakeStorage, fakeJwt),
                    deps: [PersistentStorageService, JwtService]
                }, {
                    provide: SecureHttpService,
                    useFactory: (fakeApplication, fakeHttp, fakeAuth, fakePersistentStorage) => new SecureHttpService(fakePersistentStorage, fakeApplication, fakeHttp, fakeAuth),
                    deps: [ApplicationService, Http, AuthService, PersistentStorageService]
                }, {
                    provide: Platform, 
                    useFactory: () => PlatformMock.instance(), 
                    deps: [] 
                }, {
                    provide: Http, 
                    useFactory: () => new Http(new MockBackend, new BaseRequestOptions), 
                    deps: [] 
                }
            ]}
        );

        // init service's fakes
        fakeRedux = new ReduxFake();
        fakeHttp = TestBed.get(SecureHttpService);

        // init service
        matchedUsers = new MatchedUsersService(fakeRedux, fakeHttp);
    });

    it('markMatchedUserAsRead should return correct result and dispatch both MATCHED_USERS_BEFORE_MARK_READ and MATCHED_USERS_AFTER_MARK_READ actions', () => {
        const response: string = 'ok';
        const matchedUserId: number = 1;

        const payload: IByIdPayload = {
            id: matchedUserId
        };

        // fake http
        spyOn(fakeHttp, 'put').and.returnValue(
            Observable.of(response)
        );

        // fake dispatch
        spyOn(fakeRedux, 'dispatch');

        matchedUsers.markMatchedUserAsRead(matchedUserId).subscribe(() => {
            expect(fakeRedux.dispatch).toHaveBeenCalled();
            expect(fakeRedux.dispatch).toHaveBeenCalledWith({
                type: MATCHED_USERS_AFTER_MARK_READ,
                payload: payload
            });

            // http
            expect(fakeHttp.put).toHaveBeenCalledWith('/matched-users/' + matchedUserId, {
                isNew: false
            });
        });

        expect(fakeRedux.dispatch).toHaveBeenCalled();
        expect(fakeRedux.dispatch).toHaveBeenCalledWith({
            type: MATCHED_USERS_BEFORE_MARK_READ,
            payload: payload
        });
    });

    it('markMatchedUserAsRead should dispatch MATCHED_USERS_ERROR_MARK_READ action if an error occurred', () => {
        const matchedUserId: number = 1;
        const errorResponse: string  = 'Some error';
 
        const payload: IByIdPayload = {
            id: matchedUserId
        };

        // fake http
        spyOn(fakeHttp, 'put').and.returnValue(Observable.throw(errorResponse));

        // fake dispatch
        spyOn(fakeRedux, 'dispatch');

        matchedUsers.markMatchedUserAsRead(matchedUserId).subscribe(() => {}, (error) => {
            expect(error).toEqual(errorResponse);
            expect(fakeRedux.dispatch).toHaveBeenCalled();
            expect(fakeRedux.dispatch).toHaveBeenCalledWith({
                type: MATCHED_USERS_ERROR_MARK_READ,
                payload: payload
            });

            // http
            expect(fakeHttp.put).toHaveBeenCalledWith('/matched-users/' + matchedUserId, {
                isNew: false
            });
        });

        expect(fakeRedux.dispatch).toHaveBeenCalled();
        expect(fakeRedux.dispatch).toHaveBeenCalledWith({
            type: MATCHED_USERS_BEFORE_MARK_READ,
            payload: payload
        });
    });

    it('markMatchedUserAsNotified should return correct result and dispatch both MATCHED_USERS_BEFORE_MARK_NOTIFIED and MATCHED_USERS_AFTER_MARK_NOTIFIED actions', () => {
        const response: string = 'ok';
        const matchedUserId: number = 1;

        const payload: IByIdPayload = {
            id: matchedUserId
        };
 
        // fake http
        spyOn(fakeHttp, 'put').and.returnValue(
            Observable.of(response)
        );

        // fake dispatch
        spyOn(fakeRedux, 'dispatch');

        matchedUsers.markMatchedUserAsNotified(matchedUserId).subscribe(() => {
            expect(fakeRedux.dispatch).toHaveBeenCalled();
            expect(fakeRedux.dispatch).toHaveBeenCalledWith({
                type: MATCHED_USERS_AFTER_MARK_NOTIFIED,
                payload: payload
            });

            // http
            expect(fakeHttp.put).toHaveBeenCalledWith('/matched-users/' + matchedUserId, {
                isRead: true
            });
        });

        expect(fakeRedux.dispatch).toHaveBeenCalled();
        expect(fakeRedux.dispatch).toHaveBeenCalledWith({
            type: MATCHED_USERS_BEFORE_MARK_NOTIFIED,
            payload: payload
        });
    });

    it('markMatchedUserAsNotified should dispatch MATCHED_USERS_ERROR_MARK_NOTIFIED action if an error occurred', () => {
        const matchedUserId: number = 1;
        const errorResponse: string  = 'Some error';
 
        const payload: IByIdPayload = {
            id: matchedUserId
        };

        // fake http
        spyOn(fakeHttp, 'put').and.returnValue(Observable.throw(errorResponse));

        // fake dispatch
        spyOn(fakeRedux, 'dispatch');

        matchedUsers.markMatchedUserAsNotified(matchedUserId).subscribe(() => {}, (error) => {
            expect(error).toEqual(errorResponse);
            expect(fakeRedux.dispatch).toHaveBeenCalled();
            expect(fakeRedux.dispatch).toHaveBeenCalledWith({
                type: MATCHED_USERS_ERROR_MARK_NOTIFIED,
                payload: payload
            });

            // http
            expect(fakeHttp.put).toHaveBeenCalledWith('/matched-users/' + matchedUserId, {
                isRead: true
            });
        });

        expect(fakeRedux.dispatch).toHaveBeenCalled();
        expect(fakeRedux.dispatch).toHaveBeenCalledWith({
            type: MATCHED_USERS_BEFORE_MARK_NOTIFIED,
            payload: payload
        });
    });

    it('setMatchedUsers should dispatch MATCHED_USERS_SET action', () => {
        const matchedUserId: number = 1;
        const response: Array<IMatchedUserResponse> = [{
            id: matchedUserId
        }];

        const payload: IEntitiesPayload = normalize(response, matchedUserListSchema);

        const expectedArgs = {
            type: MATCHED_USERS_SET,
            payload: payload
        };

        spyOn(fakeRedux, 'dispatch');
        matchedUsers.setMatchedUsers(response);

        expect(fakeRedux.dispatch).toHaveBeenCalled();
        expect(fakeRedux.dispatch).toHaveBeenCalledWith(expectedArgs);
    });

    it('watchNewMatchedUsersCount should return a correct result of not read matched users count', () => {
        const notReadMatchedUsersCount: number = 10;

        // fake the method
        spyOn(matchedUsers, 'watchNewMatchedUsersCount').and.returnValue(
            Observable.of(notReadMatchedUsersCount)
        );

        matchedUsers.watchNewMatchedUsersCount().subscribe(count => {
            expect(count).toEqual(notReadMatchedUsersCount); 
        });
    });

    it('watchNotNotifiedMatchedUser should return a correct result', () => {
        const matchedUserId: number = 1;
        const userId: number = 1;
        const avatarId: number = 1;

        const matchedUserData: IMatchedUserData = {
            id: matchedUserId,
            user: userId
        };

        const userData: IUser = {
            id: userId,
            avatar: avatarId
        };

        const avatarData: IAvatarData = {
            id: avatarId,
            userId: userId
        };

        const result: IMatchedUserListItem = {
            matchedUser: matchedUserData,
            user: userData, 
            avatar: avatarData
        };

        // fake the method
        spyOn(matchedUsers, 'watchNotNotifiedMatchedUser').and.returnValue(
            Observable.of(result)
        );

        matchedUsers.watchNotNotifiedMatchedUser().subscribe(response => {
            expect(response).toEqual(result);
        });
    });

    it('watchNotNotifiedMatchedUser should return an undefined value if matched user list empty', () => {
        // fake the method
        spyOn(matchedUsers, 'watchNotNotifiedMatchedUser').and.returnValue(
            Observable.of(undefined)
        );

        matchedUsers.watchNotNotifiedMatchedUser().subscribe(response => {
            expect(response).toBeUndefined();
        });
    });

    it('watchIsMatchedUsersFetched should return a correct boolean value', () => {
        const isFetched: boolean = true;

        // fake the method
        spyOn(matchedUsers, 'watchIsMatchedUsersFetched').and.returnValue(
            Observable.of(isFetched)
        );

        matchedUsers.watchIsMatchedUsersFetched().subscribe(isDataFetched => {
            expect(isDataFetched).toEqual(isFetched); 
        });
    });

    it('watchMatchedUserList should return a correct result', () => {
        const matchedUserId: number = 1;
        const userId: number = 1;
        const avatarId: number = 1;
 
        const matchedUserData: IMatchedUserData = {
            id: matchedUserId,
            user: userId
        };

        const userData: IUser = {
            id: userId,
            avatar: avatarId
        };

        const avatarData: IAvatarData = {
            id: avatarId,
            userId: userId
        };

        const result: IMatchedUserListItem = {
            matchedUser: matchedUserData,
            user: userData, 
            avatar: avatarData
        };

        // fake the method
        spyOn(matchedUsers, 'watchMatchedUserList').and.returnValue(
            Observable.of(result)
        );

        matchedUsers.watchMatchedUserList().subscribe(response => {
            expect(response).toEqual(result);
        });
    });

    it('watchMatchedUserList should return an undefined value if matched user list empty or there are no relevant users', () => {
        // fake the method
        spyOn(matchedUsers, 'watchMatchedUserList').and.returnValue(
            Observable.of(undefined)
        );

        matchedUsers.watchMatchedUserList().subscribe(response => {
            expect(response).toBeUndefined();
        });
    });
});
