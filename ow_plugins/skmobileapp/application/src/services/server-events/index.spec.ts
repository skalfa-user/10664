import { TestBed } from '@angular/core/testing';
import { NgZone } from '@angular/core';
import { Observable } from 'rxjs/Rx';
import { Platform } from 'ionic-angular';

// services
import { ServerEventsService } from './';
import { ApplicationService } from 'services/application';
import { AuthService } from 'services/auth';
import { JwtService } from 'services/jwt';
import { PersistentStorageService } from 'services/persistent-storage';

// responses
import { IServerEventsResponse } from './responses';

// fakes
import { PlatformMock } from 'ionic-mocks';

import {
    ServerEventsServiceFake,
    AuthServiceFake,
    JwtFake,
    ApplicationServiceFake,
    ReduxFake,
    StringUtilsFake,
    ApplicationConfigFake, 
    DeviceFake,
    PersistentStorageMemoryAdapterFake } from 'test/fake';

describe('Server events service', () => {

    let serverEvents: ServerEventsService; // testable service

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
                    provide: Platform, 
                    useFactory: () => PlatformMock.instance(), 
                    deps: [] 
                }, {
                    provide: ServerEventsService, 
                    useFactory: (NgZoneFake, authFake, applicationFake, fakePersistentStorage) => new ServerEventsServiceFake(fakePersistentStorage, NgZoneFake, authFake, applicationFake), 
                    deps: [NgZone, AuthService, ApplicationService, PersistentStorageService] 
                }
            ]}
        );

        // init application service
        serverEvents = TestBed.get(ServerEventsService);
    });

    it('watchData should return data for a specified data channel', () => {
        const channelName: string = 'test_channel';
        const channelData: IServerEventsResponse = {
            channel: channelName,
            data: 'test'
        };

        // fake the method
        spyOn(serverEvents, 'watchData').and.returnValue(Observable.of(channelData));

        serverEvents.watchData(channelName).subscribe(response => {
            expect(response).toEqual(channelData); 
        });
    });
});

