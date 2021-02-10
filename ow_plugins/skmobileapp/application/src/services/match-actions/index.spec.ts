import { TestBed } from '@angular/core/testing';
import { Observable } from 'rxjs/Rx';
import { MockBackend } from '@angular/http/testing';
import { Http, BaseRequestOptions } from '@angular/http';
import { NgRedux } from '@angular-redux/store';
import { normalize } from 'normalizr';
import { Platform } from 'ionic-angular';

// services
import { MatchActionsService, MatchType } from './';
import { SecureHttpService } from 'services/http';
import { ApplicationService } from 'services/application';
import { PersistentStorageService } from 'services/persistent-storage';
import { JwtService } from 'services/jwt';
import { AuthService } from 'services/auth';
import { StringUtilsService } from 'services/string-utils';

// payloads
import { 
    IMatchActionDataPayload,
    IWrappedEntitiesPayload
} from 'store/payloads';

// store
import {
    MATCH_ACTIONS_BEFORE_DELETE,
    MATCH_ACTIONS_AFTER_DELETE,
    MATCH_ACTIONS_ERROR_DELETE, 
    MATCH_ACTIONS_BEFORE_ADD, 
    MATCH_ACTIONS_AFTER_ADD, 
    MATCH_ACTIONS_ERROR_ADD 
} from 'store/actions'; 

// schemas
import { matchSchema } from 'services/match-actions/schemas';

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
    PersistentStorageMemoryAdapterFake } from 'test/fake';

// responses
import { IMatchResponse } from 'services/user/responses';

