import { MockBackend } from '@angular/http/testing';
import { Http, BaseRequestOptions } from '@angular/http';
import { TestBed } from '@angular/core/testing';
import { normalize } from 'normalizr';
import { Observable } from 'rxjs/Rx';

// services
import { VideoImService } from './'
import { SecureHttpService } from 'services/http';
import { ApplicationService } from 'services/application';
import { PersistentStorageService } from 'services/persistent-storage';
import { JwtService } from 'services/jwt';
import { AuthService } from 'services/auth';

// payloads
import {
    IByIdPayload,
    IEntitiesPayload,
    IVideoImCallDataPayload,
    IVideoImActiveInterlocutorDataPayload
} from 'store/payloads'

// states
import {
    IVideoImCallData,
    IVideoImActiveInterlocutorData
} from 'store/states';

// schemas
import { notificationListSchema } from './schemas';

// actions
import {
    VIDEO_IM_ADD_NOTIFICATION,
    VIDEO_IM_AFTER_MARK_NOTIFICATION,
    VIDEO_IM_BEFORE_MARK_NOTIFICATION,
    VIDEO_IM_ERROR_MARK_NOTIFICATION,
    VIDEO_IM_SET_ACTIVE_INTERLOCUTOR_DATA,
    VIDEO_IM_SET_INTERLOCUTOR_SESSION_ID,
    VIDEO_IM_REMOVE_INTERLOCUTOR_SESSION_ID
} from 'store/actions'; 

// fakes
import {
    PersistentStorageMemoryAdapterFake,
    ApplicationConfigFake,
    ApplicationServiceFake,
    JwtFake,
    AuthServiceFake,
    ReduxFake, 
    StringUtilsFake,
    DeviceFake
} from 'test/fake';

// responses
import { INotificationResponse } from './responses';
import { IMapType } from 'store/types';

