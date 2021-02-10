import { ComponentFixture, TestBed } from '@angular/core/testing';
import { Component, DebugElement, ViewChild } from '@angular/core';
import { AutoSizeDirective } from './';
import { By } from '@angular/platform-browser';
import { MockBackend } from '@angular/http/testing';
import { Http, BaseRequestOptions } from '@angular/http';
import { NgRedux } from '@angular-redux/store';
import { Device } from '@ionic-native/device';
import { Platform } from 'ionic-angular';
import { FormsModule } from '@angular/forms';

// services
import { SiteConfigsService } from 'services/site-configs';
import { SecureHttpService } from 'services/http';
import { ApplicationService } from 'services/application';
import { PersistentStorageService } from 'services/persistent-storage';
import { JwtService } from 'services/jwt';
import { AuthService } from 'services/auth';
import { StringUtilsService } from 'services/string-utils';

// fakes
import { PlatformMock } from 'ionic-mocks';

import {
    AuthServiceFake,
    JwtFake,
    ReduxFake, 
    ApplicationServiceFake, 
    ApplicationConfigFake, 
    PersistentStorageMemoryAdapterFake } from 'test/fake';

// test component
@Component({
    template: `<div autoSize ngDefaultControl [(ngModel)]="value"><textarea></textarea></div>`
})

class TestAutoSizeComponent {
    @ViewChild(AutoSizeDirective) autoSize: AutoSizeDirective;
}

describe('Auto size directive', () => {
    let fixture: ComponentFixture<TestAutoSizeComponent>;
    let inputEl: DebugElement;
    let config: SiteConfigsService;

    beforeEach(() => {
        TestBed.configureTestingModule({
            imports: [
                FormsModule
              ],
            declarations: [
                TestAutoSizeComponent, 
                AutoSizeDirective
            ],
            providers: [{
                    provide: ApplicationService,
                    useFactory: (fakeStorage, fakePlatform) => new ApplicationServiceFake(ApplicationConfigFake, new ReduxFake(), fakeStorage, new Device, new StringUtilsService, fakePlatform),
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
                SiteConfigsService
            ]
        }).compileComponents();

        config = TestBed.get(SiteConfigsService);
        fixture = TestBed.createComponent(TestAutoSizeComponent); 
        inputEl = fixture.debugElement.query(By.css('textarea'));
    });

    it('the directive should correctly increase the text area height', () => {
        const text: string = "test \n test \n test \n test \n test \n test \n test";

        // fake the method
        spyOn(config, 'getConfig').and.returnValue(1000);

        inputEl.nativeElement.value = text;
        fixture.componentInstance.autoSize.adjust();
        fixture.detectChanges();

        expect(inputEl.nativeElement.value).toEqual(text);
        expect(parseInt(inputEl.nativeElement.style.height)).toBeGreaterThan(0);
    });

    it('height should not be more then "maxTextareaHeight" config value', () => {
        const text: string = "test \n test \n test \n test \n test \n test \n test";

        // fake the method
        spyOn(config, 'getConfig').and.returnValue(0);

        inputEl.nativeElement.value = text;
        fixture.componentInstance.autoSize.adjust();
        fixture.detectChanges();

        expect(inputEl.nativeElement.value).toEqual(text);
        expect(inputEl.nativeElement.style.height).toEqual('');
        expect(inputEl.nativeElement.style.overflow).toEqual('auto');
    });
});