describe('Match action service', () => {
    // register service's fakes
    let fakeHttp: SecureHttpService;
    let fakeRedux: ReduxFake;
    let fakeStringUtils: StringUtilsService;

    let matchAction: MatchActionsService; // testable service

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
                    provide: Http, 
                    useFactory: () => new Http(new MockBackend, new BaseRequestOptions), 
                    deps: [] 
                }, {
                    provide: Platform, 
                    useFactory: () => PlatformMock.instance(), 
                    deps: [] 
                }, {
                    provide: NgRedux, 
                    useFactory: () => new ReduxFake(), 
                    deps: [] 
                },
                MatchActionsService,
                StringUtilsService
            ]}
        );

        // init service's fakes
        fakeRedux = TestBed.get(NgRedux);
        fakeHttp = TestBed.get(SecureHttpService);
        fakeStringUtils = TestBed.get(StringUtilsService);
 
        // init application service
        matchAction = TestBed.get(MatchActionsService);
    });

    it('deleteMatch should return correct result and dispatch both MATCH_ACTIONS_BEFORE_DELETE and MATCH_ACTIONS_AFTER_DELETE actions', () => {
        const userId: number = 1;
        const matchId: number = 1;

        const payload: IMatchActionDataPayload = {
            id: matchId,
            userId: userId
        };

        // fake http
        spyOn(fakeHttp, 'delete').and.returnValue(
            Observable.of({})
        );

        // fake dispatch
        spyOn(fakeRedux, 'dispatch');

        matchAction.deleteMatch(matchId, userId).subscribe(() => {
            expect(fakeRedux.dispatch).toHaveBeenCalled();
            expect(fakeRedux.dispatch).toHaveBeenCalledWith({
                type: MATCH_ACTIONS_AFTER_DELETE,
                payload: payload
            });

            // http
            expect(fakeHttp.delete).toHaveBeenCalledWith('/math-actions/user/' + userId);
        });

        expect(fakeRedux.dispatch).toHaveBeenCalled();
        expect(fakeRedux.dispatch).toHaveBeenCalledWith({
            type: MATCH_ACTIONS_BEFORE_DELETE,
            payload: payload
        });
    });

    it('deleteMatch should dispatch MATCH_ACTIONS_ERROR_DELETE action if an error occurred', () => {
        const userId: number = 1;
        const matchId: number = 1;
        const errorResponse: string  = 'Some error';

        // fake http
        spyOn(fakeHttp, 'delete').and.returnValue(Observable.throw(errorResponse));

        // fake dispatch
        spyOn(fakeRedux, 'dispatch');

        matchAction.deleteMatch(userId, matchId).subscribe(() => {}, (error) => {
            expect(error).toEqual(errorResponse);
            expect(fakeRedux.dispatch).toHaveBeenCalled();
            expect(fakeRedux.dispatch).toHaveBeenCalledWith({
                type: MATCH_ACTIONS_ERROR_DELETE,
                payload: {
                    id: matchId,
                    userId: userId
                }
            });

            // http
            expect(fakeHttp.delete).toHaveBeenCalledWith('/math-actions/user/' + userId);
        });

        expect(fakeRedux.dispatch).toHaveBeenCalled();
        expect(fakeRedux.dispatch).toHaveBeenCalledWith({
            type: MATCH_ACTIONS_BEFORE_DELETE,
            payload: {
                id: matchId,
                userId: userId
            }
        });
    });

    it('createMatch should return correct result and dispatch both MATCH_ACTIONS_BEFORE_ADD and MATCH_ACTIONS_AFTER_ADD actions', () => {
        const matchType: MatchType = 'like';
        const randomMatchId: string = 'test';
        const userId: number = 1;
        const matchId: number = 1;

        const response: IMatchResponse = {
            id: matchId
        };

        // fake string utils
        spyOn(fakeStringUtils, 'getRandomString').and.returnValue(randomMatchId);

        // fake http
        spyOn(fakeHttp, 'post').and.returnValue(
            Observable.of(response)
        );

        // fake dispatch
        spyOn(fakeRedux, 'dispatch');

        matchAction.createMatch(userId, matchType).subscribe(response => {
            const afterAddPayload: IWrappedEntitiesPayload = {
                id: randomMatchId,
                data: normalize(response, matchSchema)
            };

            expect(fakeRedux.dispatch).toHaveBeenCalled();
            expect(fakeRedux.dispatch).toHaveBeenCalledWith({
                type: MATCH_ACTIONS_AFTER_ADD,
                payload: afterAddPayload
            });

            // http
            expect(fakeHttp.post).toHaveBeenCalledWith('/math-actions/user', {
                userId: userId,
                type: matchType
            });
        });

        const beforeAddPayload: IMatchActionDataPayload = {
            id: randomMatchId,
            type: matchType,
            userId: userId
        };

        expect(fakeRedux.dispatch).toHaveBeenCalled();
        expect(fakeRedux.dispatch).toHaveBeenCalledWith({
            type: MATCH_ACTIONS_BEFORE_ADD,
            payload: beforeAddPayload
        });
    });

    it('createMatch should dispatch MATCH_ACTIONS_ERROR_ADD action if an error occurred', () => {
        const matchType: MatchType = 'like';
        const userId: number = 1;
        const randomMatchId: string = 'test';
        const errorResponse: string  = 'Some error';
 
        const payload: IMatchActionDataPayload = {
            id: randomMatchId,
            type: matchType,
            userId: userId
        };

        // fake string utils
        spyOn(fakeStringUtils, 'getRandomString').and.returnValue(randomMatchId);

        // fake http
        spyOn(fakeHttp, 'post').and.returnValue(Observable.throw(errorResponse));

        // fake dispatch
        spyOn(fakeRedux, 'dispatch');

        matchAction.createMatch(userId, matchType).subscribe(() => {}, (error) => {
            expect(error).toEqual(errorResponse);
            expect(fakeRedux.dispatch).toHaveBeenCalled();
            expect(fakeRedux.dispatch).toHaveBeenCalledWith({
                type: MATCH_ACTIONS_ERROR_ADD,
                payload: payload
            });

            // http
            expect(fakeHttp.post).toHaveBeenCalledWith('/math-actions/user', {
                userId: userId,
                type: matchType
            });
        });

        expect(fakeRedux.dispatch).toHaveBeenCalled();
        expect(fakeRedux.dispatch).toHaveBeenCalledWith({
            type: MATCH_ACTIONS_BEFORE_ADD,
            payload: payload
        });
    });
});
