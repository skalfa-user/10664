import { TestBed } from '@angular/core/testing';
import { MockBackend } from '@angular/http/testing';
import { Http, BaseRequestOptions } from '@angular/http';
import { NgRedux } from '@angular-redux/store';
import { Observable } from 'rxjs/Rx';
import { Platform } from 'ionic-angular';

// services
import { AvatarsService } from './';
import { SecureHttpService } from 'services/http';
import { ApplicationService } from 'services/application';
import { PersistentStorageService } from 'services/persistent-storage';
import { JwtService } from 'services/jwt';
import { AuthService } from 'services/auth';
import { MatchActionsService } from 'services/match-actions';

// payloads
import {
    IByIdPayload,
    IEntityPayload, 
    IAvatarDataPayload,
    IAvatarAfterUploadPayload
} from 'store/payloads';

// store
import { 
    IAvatarData
} from 'store/states';

import {
    AVATARS_BEFORE_UPLOAD,
    AVATARS_AFTER_UPLOAD,
    AVATARS_ERROR_UPLOAD,
    AVATARS_BEFORE_DELETE,
    AVATARS_AFTER_DELETE,
    AVATARS_ERROR_DELETE
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
    PersistentStorageMemoryAdapterFake } from 'test/fake';

// responses
import { IAvatarResponse } from 'services/user/responses';

