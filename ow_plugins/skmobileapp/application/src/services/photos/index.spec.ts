import { TestBed } from '@angular/core/testing';
import { MockBackend } from '@angular/http/testing';
import { Http, BaseRequestOptions } from '@angular/http';
import { NgRedux } from '@angular-redux/store';
import { Observable } from 'rxjs/Rx';
import { Platform } from 'ionic-angular';

// services
import { PhotosService } from './';
import { SecureHttpService } from 'services/http';
import { ApplicationService } from 'services/application';
import { PersistentStorageService } from 'services/persistent-storage';
import { JwtService } from 'services/jwt';
import { AuthService } from 'services/auth';
import { MatchActionsService } from 'services/match-actions';
import { StringUtilsService } from 'services/string-utils';

// payloads
import {
    IByIdPayload,
    IAvatarDataPayload, 
    IAvatarAfterUploadPayload,
    IEntityPayload,
    IPhotoDataPayload,
    IPhotosAfterUploadPayload
} from 'store/payloads';

// store
import { 
    IPhotoData, IAvatarData
} from 'store/states';

import {
    PHOTOS_BEFORE_UPLOAD,
    PHOTOS_AFTER_UPLOAD,
    PHOTOS_ERROR_UPLOAD,
    PHOTOS_BEFORE_DELETE,
    PHOTOS_AFTER_DELETE,
    PHOTOS_ERROR_DELETE,
    PHOTOS_BEFORE_SET_AS_AVATAR,
    PHOTOS_AFTER_SET_AS_AVATAR,
    PHOTOS_ERROR_SET_AS_AVATAR
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
import { IPhotoResponse, IAvatarResponse } from 'services/user/responses';

describe('Photos service', () => {
    // register service's fakes
    let fakeRedux: ReduxFake;
    let fakeHttp: SecureHttpService;
    let fakeAuth: AuthService;
    let fakeStringUtils: StringUtilsService;

    let photos: PhotosService; // testable service

    beforeEach(() => { 
        TestBed.configureTestingModule({
            providers: [{
                    provide: ApplicationService,
                    useFactory: (fakeStorage, fakeStringUtils, fakePlatform) => new ApplicationServiceFake(ApplicationConfigFake, new ReduxFake(), fakeStorage, new DeviceFake, fakeStringUtils, fakePlatform),
                    deps: [PersistentStorageService, StringUtilsService, Platform]
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
                    provide: StringUtilsService,
                    useFactory: () => new StringUtilsFake(),
                    deps: []
                }, {
                    provide: Platform, 
                    useFactory: () => PlatformMock.instance(), 
                    deps: [] 
                },
                MatchActionsService,
                PhotosService                
            ]}
        );

        // init service's fakes
        fakeRedux = TestBed.get(NgRedux);
        fakeHttp = TestBed.get(SecureHttpService);
        fakeAuth = TestBed.get(AuthService);
        fakeStringUtils = TestBed.get(StringUtilsService);

        // init service
        photos = TestBed.get(PhotosService);
    });

    it('watchMyAllPhotos should return a correct result', () => {
        const photoId: number = 1;
        const photo: IPhotoData = {
            id: photoId
        };

        // fake the method
        spyOn(photos, 'watchMyAllPhotos').and.returnValue(
            Observable.of([photo])
        );

        photos.watchMyAllPhotos().subscribe(photoList => {
            expect(photoList).toEqual([
                photo
            ]); 
        });
    });

    it('beforeUploadMyPhoto should dispatch the PHOTOS_BEFORE_UPLOAD action', () => {
        const photoUrl: string = 'test';
        const userId: number = 1;
        const photoId: number = 1;

        // fake auth
        spyOn(fakeAuth, 'getUserId').and.returnValue(userId);

        // fake dispatch
        spyOn(fakeRedux, 'dispatch');

        photos.beforeUploadMyPhoto(photoId, photoUrl);

        const payload: IPhotoDataPayload = {
            id: photoId,
            url: photoUrl,
            bigUrl: photoUrl,
            approved: true,
            userId: userId
        };

        expect(fakeAuth.getUserId).toHaveBeenCalled();
        expect(fakeRedux.dispatch).toHaveBeenCalled();
        expect(fakeRedux.dispatch).toHaveBeenCalledWith({
            type: PHOTOS_BEFORE_UPLOAD,
            payload: payload
        });
    });

    it('errorUploadPhoto should dispatch the PHOTOS_ERROR_UPLOAD action', () => {
        const photoId: number = 1;

        // fake dispatch
        spyOn(fakeRedux, 'dispatch');

        photos.errorUploadPhoto(photoId);

        const payload: IByIdPayload = {
            id: photoId
        };

        expect(fakeRedux.dispatch).toHaveBeenCalled();
        expect(fakeRedux.dispatch).toHaveBeenCalledWith({
            type: PHOTOS_ERROR_UPLOAD,
            payload: payload
        });
    });

    it('afterUploadMyPhoto should dispatch the PHOTOS_AFTER_UPLOAD action', () => {
        const userId: number = 1;
        const photoId: number = 1;
        const photo: IPhotoResponse = {
            id: photoId
        };

        // fake auth
        spyOn(fakeAuth, 'getUserId').and.returnValue(userId);

        // fake dispatch
        spyOn(fakeRedux, 'dispatch');

        photos.afterUploadMyPhoto(photoId, photo);

        const payload: IPhotosAfterUploadPayload = {
            id: photoId,
            userId: userId,
            photo: photo
        };

        expect(fakeAuth.getUserId).toHaveBeenCalled();
        expect(fakeRedux.dispatch).toHaveBeenCalled();
        expect(fakeRedux.dispatch).toHaveBeenCalledWith({
            type: PHOTOS_AFTER_UPLOAD,
            payload: payload
        });
    });

    it('deleteMyPhoto should return correct result and dispatch both PHOTOS_BEFORE_DELETE and PHOTOS_AFTER_DELETE actions', () => {
        const photoId: number = 1;
        const userId: number = 1;
        const response: string = 'ok';

        const payload: IEntityPayload = {
            id: photoId,
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

        photos.deleteMyPhoto(photoId).subscribe(() => {
            expect(fakeAuth.getUserId).toHaveBeenCalled();
            expect(fakeRedux.dispatch).toHaveBeenCalled();
            expect(fakeRedux.dispatch).toHaveBeenCalledWith({
                type: PHOTOS_AFTER_DELETE,
                payload: payload
            });

            // http
            expect(fakeHttp.delete).toHaveBeenCalledWith('/photos/' + photoId);
        });

        expect(fakeRedux.dispatch).toHaveBeenCalled();
        expect(fakeRedux.dispatch).toHaveBeenCalledWith({
            type: PHOTOS_BEFORE_DELETE,
            payload: payload
        });
    });

    it('deleteMyPhoto should dispatch PHOTOS_ERROR_DELETE action if an error occurred', () => {
        const photoId: number = 1;
        const userId: number = 1;
        const errorResponse: string  = 'Some error';
 
        const payload: IEntityPayload = {
            id: photoId,
            entityId: userId
        };

        // fake auth
        spyOn(fakeAuth, 'getUserId').and.returnValue(userId);

        // fake http
        spyOn(fakeHttp, 'delete').and.returnValue(Observable.throw(errorResponse));

        // fake dispatch
        spyOn(fakeRedux, 'dispatch');

        photos.deleteMyPhoto(photoId).subscribe(() => {}, (error) => {
            expect(fakeAuth.getUserId).toHaveBeenCalled();
            expect(error).toEqual(errorResponse);
            expect(fakeRedux.dispatch).toHaveBeenCalled();
            expect(fakeRedux.dispatch).toHaveBeenCalledWith({
                type: PHOTOS_ERROR_DELETE,
                payload: payload
            });

            // http
            expect(fakeHttp.delete).toHaveBeenCalledWith('/photos/' + photoId);
        });

        expect(fakeRedux.dispatch).toHaveBeenCalled();
        expect(fakeRedux.dispatch).toHaveBeenCalledWith({
            type: PHOTOS_BEFORE_DELETE,
            payload: payload
        });
    });

    it('setPhotoAsMyAvatar should return correct result and dispatch both PHOTOS_BEFORE_SET_AS_AVATAR and PHOTOS_AFTER_SET_AS_AVATAR actions', () => {
        const photoId: number = 1;
        const fakeId: string = 'test';
        const url: string = 'test';
        const userId: number = 1;
        const avatarId: number = 1;

        const avatarResponse: IAvatarResponse = {
            id: avatarId
        };

        const avatar: IAvatarData = {
            id: fakeId,
            url: url,
            bigUrl: url,
            pendingUrl: url,
            pendingBigUrl: url,
            active: true,
            userId: userId
        };
 
        // fake string utils
        spyOn(fakeStringUtils, 'getRandomString').and.returnValue(fakeId);
 
        // fake auth
        spyOn(fakeAuth, 'getUserId').and.returnValue(userId);

        // fake http
        spyOn(fakeHttp, 'put').and.returnValue(
            Observable.of(avatarResponse)
        );

        // fake dispatch
        spyOn(fakeRedux, 'dispatch');

        const afterSetAvatarPayload: IAvatarAfterUploadPayload = {
            id: fakeId,
            userId: userId,
            avatar: avatarResponse
        };
  
        photos.setPhotoAsMyAvatar(photoId, url).subscribe(() => {
            expect(fakeAuth.getUserId).toHaveBeenCalled();
            expect(fakeRedux.dispatch).toHaveBeenCalled();
            expect(fakeRedux.dispatch).toHaveBeenCalledWith({
                type: PHOTOS_AFTER_SET_AS_AVATAR,
                payload: afterSetAvatarPayload
            });

            // http
            expect(fakeHttp.put).toHaveBeenCalledWith('/photos/' + photoId + '/setAsAvatar');
        });

        const beforeSetAvatarPayload: IAvatarDataPayload = {
            ...avatar
        };

        expect(fakeRedux.dispatch).toHaveBeenCalled();
        expect(fakeRedux.dispatch).toHaveBeenCalledWith({
            type: PHOTOS_BEFORE_SET_AS_AVATAR,
            payload: beforeSetAvatarPayload
        });
    });

    it('setPhotoAsMyAvatar should dispatch PHOTOS_ERROR_SET_AS_AVATAR action if an error occurred', () => {
        const photoId: number = 1;
        const url: string = 'test';
        const userId: number = 1;
        const fakeId: string = 'test';
        const errorResponse: string  = 'Some error';

        const avatar: IAvatarData = {
            id: fakeId,
            url: url,
            bigUrl: url,
            pendingUrl: url,
            pendingBigUrl: url,
            active: true,
            userId: userId
        };
 
        // fake string utils
        spyOn(fakeStringUtils, 'getRandomString').and.returnValue(fakeId);
 
        // fake auth
        spyOn(fakeAuth, 'getUserId').and.returnValue(userId);

        // fake http
        spyOn(fakeHttp, 'put').and.returnValue(Observable.throw(errorResponse));

        // fake dispatch
        spyOn(fakeRedux, 'dispatch');

        photos.setPhotoAsMyAvatar(photoId, url).subscribe(() => {}, (error) => {
            const errorSetAvatarPayload: IByIdPayload = {
                id: fakeId
            };

            expect(fakeAuth.getUserId).toHaveBeenCalled();
            expect(error).toEqual(errorResponse);
            expect(fakeRedux.dispatch).toHaveBeenCalled();
            expect(fakeRedux.dispatch).toHaveBeenCalledWith({
                type: PHOTOS_ERROR_SET_AS_AVATAR,
                payload: errorSetAvatarPayload
            });

            // http
            expect(fakeHttp.put).toHaveBeenCalledWith('/photos/' + photoId + '/setAsAvatar');
        });

        const beforeSetAvatarPayload: IAvatarDataPayload = {
            ...avatar
        };

        expect(fakeRedux.dispatch).toHaveBeenCalled();
        expect(fakeRedux.dispatch).toHaveBeenCalledWith({
            type: PHOTOS_BEFORE_SET_AS_AVATAR,
            payload: beforeSetAvatarPayload
        });
    });
});
