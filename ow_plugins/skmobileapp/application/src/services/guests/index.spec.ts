import { normalize } from 'normalizr';
import { Observable } from 'rxjs/Rx';
import { MockBackend } from '@angular/http/testing';
import { Http, BaseRequestOptions } from '@angular/http';
import { TestBed } from '@angular/core/testing';
import { Platform } from 'ionic-angular';

// services
import { GuestsService } from './'

// services
import { SecureHttpService } from 'services/http';
import { ApplicationService } from 'services/application';
import { PersistentStorageService } from 'services/persistent-storage';
import { JwtService } from 'services/jwt';
import { AuthService } from 'services/auth';

// payloads
import {
    IEntitiesPayload,
    IByIdPayload
} from 'store/payloads';

// store
import { 
    IUser, 
    IAvatarData, 
    IMatchAction, 
    IGuestData 
} from 'store/states';

import { IGuestListItem } from 'store/reducers';

import { 
    GUESTS_SET, 
    GUESTS_BEFORE_DELETE, 
    GUESTS_AFTER_DELETE, 
    GUESTS_ERROR_DELETE ,
    GUESTS_BEFORE_MARK_ALL_READ,
    GUESTS_AFTER_MARK_ALL_READ,
    GUESTS_ERROR_MARK_ALL_READ,
    GUESTS_MARK_READ,
    GUESTS_MARK_ALL_NOTIFIED
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

// responses
import { IGuestResponse } from './responses';  

// schemas
import { guestListSchema } from './schemas';

describe('Guests service', () => {
    // register service's fakes
    let fakeRedux: ReduxFake;
    let fakeHttp: SecureHttpService;

    let guest: GuestsService; // testable service

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

        // init guest service
        guest = new GuestsService(fakeRedux, fakeHttp);
    });

    it('setGuests should dispatch GUESTS_SET action', () => {
        const guestId: number = 1;
        const response: Array<IGuestResponse> = [{
            id: guestId
        }];

        const payload: IEntitiesPayload = normalize(response, guestListSchema);

        const expectedArgs = {
            type: GUESTS_SET,
            payload: payload
        };

        spyOn(fakeRedux, 'dispatch');
        guest.setGuests(response);

        expect(fakeRedux.dispatch).toHaveBeenCalled();
        expect(fakeRedux.dispatch).toHaveBeenCalledWith(expectedArgs);
    });

    it('watchNotNotifiedGuestCount should return a correct result of not notified guests count', () => {
        const notNotifiedGuestCount: number = 10;

        // fake the method
        spyOn(guest, 'watchNotNotifiedGuestCount').and.returnValue(
            Observable.of(notNotifiedGuestCount)
        );

        guest.watchNotNotifiedGuestCount().subscribe(count => {
            expect(count).toEqual(notNotifiedGuestCount); 
        });
    });

    it('watchNewGuestCount should return a correct new guests count', () => {
        const newGuestCount: number = 10;

        // fake the method
        spyOn(guest, 'watchNewGuestCount').and.returnValue(
            Observable.of(newGuestCount)
        );

        guest.watchNewGuestCount().subscribe(count => {
            expect(count).toEqual(newGuestCount); 
        });
    });

    it('watchIsGuestsFetched should return a correct boolean value', () => {
        const isFetched: boolean = true;

        // fake the method
        spyOn(guest, 'watchIsGuestsFetched').and.returnValue(
            Observable.of(isFetched)
        );

        guest.watchIsGuestsFetched().subscribe(isDataFetched => {
            expect(isDataFetched).toEqual(isFetched); 
        });
    });

    it('watchGuestList should return a correct result', () => {
        const guestId: number = 1;
        const userId: number = 1;
        const avatarId: number = 1;
        const matchActionId: number = 1;

        const guestData: IGuestData = {
            id: guestId,
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

        const result: IGuestListItem = {
            guest: guestData,
            user: userData, 
            avatar: avatarData,
            matchAction: matchActionData
        };

        // fake the method
        spyOn(guest, 'watchGuestList').and.returnValue(
            Observable.of(result)
        );

        guest.watchGuestList().subscribe(response => {
            expect(response).toEqual(result);
        });
    });

    it('watchGuestList should return an undefined value if guest list empty', () => {
        // fake the method
        spyOn(guest, 'watchGuestList').and.returnValue(
            Observable.of(undefined)
        );

        guest.watchGuestList().subscribe(response => {
            expect(response).toBeUndefined();
        });
    });

    it('deleteGuest should return correct result and dispatch both GUESTS_BEFORE_DELETE and GUESTS_AFTER_DELETE actions', () => {
        const guestId: number = 1;
        const response: string = 'ok';

        // fake http
        spyOn(fakeHttp, 'delete').and.returnValue(
            Observable.of(response)
        );

        // fake dispatch
        spyOn(fakeRedux, 'dispatch');

        const payload: IByIdPayload = {
            id: guestId
        };

        guest.deleteGuest(guestId).subscribe(() => {
            expect(fakeRedux.dispatch).toHaveBeenCalled();
            expect(fakeRedux.dispatch).toHaveBeenCalledWith({
                type: GUESTS_AFTER_DELETE,
                payload: payload
            });

            // http
            expect(fakeHttp.delete).toHaveBeenCalledWith('/guests/' + guestId);
        });

        expect(fakeRedux.dispatch).toHaveBeenCalled();
        expect(fakeRedux.dispatch).toHaveBeenCalledWith({
            type: GUESTS_BEFORE_DELETE,
            payload: payload
        });
    });

    it('deleteGuest should dispatch GUESTS_ERROR_DELETE action if an error occurred', () => {
        const guestId: number = 1;
        const errorResponse: string  = 'Some error';
 
        const payload: IByIdPayload = {
            id: guestId
        };

        // fake http
        spyOn(fakeHttp, 'delete').and.returnValue(Observable.throw(errorResponse));

        // fake dispatch
        spyOn(fakeRedux, 'dispatch');

        guest.deleteGuest(guestId).subscribe(() => {}, (error) => {
            expect(error).toEqual(errorResponse);
            expect(fakeRedux.dispatch).toHaveBeenCalled();
            expect(fakeRedux.dispatch).toHaveBeenCalledWith({
                type: GUESTS_ERROR_DELETE,
                payload: payload
            });

            // http
            expect(fakeHttp.delete).toHaveBeenCalledWith('/guests/' + guestId);
        });

        expect(fakeRedux.dispatch).toHaveBeenCalled();
        expect(fakeRedux.dispatch).toHaveBeenCalledWith({
            type: GUESTS_BEFORE_DELETE,
            payload: payload
        });
    });

    it('markAllGuestsAsNotified should dispatch GUESTS_MARK_ALL_NOTIFIED action', () => {
        // fake dispatch
        spyOn(fakeRedux, 'dispatch');

        guest.markAllGuestsAsNotified();

        expect(fakeRedux.dispatch).toHaveBeenCalled();
        expect(fakeRedux.dispatch).toHaveBeenCalledWith({
            type: GUESTS_MARK_ALL_NOTIFIED,
            payload: {}
        });
    });

    it('markAllGuestsAsRead should return correct result and dispatch both GUESTS_BEFORE_MARK_ALL_READ and GUESTS_AFTER_MARK_ALL_READ actions', () => {
        const response: string = 'ok';

        // fake http
        spyOn(fakeHttp, 'put').and.returnValue(
            Observable.of(response)
        );

        // fake dispatch
        spyOn(fakeRedux, 'dispatch');

        guest.markAllGuestsAsRead().subscribe(() => {
            expect(fakeRedux.dispatch).toHaveBeenCalled();
            expect(fakeRedux.dispatch).toHaveBeenCalledWith({
                type: GUESTS_AFTER_MARK_ALL_READ,
                payload: {}
            });

            // http
            expect(fakeHttp.put).toHaveBeenCalledWith('/guests/me/mark-all-as-read');
        });

        expect(fakeRedux.dispatch).toHaveBeenCalled();
        expect(fakeRedux.dispatch).toHaveBeenCalledWith({
            type: GUESTS_BEFORE_MARK_ALL_READ,
            payload: {}
        });
    });

    it('markAllGuestsAsRead should dispatch GUESTS_ERROR_MARK_ALL_READ action if an error occurred', () => {
        const errorResponse: string  = 'Some error';
 
        // fake http
        spyOn(fakeHttp, 'put').and.returnValue(Observable.throw(errorResponse));

        // fake dispatch
        spyOn(fakeRedux, 'dispatch');

        guest.markAllGuestsAsRead().subscribe(() => {}, (error) => {
            expect(error).toEqual(errorResponse);
            expect(fakeRedux.dispatch).toHaveBeenCalled();
            expect(fakeRedux.dispatch).toHaveBeenCalledWith({
                type: GUESTS_ERROR_MARK_ALL_READ,
                payload: {}
            });

            // http
            expect(fakeHttp.put).toHaveBeenCalledWith('/guests/me/mark-all-as-read');
        });

        expect(fakeRedux.dispatch).toHaveBeenCalled();
        expect(fakeRedux.dispatch).toHaveBeenCalledWith({
            type: GUESTS_BEFORE_MARK_ALL_READ,
            payload: {}
        });
    });

    it('markGuestsAsRead should dispatch GUESTS_MARK_READ action', () => {
        const guestId: number = 1;

        // fake dispatch
        spyOn(fakeRedux, 'dispatch');

        guest.markGuestsAsRead(guestId);

        const payload: IByIdPayload = {
            id: guestId
        };

        expect(fakeRedux.dispatch).toHaveBeenCalled();
        expect(fakeRedux.dispatch).toHaveBeenCalledWith({
            type: GUESTS_MARK_READ,
            payload: payload
        });
    });
});
