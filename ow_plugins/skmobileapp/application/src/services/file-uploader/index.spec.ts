import { TestBed } from '@angular/core/testing';
import { Observable } from 'rxjs/Rx';
import { MockBackend } from '@angular/http/testing';
import { Http, BaseRequestOptions } from '@angular/http';
import { Platform } from 'ionic-angular';

// services
import { FileUploaderService, IFileUploadOptions } from './';
import { SecureHttpService } from 'services/http';
import { ApplicationService } from 'services/application';
import { PersistentStorageService } from 'services/persistent-storage';
import { JwtService } from 'services/jwt';
import { AuthService } from 'services/auth';

// fakes
import { PlatformMock } from 'ionic-mocks';

import {
    createFakeFile,
    AuthServiceFake,
    JwtFake,
    ReduxFake, 
    ApplicationServiceFake, 
    ApplicationConfigFake,
    StringUtilsFake,
    DeviceFake,
    PersistentStorageMemoryAdapterFake } from 'test/fake';

describe('File uploader service', () => {
    // register service's fakes
    let fakeHttp: SecureHttpService;

    let fileUploader: FileUploaderService; // testable service 

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
        fakeHttp = TestBed.get(SecureHttpService);

        // init application service
        fileUploader = new FileUploaderService(fakeHttp);
    });

    it('isFileValid should return an error if file size is bigger then the max file size', () => {
        const file: Blob = createFakeFile('test.txt', 100);

        const options: IFileUploadOptions = {
            maxFileSize: 1 // max is one byte
        };

        expect(fileUploader.isFileValid(file, options)).toEqual(FileUploaderService.FILE_SIZE_ERROR_RESULT);
    });

    it('isFileValid should return a positive boolean value if file size is less or equal to the max file size', () => {
        const file: Blob = createFakeFile('test.txt', 100);

        const options: IFileUploadOptions = {
            maxFileSize: 100 // max is 100 bytes
        };

        expect(fileUploader.isFileValid(file, options)).toBeTruthy();
    });

    it('isFileValid should return an error if the file type is not listed in allowed mime types', () => {
        const file: Blob = createFakeFile('test.txt');

        const options: IFileUploadOptions = {
            allowedMimeTypes: ['image/jpg']
        };

        expect(fileUploader.isFileValid(file, options)).toEqual(FileUploaderService.MIME_TYPES_ERROR_RESULT);
    });

    it('isFileValid should return a positive boolean value if the file type is listed in allowed mime types', () => {
        const file: Blob = createFakeFile('test.txt');

        const options: IFileUploadOptions = {
            allowedMimeTypes: ['plain/txt']
        };

        expect(fileUploader.isFileValid(file, options)).toBeTruthy();
    });

    it('upload should send a correct post request', () => {
        const file: Blob = createFakeFile('test.txt');

        // fake http
        spyOn(fakeHttp, 'post').and.returnValue(
            Observable.of({})
        );

        const options: IFileUploadOptions = {};

        fileUploader.upload(file, options).subscribe(response => {
            expect(fakeHttp.post).toHaveBeenCalled();
            expect(response.type).toEqual(FileUploaderService.SUCCESS_RESULT);
        });
    });

    it('upload should return an error for failed post request', () => {
        const file: Blob = createFakeFile('test.txt');

        // fake http
        spyOn(fakeHttp, 'post').and.callFake(() => Observable.throw('error'));

        const options: IFileUploadOptions = {};

        fileUploader.upload(file, options).subscribe(response => {
            expect(fakeHttp.post).toHaveBeenCalled();
            expect(response.type).toEqual(FileUploaderService.UPLOAD_ERROR_RESULT);
        });
    });

    it('upload should return a correct value of uploading progress', () => {
        const progress: number = 100;
        const file: Blob = createFakeFile('test.txt');

        // fake http
        spyOn(fakeHttp, 'post').and.callFake(() => {
            fakeHttp.post.arguments[4](progress); // call progress callback

            return Observable.empty();
        });

        const options: IFileUploadOptions = {};

        fileUploader.upload(file, options).subscribe(response => {
            expect(fakeHttp.post).toHaveBeenCalled();
            expect(response.type).toEqual(FileUploaderService.UPLOAD_PROGRESS_RESULT);
            expect(response.data).toEqual(progress);
        });
    }); 
});
