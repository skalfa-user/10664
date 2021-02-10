import { normalize } from 'normalizr';
import { Observable } from 'rxjs/Rx';
import { MockBackend } from '@angular/http/testing';
import { Http, BaseRequestOptions } from '@angular/http';
import { TestBed } from '@angular/core/testing';
import { Platform } from 'ionic-angular';

// services
import { HotListService } from './'
import { StringUtilsService } from 'services/string-utils';
import { SecureHttpService } from 'services/http';
import { ApplicationService } from 'services/application';
import { PersistentStorageService } from 'services/persistent-storage';
import { JwtService } from 'services/jwt';
import { AuthService } from 'services/auth';

// payloads
import {
    IEntitiesPayload,
    IEntityPayload,
    IWrappedEntitiesPayload
} from 'store/payloads';

import { 
    HOT_LIST_SET,
    HOT_LIST_AFTER_ADD,
    HOT_LIST_BEFORE_ADD,
    HOT_LIST_ERROR_ADD,
    HOT_LIST_AFTER_DELETE,
    HOT_LIST_BEFORE_DELETE,
    HOT_LIST_ERROR_DELETE
} from 'store/actions'; 

// fakes
import { PlatformMock } from 'ionic-mocks';

import {
    AuthServiceFake,
    JwtFake,
    ReduxFake, 
    StringUtilsFake,
    ApplicationServiceFake, 
    ApplicationConfigFake, 
    DeviceFake,
    PersistentStorageMemoryAdapterFake 
} from 'test/fake';

// responses
import { IHotListResponse } from './responses';  

import { 
    IUser, 
    IAvatarData, 
    IHotListData 
} from 'store/states';

import { IHotListItem } from 'store/reducers';

// schemas
import { hotListSchema } from './schemas';

