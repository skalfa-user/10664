import { TestBed } from '@angular/core/testing';
import { NgRedux } from '@angular-redux/store';
import { normalize } from 'normalizr';
import { MockBackend } from '@angular/http/testing';
import { Http, BaseRequestOptions } from '@angular/http';
import { Observable } from 'rxjs/Rx';
import { Platform } from 'ionic-angular';

// services
import { PermissionsService } from './';
import { SecureHttpService } from 'services/http';
import { ApplicationService } from 'services/application';
import { PersistentStorageService } from 'services/persistent-storage';
import { JwtService } from 'services/jwt';
import { AuthService } from 'services/auth';

// store
import { IAppState } from 'store';
import { PERMISSIONS_UPDATE } from 'store/actions'; 
import { IPermission } from 'store/states'; 

// schemas
import { permissionListSchema } from 'services/permissions/schemas';

// responses
import { IPermissionResponse } from 'services/user/responses';

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

// payloads
import {
    IEntitiesPayload 
} from 'store/payloads';

describe('User permissions service', () => {
    // register service's fakes
    let fakeRedux: ReduxFake;
    let fakeAuth: AuthServiceFake;
    let fakeHttp: SecureHttpService;

    let permissions: PermissionsService; // testable service

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
        fakeAuth = TestBed.get(AuthService);
        fakeHttp = TestBed.get(SecureHttpService);
 
        // init service
        permissions = new PermissionsService(fakeHttp, fakeRedux as NgRedux<IAppState>, fakeAuth);
    });

    it('updatePermissions should dispatch PERMISSIONS_UPDATE action', () => {
        const permissionList: Array<IPermissionResponse> = [
            {
                id: '1',
                permission: 'test',
                isPromoted: false,
                isAllowedAfterTracking: false,
                isAllowed: false,
                creditsCost: 0,
                authorizedByCredits: true,
                user: {
                    id: 1
                }
            }
        ];

        const payload: IEntitiesPayload = normalize(permissionList, permissionListSchema);

        const expectedArgs = {
            type: PERMISSIONS_UPDATE,
            payload: payload
        };

        spyOn(fakeRedux, 'dispatch');
        permissions.updatePermissions(permissionList);

        expect(fakeRedux.dispatch).toHaveBeenCalled();
        expect(fakeRedux.dispatch).toHaveBeenCalledWith(expectedArgs);
    });

    it('watchMe should return a correct result', () => {
        const permissionId: string = 'test';
        const permissionName: string = 'test_permission';

        const permission: IPermission = {
            id: permissionId,
            permission: permissionName
        };

        // fake the method
        spyOn(permissions, 'watchMe').and.returnValue(
            Observable.of(permission)
        );

        permissions.watchMe(permissionName).subscribe(response => {
            expect(response).toEqual(permission);
        });
    });

    it('watchMeGroup should return correct result', () => {
        const permissionId1: string = 'permission1';
        const permissionId2: string = 'permission2';

        const permissionList: Array<IPermission> = [{
            id: permissionId1
        },
        {
            id: permissionId2
        }];

        // fake the method
        spyOn(permissions, 'watchMeGroup').and.returnValue(
            Observable.of(permissionList)
        );

        permissions.watchMeGroup([
            permissionId1,
            permissionId2
        ]).subscribe(changedPermissions => {
            expect(changedPermissions).toEqual(permissionList); 
        });
    });

    it('trackAction should return correct result', () => {
        const groupName: string = 'test';
        const actionName: string = 'test';
        const extra: {} = {};
        const response: string = 'ok';

        // fake http
        spyOn(fakeHttp, 'post').and.returnValue(
            Observable.of(response)
        );

        permissions.trackAction(groupName, actionName, extra).subscribe(data => {
            expect(fakeHttp.post).toHaveBeenCalledWith('/permissions/track-actions', {
                groupName: groupName,
                actionName: actionName,
                extra: extra
            });
            expect(data).toEqual(response);
        });
    });
});
