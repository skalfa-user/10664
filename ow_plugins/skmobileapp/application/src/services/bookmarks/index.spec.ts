import { Observable } from 'rxjs/Rx';
import { MockBackend } from '@angular/http/testing';
import { Http, BaseRequestOptions } from '@angular/http';
import { TestBed } from '@angular/core/testing';
import { normalize } from 'normalizr';
import { Platform } from 'ionic-angular';

// services
import { BookmarksService } from './'
import { SecureHttpService } from 'services/http';
import { ApplicationService } from 'services/application';
import { PersistentStorageService } from 'services/persistent-storage';
import { JwtService } from 'services/jwt';
import { AuthService } from 'services/auth';
import { StringUtilsService } from 'services/string-utils';

// payloads
import {
    IEntitiesPayload,
    IEntityPayload,
    IWrappedEntitiesPayload
} from 'store/payloads';

// store
import {
    IMatchAction,
    IAvatarData,
    IUser,
    IBookmarkData
} from 'store/states';

import { IBookmarkListItem } from 'store/reducers';

import {
    BOOKMARKS_LOAD,
    BOOKMARKS_BEFORE_DELETE,
    BOOKMARKS_AFTER_DELETE,
    BOOKMARKS_ERROR_DELETE,
    BOOKMARKS_BEFORE_ADD,
    BOOKMARKS_AFTER_ADD,
    BOOKMARKS_ERROR_ADD
} from 'store/actions'; 

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

// schemas
import { bookmarkListSchema } from './schemas';

// responses
import { IBookmarkResponse } from './responses';  