describe('Hot list service', () => {
    // register service's fakes
    let fakeRedux: ReduxFake;
    let fakeHttp: SecureHttpService;
    let fakeAuth: AuthService;
    let fakeStringUtils: StringUtilsService;

    let hotList: HotListService; // testable service

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
                },
                StringUtilsService
            ]}
        );

        // init service's fakes
        fakeRedux = new ReduxFake();
        fakeHttp  = TestBed.get(SecureHttpService);
        fakeAuth  = TestBed.get(AuthService);
        fakeStringUtils = TestBed.get(StringUtilsService);

        // init service
        hotList = new HotListService(fakeStringUtils, fakeRedux, fakeHttp, fakeAuth);
    });

    it('setHotList should dispatch HOT_LIST_SET action', () => {
        const hotListId: number = 1;
        const response: Array<IHotListResponse> = [{
            id: hotListId
        }];

        const payload: IEntitiesPayload = normalize(response, hotListSchema);

        const expectedArgs = {
            type: HOT_LIST_SET,
            payload: payload
        };

        spyOn(fakeRedux, 'dispatch');
        hotList.setHotList(response);

        expect(fakeRedux.dispatch).toHaveBeenCalled();
        expect(fakeRedux.dispatch).toHaveBeenCalledWith(expectedArgs);
    });

    it('deleteMeFromHotList should return correct result and dispatch both HOT_LIST_BEFORE_DELETE and HOT_LIST_AFTER_DELETE actions', () => {
        const hotListId: number = 1;
        const userId: number = 1;
        const response: string = 'ok';

        const payload: IEntityPayload = {
            id: hotListId,
            entityId: userId
        };
 
        // fake http
        spyOn(fakeHttp, 'delete').and.returnValue(
            Observable.of(response)
        );

        // fake hot list
        spyOn(hotList, 'getMyHotListId').and.returnValue(hotListId);

        // fake auth
        spyOn(fakeAuth, 'getUserId').and.returnValue(userId);

        // fake dispatch
        spyOn(fakeRedux, 'dispatch');

        hotList.deleteMeFromHotList().subscribe(() => {
            expect(fakeRedux.dispatch).toHaveBeenCalled();
            expect(fakeRedux.dispatch).toHaveBeenCalledWith({
                type: HOT_LIST_AFTER_DELETE,
                payload: payload
            });

            // http
            expect(fakeHttp.delete).toHaveBeenCalledWith('/hotlist-users/me');

            // auth 
            expect(fakeAuth.getUserId).toHaveBeenCalled();

            // hot list 
            expect(hotList.getMyHotListId).toHaveBeenCalled();

        });

        expect(fakeRedux.dispatch).toHaveBeenCalled();
        expect(fakeRedux.dispatch).toHaveBeenCalledWith({
            type: HOT_LIST_BEFORE_DELETE,
            payload: payload
        });
    });
 
    it('deleteMeFromHotList should trigger a type error if there is no logged user in hot list', () => {
        // fake hot list
        spyOn(hotList, 'getMyHotListId').and.returnValue(undefined);

        expect(() => hotList
            .deleteMeFromHotList())
            .toThrow(new TypeError(`Logged user is not listed in hot list and could not be deleted`));
    });
 
    it('deleteMeFromHotList should dispatch HOT_LIST_ERROR_DELETE action if an error occurred', () => {
        const hotListId: number = 1;
        const errorResponse: string  = 'Some error';
        const userId: number = 1;

        const payload: IEntityPayload = {
            id: hotListId,
            entityId: userId
        };
 
        // fake http
        spyOn(fakeHttp, 'delete').and.returnValue(Observable.throw(errorResponse));

        // fake hot list
        spyOn(hotList, 'getMyHotListId').and.returnValue(hotListId);

        // fake auth
        spyOn(fakeAuth, 'getUserId').and.returnValue(userId);

        // fake dispatch
        spyOn(fakeRedux, 'dispatch');

        hotList.deleteMeFromHotList().subscribe(() => {}, (error) => {
            expect(error).toEqual(errorResponse);
            expect(fakeRedux.dispatch).toHaveBeenCalled();
            expect(fakeRedux.dispatch).toHaveBeenCalledWith({
                type: HOT_LIST_ERROR_DELETE,
                payload: payload
            });

            // http
            expect(fakeHttp.delete).toHaveBeenCalledWith('/hotlist-users/me');

            // auth 
            expect(fakeAuth.getUserId).toHaveBeenCalled();

            // hot list 
            expect(hotList.getMyHotListId).toHaveBeenCalled();
        });

        expect(fakeRedux.dispatch).toHaveBeenCalled();
        expect(fakeRedux.dispatch).toHaveBeenCalledWith({
            type: HOT_LIST_BEFORE_DELETE,
            payload: payload
        });
    });

    it('addMeToHotList should return correct result and dispatch both HOT_LIST_BEFORE_ADD and HOT_LIST_AFTER_ADD actions', () => {
        const randomHotListId: string = 'test';
        const userId: number = 1;
        const hotListId: number = 1;

        const response: IHotListResponse = {
            id: hotListId,
            user: {
                id: userId
            }
        };

        // fake hot list
        spyOn(hotList, 'isMeInHotList').and.returnValue(false);

        // fake string utils
        spyOn(fakeStringUtils, 'getRandomString').and.returnValue(randomHotListId);

        // fake http
        spyOn(fakeHttp, 'post').and.returnValue(
            Observable.of(response)
        );

        // fake dispatch
        spyOn(fakeRedux, 'dispatch');

        // fake auth
        spyOn(fakeAuth, 'getUserId').and.returnValue(userId);

        hotList.addMeToHotList().subscribe(response => {
            const afterAddPayload: IWrappedEntitiesPayload = {
                id: randomHotListId,
                data: normalize(response, hotListSchema)
            };

            expect(fakeRedux.dispatch).toHaveBeenCalled();
            expect(fakeRedux.dispatch).toHaveBeenCalledWith({
                type: HOT_LIST_AFTER_ADD,
                payload: afterAddPayload
            });

            // http
            expect(fakeHttp.post).toHaveBeenCalledWith('/hotlist-users/me');

            // auth 
            expect(fakeAuth.getUserId).toHaveBeenCalled();

            // hot list 
            expect(hotList.isMeInHotList).toHaveBeenCalled();

            // string utils
            expect(fakeStringUtils.getRandomString).toHaveBeenCalled();
        });

        const payload: IEntityPayload = {
            id: randomHotListId,
            entityId: userId
        };

        expect(fakeRedux.dispatch).toHaveBeenCalled();
        expect(fakeRedux.dispatch).toHaveBeenCalledWith({
            type: HOT_LIST_BEFORE_ADD,
            payload: payload
        });
    });

    it('addMeToHotList should trigger a type error if logged user already in hot list', () => {
        // fake hot list
        spyOn(hotList, 'isMeInHotList').and.returnValue(true);

        expect(() => hotList
            .addMeToHotList())
            .toThrow(new TypeError(`Logged user already listed in hot list and could not be added`));
    });

    it('addMeToHotList should dispatch HOT_LIST_ERROR_ADD action if an error occurred', () => {
        const userId: number = 1;
        const randomHotListId: string = 'test';
        const errorResponse: string  = 'Some error';
 
        const payload: IEntityPayload = {
            id: randomHotListId,
            entityId: userId
        };

        // fake hot list
        spyOn(hotList, 'isMeInHotList').and.returnValue(false);

        // fake string utils
        spyOn(fakeStringUtils, 'getRandomString').and.returnValue(randomHotListId);

        // fake http
        spyOn(fakeHttp, 'post').and.returnValue(Observable.throw(errorResponse));

        // fake dispatch
        spyOn(fakeRedux, 'dispatch');

        // fake auth
        spyOn(fakeAuth, 'getUserId').and.returnValue(userId);

        hotList.addMeToHotList().subscribe(() => {}, (error) => {
            expect(error).toEqual(errorResponse);
            expect(fakeRedux.dispatch).toHaveBeenCalled();
            expect(fakeRedux.dispatch).toHaveBeenCalledWith({
                type: HOT_LIST_ERROR_ADD,
                payload: payload
            });

            // http
            expect(fakeHttp.post).toHaveBeenCalledWith('/hotlist-users/me');

            // auth 
            expect(fakeAuth.getUserId).toHaveBeenCalled();

            // hot list 
            expect(hotList.isMeInHotList).toHaveBeenCalled();

            // string utils
            expect(fakeStringUtils.getRandomString).toHaveBeenCalled();
        });

        expect(fakeRedux.dispatch).toHaveBeenCalled();
        expect(fakeRedux.dispatch).toHaveBeenCalledWith({
            type: HOT_LIST_BEFORE_ADD,
            payload: payload
        });
    });

    it('watchMeInHotList should return a correct boolean value', () => {
        const isMeInHotList: boolean = true;

        // fake the method
        spyOn(hotList, 'watchMeInHotList').and.returnValue(
            Observable.of(isMeInHotList)
        );

        hotList.watchMeInHotList().subscribe(isUserInHotList => {
            expect(isUserInHotList).toEqual(isMeInHotList); 
        });
    });

    it('watchIsHotListFetched should return a correct boolean value', () => {
        const isFetched: boolean = true;

        // fake the method
        spyOn(hotList, 'watchIsHotListFetched').and.returnValue(
            Observable.of(isFetched)
        );

        hotList.watchIsHotListFetched().subscribe(isDataFetched => {
            expect(isDataFetched).toEqual(isFetched); 
        });
    });

    it('watchHotList should return a correct result', () => {
        const hotListId: number = 1;
        const userId: number = 1;
        const avatarId: number = 1;

        const hotListData: IHotListData = {
            id: hotListId,
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

        const result: IHotListItem = {
            hotList: hotListData,
            user: userData, 
            avatar: avatarData
        };

        // fake the method
        spyOn(hotList, 'watchHotList').and.returnValue(
            Observable.of(result)
        );

        hotList.watchHotList().subscribe(response => {
            expect(response).toEqual(result);
        });
    });

    it('watchHotList should return an undefined value if hot list empty', () => {
        // fake the method
        spyOn(hotList, 'watchHotList').and.returnValue(
            Observable.of(undefined)
        );

        hotList.watchHotList().subscribe(response => {
            expect(response).toBeUndefined();
        });
    });
});