describe('Avatars service', () => {
    // register service's fakes
    let fakeRedux: ReduxFake;
    let fakeHttp: SecureHttpService;
    let fakeAuth: AuthService;

    let avatars: AvatarsService; // testable service

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
                },{
                    provide: SecureHttpService,
                    useFactory: (fakeApplication, fakeHttp, fakeAuth, fakePersistentStorage) => new SecureHttpService(fakePersistentStorage, fakeApplication, fakeHttp, fakeAuth),
                    deps: [ApplicationService, Http, AuthService, PersistentStorageService]
                }, {
                    provide: Http, 
                    useFactory: () => new Http(new MockBackend, new BaseRequestOptions), 
                    deps: [] 
                }, {
                    provide: NgRedux, 
                    useFactory: () => new ReduxFake(), 
                    deps: [] 
                }, {
                    provide: Platform, 
                    useFactory: () => PlatformMock.instance(), 
                    deps: [] 
                },
                MatchActionsService,
                AvatarsService
            ]}
        );

        // init service's fakes
        fakeRedux = TestBed.get(NgRedux);
        fakeHttp = TestBed.get(SecureHttpService);
        fakeAuth = TestBed.get(AuthService);

        // init service
        avatars = TestBed.get(AvatarsService);
    });

    it('watchMyUploadingAvatar should return a correct result', () => {
        const avatarId: number = 1;
        const avatar: IAvatarData = {
            id: avatarId
        };

        // fake the method
        spyOn(avatars, 'watchMyUploadingAvatar').and.returnValue(
            Observable.of(avatar)
        );

        avatars.watchMyUploadingAvatar().subscribe(result => {
            expect(result).toEqual(avatar); 
        });
    });

    it('beforeUploadMyAvatar should dispatch the AVATARS_BEFORE_UPLOAD action', () => {
        const avatarUrl: string = 'test';
        const userId: number = 1;
        const avatarId: number = 1;

        // fake auth
        spyOn(fakeAuth, 'getUserId').and.returnValue(userId);

        // fake dispatch
        spyOn(fakeRedux, 'dispatch');

        avatars.beforeUploadMyAvatar(avatarId, avatarUrl);

        const payload: IAvatarDataPayload = {
            id: avatarId,
            url: avatarUrl,
            bigUrl: avatarUrl,
            pendingUrl: avatarUrl,
            pendingBigUrl: avatarUrl,
            active: true,
            userId: userId
        };

        expect(fakeAuth.getUserId).toHaveBeenCalled();
        expect(fakeRedux.dispatch).toHaveBeenCalled();
        expect(fakeRedux.dispatch).toHaveBeenCalledWith({
            type: AVATARS_BEFORE_UPLOAD,
            payload:  payload
        });
    });

    it('errorUploadAvatar should dispatch the AVATARS_ERROR_UPLOAD action', () => {
        const avatarId: number = 1;

        // fake dispatch
        spyOn(fakeRedux, 'dispatch');

        avatars.errorUploadAvatar(avatarId);

        const payload: IByIdPayload = {
            id: avatarId
        };

        expect(fakeRedux.dispatch).toHaveBeenCalled();
        expect(fakeRedux.dispatch).toHaveBeenCalledWith({
            type: AVATARS_ERROR_UPLOAD,
            payload: payload
        });
    });

    it('afterUploadMyAvatar should dispatch the AVATARS_AFTER_UPLOAD action', () => {
        const userId: number = 1;
        const avatarId: number = 1;
        const avatar: IAvatarResponse = {
            id: avatarId
        };

        // fake auth
        spyOn(fakeAuth, 'getUserId').and.returnValue(userId);

        // fake dispatch
        spyOn(fakeRedux, 'dispatch');

        avatars.afterUploadMyAvatar(avatarId, avatar);

        const payload: IAvatarAfterUploadPayload = {
            id: avatarId,
            userId: userId,
            avatar: avatar
        };

        expect(fakeAuth.getUserId).toHaveBeenCalled();
        expect(fakeRedux.dispatch).toHaveBeenCalled();
        expect(fakeRedux.dispatch).toHaveBeenCalledWith({
            type: AVATARS_AFTER_UPLOAD,
            payload: payload
        });
    });

    it('deleteMyAvatar should return correct result and dispatch both AVATARS_BEFORE_DELETE and AVATARS_AFTER_DELETE actions', () => {
        const avatarId: number = 1;
        const userId: number = 1;
        const response: string = 'ok';

        const payload: IEntityPayload = {
            id: avatarId,
            entityId: userId
        };

        // fake auth
        spyOn(fakeAuth, 'getUserId').and.returnValue(userId);

        // fake http
        spyOn(fakeHttp, 'delete').and.returnValue(
            Observable.of(response)
        );

        // fake dispatch
        spyOn(fakeRedux, 'dispatch');

        avatars.deleteMyAvatar(avatarId).subscribe(() => {
            expect(fakeAuth.getUserId).toHaveBeenCalled();
            expect(fakeRedux.dispatch).toHaveBeenCalled();
            expect(fakeRedux.dispatch).toHaveBeenCalledWith({
                type: AVATARS_AFTER_DELETE,
                payload: payload
            });

            // http
            expect(fakeHttp.delete).toHaveBeenCalledWith('/avatars/' + avatarId);
        });

        expect(fakeRedux.dispatch).toHaveBeenCalled();
        expect(fakeRedux.dispatch).toHaveBeenCalledWith({
            type: AVATARS_BEFORE_DELETE,
            payload: payload
        });
    });

    it('deleteMyAvatar should dispatch AVATARS_ERROR_DELETE action if an error occurred', () => {
        const avatarId: number = 1;
        const userId: number = 1;
        const errorResponse: string  = 'Some error';
 
        const payload: IEntityPayload = {
            id: avatarId,
            entityId: userId
        };

        // fake auth
        spyOn(fakeAuth, 'getUserId').and.returnValue(userId);

        // fake http
        spyOn(fakeHttp, 'delete').and.returnValue(Observable.throw(errorResponse));

        // fake dispatch
        spyOn(fakeRedux, 'dispatch');

        avatars.deleteMyAvatar(avatarId).subscribe(() => {}, (error) => {
            expect(fakeAuth.getUserId).toHaveBeenCalled();
            expect(error).toEqual(errorResponse);
            expect(fakeRedux.dispatch).toHaveBeenCalled();
            expect(fakeRedux.dispatch).toHaveBeenCalledWith({
                type: AVATARS_ERROR_DELETE,
                payload: payload
            });

            // http
            expect(fakeHttp.delete).toHaveBeenCalledWith('/avatars/' + avatarId);
        });

        expect(fakeRedux.dispatch).toHaveBeenCalled();
        expect(fakeRedux.dispatch).toHaveBeenCalledWith({
            type: AVATARS_BEFORE_DELETE,
            payload: payload
        });
    });
});