describe('Video IM service', () => {
    // register service's fakes
    let fakeRedux: ReduxFake;
    let fakeHttp: SecureHttpService;

    let videoIm: VideoImService; // testable service

    beforeEach(() => {
        TestBed.configureTestingModule({
            providers: [
                {
                    provide: ApplicationService,
                    useFactory: (fakeStorage, fakePlatform) => new ApplicationServiceFake(ApplicationConfigFake, new ReduxFake(), fakeStorage, new DeviceFake, new StringUtilsFake, fakePlatform),
                    deps: [PersistentStorageService]
                },
                {
                    provide: PersistentStorageService,
                    useFactory: () => new PersistentStorageService(new PersistentStorageMemoryAdapterFake),
                    deps: []
                },
                {
                    provide: JwtService,
                    useFactory: () => new JwtFake(),
                    deps: []
                },
                {
                    provide: AuthService,
                    useFactory: (fakeStorage, fakeJwt) => new AuthServiceFake(fakeStorage, fakeJwt),
                    deps: [PersistentStorageService, JwtService]
                },
                {
                    provide: SecureHttpService,
                    useFactory: (fakeApplication, fakeHttp, fakeAuth, fakePersistentStorage) => new SecureHttpService(fakePersistentStorage, fakeApplication, fakeHttp, fakeAuth),
                    deps: [ApplicationService, Http, AuthService, PersistentStorageService]
                },
                {
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
        videoIm = new VideoImService(fakeRedux, fakeHttp);
    });

    it('addNotifications should dispatch VIDEO_IM_ADD_NOTIFICATION action', () => {
        const notificationId: number = 1;

        const response: Array<INotificationResponse> = [{
            id: notificationId
        }];

        spyOn(fakeRedux, 'dispatch');

        const payload: IEntitiesPayload = normalize(response, notificationListSchema);

        const expectedArgs = {
            type: VIDEO_IM_ADD_NOTIFICATION,
            payload: payload
        };

        videoIm.addNotifications(response);

        expect(fakeRedux.dispatch).toHaveBeenCalled();
        expect(fakeRedux.dispatch).toHaveBeenCalledWith(expectedArgs);
    });

    it('setSessionId should dispatch VIDEO_IM_SET_INTERLOCUTOR_SESSION_ID action', () => {
        const userId: number = 1;
        const sessionId: string = 'qwerty';
        const payload: IVideoImCallDataPayload = {
            userId: userId,
            sessionId: sessionId
        };

        // fake dispatch
        spyOn(fakeRedux, 'dispatch');

        videoIm.setSessionId(userId, sessionId);

        expect(fakeRedux.dispatch).toHaveBeenCalled();
        expect(fakeRedux.dispatch).toHaveBeenCalledWith({
            type: VIDEO_IM_SET_INTERLOCUTOR_SESSION_ID,
            payload: payload
        });
    });

    it('removeSessionId should dispatch VIDEO_IM_REMOVE_INTERLOCUTOR_SESSION_ID action', () => {
        const userId: number = 1;
        const payload: IByIdPayload = {
            id: userId
        };

        // fake dispatch
        spyOn(fakeRedux, 'dispatch');

        videoIm.removeSessionId(userId);

        expect(fakeRedux.dispatch).toHaveBeenCalled();
        expect(fakeRedux.dispatch).toHaveBeenCalledWith({
            type: VIDEO_IM_REMOVE_INTERLOCUTOR_SESSION_ID,
            payload: payload
        });
    });

    it('markNotifications should return correct result and dispatch both VIDEO_IM_BEFORE_MARK_NOTIFICATION and VIDEO_IM_AFTER_MARK_NOTIFICATION actions', () => {
        const userId: number = 1;
        const sessionId: string = 'qwerty';
        const response: string = 'ok';
        const payload: IByIdPayload = {
            id: userId
        };
        const callData: IVideoImCallData = {
            sessionId: sessionId,
            userId: userId
        };

        // fake http
        spyOn(fakeHttp, 'put').and.returnValue(
            Observable.of(response)
        );

        // fake dispatch
        spyOn(fakeRedux, 'dispatch');

        videoIm.markNotifications(userId, sessionId).subscribe(response => {
            expect(fakeRedux.dispatch).toHaveBeenCalled();
            expect(fakeRedux.dispatch).toHaveBeenCalledWith({
                type: VIDEO_IM_AFTER_MARK_NOTIFICATION,
                payload: payload
            });

            // http
            expect(fakeHttp.put).toHaveBeenCalledWith('/video-im/notifications/me', callData);
        });

        expect(fakeRedux.dispatch).toHaveBeenCalled();
        expect(fakeRedux.dispatch).toHaveBeenCalledWith({
            type: VIDEO_IM_BEFORE_MARK_NOTIFICATION,
            payload: payload
        });
    });

    it('markNotifications should dispatch VIDEO_IM_ERROR_MARK_NOTIFICATION action if an error occurred', () => {
        const userId: number = 1;
        const sessionId: string = 'qwerty';
        const errorResponse: string  = 'Some error';
        const payload: IByIdPayload = {
            id: userId
        };
        const callData: IVideoImCallData = {
            sessionId: sessionId,
            userId: userId
        };

        // fake http
        spyOn(fakeHttp, 'put').and.returnValue(Observable.throw(errorResponse));

        // fake dispatch
        spyOn(fakeRedux, 'dispatch');

        videoIm.markNotifications(userId, sessionId).subscribe(() => {}, (error) => {
            expect(error).toEqual(errorResponse);
            expect(fakeRedux.dispatch).toHaveBeenCalled();
            expect(fakeRedux.dispatch).toHaveBeenCalledWith({
                type: VIDEO_IM_ERROR_MARK_NOTIFICATION,
                payload: payload
            });

            // http
            expect(fakeHttp.put).toHaveBeenCalledWith('/video-im/notifications/me', callData);
        });

        expect(fakeRedux.dispatch).toHaveBeenCalled();
        expect(fakeRedux.dispatch).toHaveBeenCalledWith({
            type: VIDEO_IM_BEFORE_MARK_NOTIFICATION,
            payload: payload
        });
    });

    it('setActiveInterlocutorData should dispatch VIDEO_IM_SET_ACTIVE_INTERLOCUTOR_DATA action', () => {
        const userId: number = 1;
        const isMeInitiator: boolean = false;
        const payload: IVideoImActiveInterlocutorDataPayload = {
            userId: userId,
            isMeInitiator: isMeInitiator
        };

        // fake dispatch
        spyOn(fakeRedux, 'dispatch');

        videoIm.setActiveInterlocutorData(userId, isMeInitiator);

        expect(fakeRedux.dispatch).toHaveBeenCalled();
        expect(fakeRedux.dispatch).toHaveBeenCalledWith({
            type: VIDEO_IM_SET_ACTIVE_INTERLOCUTOR_DATA,
            payload: payload
        });
    });

    it('removeActiveInterlocutorData should dispatch VIDEO_IM_SET_ACTIVE_INTERLOCUTOR_DATA action', () => {
        const userId: number = 1;
        const isMeInitiator: boolean = true;
        const activeInterlocutorData: IVideoImActiveInterlocutorData = {
            userId: userId,
            isMeInitiator: isMeInitiator
        };
        const payload: IVideoImActiveInterlocutorDataPayload = {
            userId: 0,
            isMeInitiator: false
        };

        // fake dispatch
        spyOn(fakeRedux, 'dispatch');

        // fake active interlocutor data
        spyOn(videoIm, 'getActiveInterlocutorData').and.returnValue(activeInterlocutorData);

        videoIm.removeActiveInterlocutorData(userId);

        // active interlocutor data
        expect(videoIm.getActiveInterlocutorData).toHaveBeenCalled();

        expect(fakeRedux.dispatch).toHaveBeenCalled();
        expect(fakeRedux.dispatch).toHaveBeenCalledWith({
            type: VIDEO_IM_SET_ACTIVE_INTERLOCUTOR_DATA,
            payload: payload
        });
    });

    it('sendNotification should return succeeded response', () => {
        const sessionId: string = 'qwerty';
        const interlocutorId: number = 1;
        const notification: IMapType<any> = {};
        const response: string = 'ok';

        // fake session id
        spyOn(videoIm, 'getSessionId').and.returnValue(sessionId);

        // fake http
        spyOn(fakeHttp, 'post').and.returnValue(
            Observable.of(response)
        );

        videoIm.sendNotification(interlocutorId, notification).subscribe( (response) => {
            // session id
            expect(videoIm.getSessionId).toHaveBeenCalled();

            // http
            expect(fakeHttp.post).toHaveBeenCalledWith('/video-im/notifications', {
                sessionId: sessionId,
                interlocutorId: interlocutorId,
                notification: notification
            });
        });
    });

    it('sendNotification should return failure response', () => {
        const sessionId: string = 'qwerty';
        const interlocutorId: number = 1;
        const notification: IMapType<any> = {};
        const errorResponse: string  = 'Some error';

        // fake session id
        spyOn(videoIm, 'getSessionId').and.returnValue(sessionId);

        // fake http
        spyOn(fakeHttp, 'post').and.returnValue(Observable.throw(errorResponse));

        videoIm.sendNotification(interlocutorId, notification).subscribe(() => {}, (error) => {
            expect(error).toEqual(errorResponse);

            // session id
            expect(videoIm.getSessionId).toHaveBeenCalled();

            // http
            expect(fakeHttp.post).toHaveBeenCalledWith('/video-im/notifications', {
                sessionId: sessionId,
                interlocutorId: interlocutorId,
                notification: notification
            });
        });
    });

    it('watchFirstCallingUserId should return a correct result', () => {
        const userId: number = 1;

        // fake the method
        spyOn(videoIm, 'watchFirstCallingUserId').and.returnValue(
            Observable.of(userId)
        );

        videoIm.watchFirstCallingUserId().subscribe(response => {
            expect(response).toEqual(userId);
        });
    });

    it('watchActiveInterlocutorData should return a correct result', () => {
        const activeInterlocutorData: IVideoImActiveInterlocutorData = {
            userId: 1,
            isMeInitiator: false
        };

        // fake the method
        spyOn(videoIm, 'watchActiveInterlocutorData').and.returnValue(
            Observable.of(activeInterlocutorData)
        );

        videoIm.watchActiveInterlocutorData().subscribe(response => {
            expect(response).toEqual(activeInterlocutorData);
        });
    });

    it('watchActiveCallerNotifications should return a correct result', () => {
        // fake the method
        spyOn(videoIm, 'watchActiveInterlocutorNotifications').and.returnValue(
            Observable.of([])
        );
    });
});
