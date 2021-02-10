import { MockBackend } from '@angular/http/testing';
import { Http, BaseRequestOptions } from '@angular/http';
import { TestBed } from '@angular/core/testing';
import { Platform } from 'ionic-angular';

// services
import { DashboardService } from './'

// services
import { SecureHttpService } from 'services/http';
import { ApplicationService } from 'services/application';
import { PersistentStorageService } from 'services/persistent-storage';
import { JwtService } from 'services/jwt';
import { AuthService } from 'services/auth';
import { SiteConfigsService } from 'services/site-configs';

// fakes
import { PlatformMock } from 'ionic-mocks';

import {
    SiteConfigsServiceFake,
    AuthServiceFake,
    JwtFake,
    ReduxFake, 
    ApplicationServiceFake, 
    ApplicationConfigFake,
    StringUtilsFake,
    DeviceFake,
    PersistentStorageMemoryAdapterFake 
} from 'test/fake';

describe('Dashboard service', () => {
    // register service's fakes
    let fakeSiteConfig: SiteConfigsServiceFake;
    let fakePersistentStorage: PersistentStorageService;

    let dashboard: DashboardService; // testable service

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
                    provide: SiteConfigsService,
                    useFactory: (fakeHttp) => new SiteConfigsServiceFake(new ReduxFake(), fakeHttp),
                    deps: [SecureHttpService]
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
        fakeSiteConfig = TestBed.get(SiteConfigsService);
        fakePersistentStorage = TestBed.get(PersistentStorageService);

        // init service
        dashboard = new DashboardService(fakeSiteConfig, fakePersistentStorage);
    });

    it('getComponentIndexByName should return a correct value', () => {
        const componentIndex: number = 0;
        const componentName: string = dashboard.components[componentIndex];

        dashboard.getComponentIndexByName(componentName);

        expect(dashboard.getComponentIndexByName(componentName)).toEqual(componentIndex);
    });

    it('getComponentIndexByName should return -1 if the component not found', () => {
        const componentName: string = 'not_exiting_component';

        dashboard.getComponentIndexByName(componentName);

        expect(dashboard.getComponentIndexByName(componentName)).toEqual(-1);
    });

    it('setActiveSubComponent should correctly set a sub component', () => {
        const subComponentName: string = dashboard.hotListPage;

        spyOn(fakePersistentStorage, 'setValue');
        
        dashboard.setActiveSubComponent(subComponentName);

        expect(fakePersistentStorage.setValue).toHaveBeenCalled();
    });

    it('getActiveComponent should return a correct result', () => {
        const componentName: string = dashboard.components[0];

        spyOn(fakePersistentStorage, 'getValue').and.returnValue(componentName);

        expect(dashboard.getActiveComponent()).toEqual(componentName);
        expect(fakePersistentStorage.getValue).toHaveBeenCalled();
    });

    it('getActiveSubComponent should return a correct result', () => {
        const subComponentName: string = dashboard.hotListPage;

        spyOn(fakePersistentStorage, 'getValue').and.returnValue(subComponentName);

        expect(dashboard.getActiveSubComponent()).toEqual(subComponentName);
        expect(fakePersistentStorage.getValue).toHaveBeenCalled();
    });

    it('setActiveComponent should trigger a type error if a component not found in the list', () => {
        const componentName: string = 'notExistingComponent';

        expect(() => dashboard.setActiveComponent(componentName))
            .toThrow(new TypeError(`Component not found`));
    });

    it('setActiveComponent should correctly set both component and sub component', () => {
        const componentName: string = dashboard.components[0];
        const subComponentName: string = dashboard.hotListPage;

        spyOn(fakePersistentStorage, 'setValue');

        dashboard.setActiveComponent(componentName, subComponentName);

        expect(fakePersistentStorage.setValue).toHaveBeenCalled();
    });

    it('isActiveSubComponent should return a positive boolean value if an active sub component is equals to the argument', () => {
        const activeSubComponent: string = dashboard.hotListPage;

        spyOn(dashboard, 'getActiveSubComponent').and.returnValue(activeSubComponent);

        expect(dashboard.isActiveSubComponent(activeSubComponent)).toBeTruthy();
    });

    it('isActiveSubComponent should return a positive boolean value if an active sub component is empty but the default sub component is equals to the argument', () => {
        const activeSubComponent: string = dashboard.hotListPage;
        spyOn(dashboard, 'defaultSubComponent').and.returnValue(activeSubComponent);

        expect(dashboard.isActiveSubComponent(activeSubComponent)).toBeTruthy();
    });

    it('isActiveSubComponent should return a negative boolean value if an active sub component is not equals to the argument', () => {
        const activeSubComponent: string = dashboard.hotListPage;
        const defaultSubComponent: string = dashboard.browsePage;

        spyOn(dashboard, 'defaultSubComponent').and.returnValue(defaultSubComponent);

        expect(dashboard.isActiveSubComponent(activeSubComponent)).toBeFalsy();
    });

    it('setActiveComponentByIndex should correctly set an active component', () => {
        spyOn(fakePersistentStorage, 'setValue');

        const activeComponent: string = 'profile';
        const activeComponentIndex = dashboard.components.indexOf(activeComponent);
        dashboard.setActiveComponentByIndex(activeComponentIndex);

        expect(fakePersistentStorage.setValue).toHaveBeenCalled();
    });

    it('setActiveComponentByIndex should trigger a range error if index is out of range', () => {
        expect(() => dashboard.setActiveComponentByIndex(dashboard.components.length + 1))
            .toThrow(new RangeError(`The argument must be between 0 and ${dashboard.components.length - 1}`));
    });

    it('defaultSubComponent should return a hotlist page if the hotlist plugin is installed', () => {
        spyOn(fakeSiteConfig, 'isPluginActive').and.returnValue(true);

        expect(dashboard.defaultSubComponent()).toEqual(dashboard.hotListPage);
    });

    it('defaultSubComponent should return a tinder page if the hotlist plugin is not installed and tinder search is allowed', () => {
        spyOn(fakeSiteConfig, 'isPluginActive').and.returnValue(false);
        spyOn(fakeSiteConfig, 'isTinderSearchAllowed').and.returnValue(true);

        expect(dashboard.defaultSubComponent()).toEqual(dashboard.tinderPage);
    });

    it('defaultSubComponent should return a browse page if the hotlist plugin not installed and tinder search is disallowed', () => {
        spyOn(fakeSiteConfig, 'isPluginActive').and.returnValue(false);
        spyOn(fakeSiteConfig, 'isTinderSearchAllowed').and.returnValue(false);

        expect(dashboard.defaultSubComponent()).toEqual(dashboard.browsePage);
    });
});