describe('Bookmarks service', () => {
    // register service's fakes
    let fakeRedux: ReduxFake;
    let fakeHttp: SecureHttpService;
    let fakeStringUtils: StringUtilsService;

    let bookmarks: BookmarksService; // testable service

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
                },{
                    provide: Http, 
                    useFactory: () => new Http(new MockBackend, new BaseRequestOptions), 
                    deps: [] 
                }, {
                    provide: Platform, 
                    useFactory: () => PlatformMock.instance(), 
                    deps: [] 
                },
                StringUtilsService
            ]}
        );

        // init service's fakes
        fakeRedux = new ReduxFake();
        fakeHttp = TestBed.get(SecureHttpService);
        fakeStringUtils = TestBed.get(StringUtilsService);

        // init service
        bookmarks = new BookmarksService(fakeStringUtils, fakeRedux, fakeHttp);
    });

    it('loadBookmarkList should dispatch BOOKMARKS_LOAD action', () => {
        const bookmarkId: number = 1;

        const response: Array<IBookmarkResponse> = [{
            id: bookmarkId
        }];

        // fake http
        spyOn(fakeHttp, 'get').and.returnValue(
            Observable.of(response)
        );

        spyOn(fakeRedux, 'dispatch');

        const payload: IEntitiesPayload = normalize(response, bookmarkListSchema);

        const expectedArgs = {
            type: BOOKMARKS_LOAD,
            payload: payload
        };

        bookmarks.loadBookmarkList().subscribe(() => {
            expect(fakeRedux.dispatch).toHaveBeenCalled();
            expect(fakeRedux.dispatch).toHaveBeenCalledWith(expectedArgs);

            // http
            expect(fakeHttp.get).toHaveBeenCalledWith('/bookmarks');
        });
    });

    it('addBookmark should return correct result and dispatch both BOOKMARKS_BEFORE_ADD and BOOKMARKS_AFTER_ADD actions', () => {
        const bookmarkId: number = 1;
        const userId: number = 1;

        const response: Array<IBookmarkResponse> = [{
            id: bookmarkId,
            user: {
                id: userId
            }
        }];

        // fake http
        spyOn(fakeHttp, 'post').and.returnValue(
            Observable.of(response)
        );

        // fake sting utils
        spyOn(fakeStringUtils, 'getRandomString').and.returnValue(
            bookmarkId
        );

        // fake dispatch
        spyOn(fakeRedux, 'dispatch');

        bookmarks.addBookmark(userId).subscribe((response) => {
            const afterAddPayload: IWrappedEntitiesPayload = {
                id: bookmarkId,
                data: normalize(response, bookmarkListSchema)
            };

            expect(fakeRedux.dispatch).toHaveBeenCalled();
            expect(fakeRedux.dispatch).toHaveBeenCalledWith({
                type: BOOKMARKS_AFTER_ADD,
                payload: afterAddPayload
            });

            // http
            expect(fakeHttp.post).toHaveBeenCalledWith('/bookmarks', {
                userId: userId
            });
        });

        const beforeAddPayload: IEntityPayload = {
            id: bookmarkId,
            entityId: userId
        };

        expect(fakeRedux.dispatch).toHaveBeenCalled();
        expect(fakeRedux.dispatch).toHaveBeenCalledWith({
            type: BOOKMARKS_BEFORE_ADD,
            payload: beforeAddPayload
        });
    });

    it('addBookmark should dispatch BOOKMARKS_ERROR_ADD action if an error occurred', () => {
        const bookmarkId: number = 1;
        const userId: number = 1;
        const errorResponse: string  = 'Some error';
 
        // fake http
        spyOn(fakeHttp, 'post').and.returnValue(Observable.throw(errorResponse));

        // fake sting utils
        spyOn(fakeStringUtils, 'getRandomString').and.returnValue(
            bookmarkId
        );

        // fake dispatch
        spyOn(fakeRedux, 'dispatch');

        const payload: IEntityPayload = {
            id: bookmarkId,
            entityId: userId
        };

        bookmarks.addBookmark(userId).subscribe(() => {}, (error) => {
            expect(error).toEqual(errorResponse);
            expect(fakeRedux.dispatch).toHaveBeenCalled();
            expect(fakeRedux.dispatch).toHaveBeenCalledWith({
                type: BOOKMARKS_ERROR_ADD,
                payload: payload
            });

            // http
            expect(fakeHttp.post).toHaveBeenCalledWith('/bookmarks', {
                userId: userId
            });
        });

        expect(fakeRedux.dispatch).toHaveBeenCalled();
        expect(fakeRedux.dispatch).toHaveBeenCalledWith({
            type: BOOKMARKS_BEFORE_ADD,
            payload: payload
        });
    });

    it('deleteBookmark should return correct result and dispatch both BOOKMARKS_BEFORE_DELETE and BOOKMARKS_AFTER_DELETE actions', () => {
        const bookmarkId: number = 1;
        const userId: number = 1;
        const response: string = 'ok';

        // fake http
        spyOn(fakeHttp, 'delete').and.returnValue(
            Observable.of(response)
        );

        // fake dispatch
        spyOn(fakeRedux, 'dispatch');

        bookmarks.deleteBookmark(bookmarkId, userId).subscribe(() => {
            const afterDeletePayload: IEntityPayload = {
                id: bookmarkId,
                entityId: userId
            };

            expect(fakeRedux.dispatch).toHaveBeenCalled();
            expect(fakeRedux.dispatch).toHaveBeenCalledWith({
                type: BOOKMARKS_AFTER_DELETE,
                payload: afterDeletePayload
            });

            // http
            expect(fakeHttp.delete).toHaveBeenCalledWith('/bookmarks/users/' + userId);
        });

        const beforeDeletePayload: IEntityPayload = {
            id: bookmarkId,
            entityId: userId
        };

        expect(fakeRedux.dispatch).toHaveBeenCalled();
        expect(fakeRedux.dispatch).toHaveBeenCalledWith({
            type: BOOKMARKS_BEFORE_DELETE,
            payload: beforeDeletePayload
        });
    });

    it('deleteBookmark should dispatch BOOKMARKS_ERROR_DELETE action if an error occurred', () => {
        const bookmarkId: number = 1;
        const userId: number = 1;
        const errorResponse: string  = 'Some error';
 
        // fake http
        spyOn(fakeHttp, 'delete').and.returnValue(Observable.throw(errorResponse));

        // fake dispatch
        spyOn(fakeRedux, 'dispatch');

        bookmarks.deleteBookmark(bookmarkId, userId).subscribe(() => {}, (error) => {
            const errorDeletePayload: IEntityPayload = {
                id: bookmarkId,
                entityId: userId
            };

            expect(error).toEqual(errorResponse);
            expect(fakeRedux.dispatch).toHaveBeenCalled();
            expect(fakeRedux.dispatch).toHaveBeenCalledWith({
                type: BOOKMARKS_ERROR_DELETE,
                payload: errorDeletePayload
            });

            // http
            expect(fakeHttp.delete).toHaveBeenCalledWith('/bookmarks/users/' + userId);
        });

        const beforeDeletePayload: IEntityPayload = {
            id: bookmarkId,
            entityId: userId
        };
 
        expect(fakeRedux.dispatch).toHaveBeenCalled();
        expect(fakeRedux.dispatch).toHaveBeenCalledWith({
            type: BOOKMARKS_BEFORE_DELETE,
            payload: beforeDeletePayload
        });
    });

    it('watchIsBookmarksFetched should return a correct boolean value', () => {
        const isFetched: boolean = true;

        // fake the method
        spyOn(bookmarks, 'watchIsBookmarksFetched').and.returnValue(
            Observable.of(isFetched)
        );

        bookmarks.watchIsBookmarksFetched().subscribe(isDataFetched => {
            expect(isDataFetched).toEqual(isFetched); 
        });
    });

    it('watchBookmarkList should return a correct result', () => {
        const bookmarkId: number = 1;
        const userId: number = 1;
        const avatarId: number = 1;
        const matchActionId: number = 1;

        const bookmarkData: IBookmarkData = {
            id: bookmarkId,
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

        const matchActionData: IMatchAction = {
            id: matchActionId
        };

        const result: IBookmarkListItem = {
            bookmark: bookmarkData,
            user: userData,
            avatar: avatarData,
            matchAction: matchActionData
        };

        // fake the method
        spyOn(bookmarks, 'watchBookmarkList').and.returnValue(
            Observable.of(result)
        );

        bookmarks.watchBookmarkList().subscribe(response => {
            expect(response).toEqual(result);
        });
    });
});
