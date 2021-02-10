import { TestBed } from '@angular/core/testing';
import { Observable } from 'rxjs/Rx';
import { MockBackend } from '@angular/http/testing';
import { Http, BaseRequestOptions } from '@angular/http';
import { NgRedux } from '@angular-redux/store';
import { normalize } from 'normalizr';
import { Platform } from 'ionic-angular';

// services
import { UserService, IUserWithAvatar, IUserWithFullData, IQuestionData, IUserData, ISearchFilter } from './';
import { SecureHttpService } from 'services/http';
import { ApplicationService } from 'services/application';
import { PersistentStorageService } from 'services/persistent-storage';
import { JwtService } from 'services/jwt';
import { AuthService } from 'services/auth';
import { SiteConfigsService } from 'services/site-configs';

// payloads
import {
    IEntitiesPayload,
    IByIdPayload
} from 'store/payloads';

// store
import {
    USERS_LOAD, 
    USERS_BEFORE_BLOCK,
    USERS_AFTER_BLOCK,
    USERS_ERROR_BLOCK,
    USERS_BEFORE_UNBLOCK,
    USERS_AFTER_UNBLOCK,
    USERS_ERROR_UNBLOCK 
} from 'store/actions'; 

import { IUser, IAvatarData,  } from 'store/states';

// schemas
import { userSchema } from 'services/user/schemas';

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
    PersistentStorageMemoryAdapterFake } from 'test/fake';

// responses
import {
    ILocationResponse,
    IJoinQuestionResponse,
    ISearchQuestionResponse,
    IResendVerifyEmailResponse,
    IVerifyEmailResponse,
    IForgotPasswordValidateResponse,
    IForgotPasswordResponse,
    IQuestionListResponse,
    ILoginResponse, 
    IGenderResponse, 
    IQuestionResponse,
    IQuestionStructureResponse,
    IUserResponse } from './responses';

describe('User service', () => {
    // register service's fakes
    let fakeHttp: SecureHttpService;
    let fakeAuth: AuthService;
    let fakeRedux: ReduxFake;
    let fakeSiteConfig: SiteConfigsServiceFake;

    let user: UserService; // testable service

    beforeEach(() => { 
        TestBed.configureTestingModule({
            providers: [{
                    provide: SiteConfigsService,
                    useFactory: (fakeHttp) => new SiteConfigsServiceFake(new ReduxFake(), fakeHttp),
                    deps: [SecureHttpService]
                }, {
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
                UserService
            ]}
        );

        // init service's fakes
        fakeSiteConfig = TestBed.get(SiteConfigsService);
        fakeRedux = TestBed.get(NgRedux);
        fakeHttp = TestBed.get(SecureHttpService);
        fakeAuth = TestBed.get(AuthService);

        // init application service
        user = TestBed.get(UserService);
    });

    it('blockUser should return correct result and dispatch both USERS_BEFORE_BLOCK and USERS_AFTER_BLOCK actions', () => {
        const userId: number = 1;
        const response: string = 'ok';

        const payload: IByIdPayload = {
            id: userId
        };

        // fake http
        spyOn(fakeHttp, 'post').and.returnValue(
            Observable.of(response)
        );

        // fake dispatch
        spyOn(fakeRedux, 'dispatch');

        user.blockUser(userId).subscribe(() => {
            expect(fakeRedux.dispatch).toHaveBeenCalled();
            expect(fakeRedux.dispatch).toHaveBeenCalledWith({
                type: USERS_AFTER_BLOCK,
                payload: payload
            });

            // http
            expect(fakeHttp.post).toHaveBeenCalledWith('/users/blocks/' + userId);
        });

        expect(fakeRedux.dispatch).toHaveBeenCalled();
        expect(fakeRedux.dispatch).toHaveBeenCalledWith({
            type: USERS_BEFORE_BLOCK,
            payload: payload
        });
    });

    it('blockUser should dispatch USERS_ERROR_BLOCK action if an error occurred', () => {
        const userId: number = 1;
        const errorResponse: string  = 'Some error';
 
        const payload: IByIdPayload = {
            id: userId
        };
 
        // fake http
        spyOn(fakeHttp, 'post').and.returnValue(Observable.throw(errorResponse));

        // fake dispatch
        spyOn(fakeRedux, 'dispatch');

        user.blockUser(userId).subscribe(() => {}, (error) => {
            expect(error).toEqual(errorResponse);
            expect(fakeRedux.dispatch).toHaveBeenCalled();
            expect(fakeRedux.dispatch).toHaveBeenCalledWith({
                type: USERS_ERROR_BLOCK,
                payload: payload
            });

            // http
            expect(fakeHttp.post).toHaveBeenCalledWith('/users/blocks/' + userId);
        });

        expect(fakeRedux.dispatch).toHaveBeenCalled();
        expect(fakeRedux.dispatch).toHaveBeenCalledWith({
            type: USERS_BEFORE_BLOCK,
            payload: payload
        });
    });

    it('unblockUser should return correct result and dispatch both USERS_BEFORE_UNBLOCK and USERS_AFTER_UNBLOCK actions', () => {
        const userId: number = 1;
        const response: string = 'ok';

        const payload: IByIdPayload = {
            id: userId
        };

        // fake http
        spyOn(fakeHttp, 'delete').and.returnValue(
            Observable.of(response)
        );

        // fake dispatch
        spyOn(fakeRedux, 'dispatch');

        user.unblockUser(userId).subscribe(() => {
            expect(fakeRedux.dispatch).toHaveBeenCalled();
            expect(fakeRedux.dispatch).toHaveBeenCalledWith({
                type: USERS_AFTER_UNBLOCK,
                payload: payload
            });

            // http
            expect(fakeHttp.delete).toHaveBeenCalledWith('/users/blocks/' + userId);
        });

        expect(fakeRedux.dispatch).toHaveBeenCalled();
        expect(fakeRedux.dispatch).toHaveBeenCalledWith({
            type: USERS_BEFORE_UNBLOCK,
            payload: payload
        });
    });

    it('unblockUser should dispatch USERS_ERROR_UNBLOCK action if an error occurred', () => {
        const userId: number = 1;
        const errorResponse: string  = 'Some error';
 
        const payload: IByIdPayload = {
            id: userId
        };

        // fake http
        spyOn(fakeHttp, 'delete').and.returnValue(Observable.throw(errorResponse));

        // fake dispatch
        spyOn(fakeRedux, 'dispatch');

        user.unblockUser(userId).subscribe(() => {}, (error) => {
            expect(error).toEqual(errorResponse);
            expect(fakeRedux.dispatch).toHaveBeenCalled();
            expect(fakeRedux.dispatch).toHaveBeenCalledWith({
                type: USERS_ERROR_UNBLOCK,
                payload: payload
            });

            // http
            expect(fakeHttp.delete).toHaveBeenCalledWith('/users/blocks/' + userId);
        });

        expect(fakeRedux.dispatch).toHaveBeenCalled();
        expect(fakeRedux.dispatch).toHaveBeenCalledWith({
            type: USERS_BEFORE_UNBLOCK,
            payload: payload
        });
    });

    it('login should return correct result', () => {
        const userName: string = 'test';
        const password: string = 'test';
        const response: ILoginResponse = {
            success: true
        };

        // fake http
        spyOn(fakeHttp, 'post').and.returnValue(
            Observable.of(response)
        );

        user.login(userName, password).subscribe(data => {
            expect(fakeHttp.post).toHaveBeenCalledWith('/login', {
                username: userName,
                password: password
            });

            expect(data).toEqual(response);
        });
    });

    it('loadGenders should return correct result', () => {
        const response: Array<IGenderResponse> = [{
            id: 'test',
            name: 'test'
        }];

        // fake http
        spyOn(fakeHttp, 'get').and.returnValue(
            Observable.of(response)
        );

        user.loadGenders().subscribe(data => {
            expect(fakeHttp.get).toHaveBeenCalledWith('/user-genders');
            expect(data).toEqual(response);
        });
    });

    it('loadJoinQuestions should return correct result', () => {
        const genderId: number = 1;
        const response: IJoinQuestionResponse = {
            id: 1,
            questions: []
        };

        // fake http
        spyOn(fakeHttp, 'get').and.returnValue(
            Observable.of(response)
        );

        user.loadJoinQuestions(genderId).subscribe(data => {
            expect(fakeHttp.get).toHaveBeenCalledWith('/join-questions/' + genderId);
            expect(data).toEqual(response);
        });
    });

    it('loadSearchQuestions should return correct result', () => {
        const response: ISearchQuestionResponse = {
            preferredAccountType: 1,
            questions: {
                1: []
            }
        };

        // fake http
        spyOn(fakeHttp, 'get').and.returnValue(
            Observable.of(response)
        );

        user.loadSearchQuestions().subscribe(data => {
            expect(fakeHttp.get).toHaveBeenCalledWith('/search-questions');
            expect(data).toEqual(response);
        });
    });

    it('loadEditQuestions should return correct result', () => {
        const response: Array<IQuestionListResponse> = [{
            order: 1,
            sectionId: 1,
            section: 'test',
            items: [
            ]
        }];

        // fake http
        spyOn(fakeHttp, 'get').and.returnValue(
            Observable.of(response)
        );

        user.loadEditQuestions().subscribe(data => {
            expect(fakeHttp.get).toHaveBeenCalledWith('/edit-questions');
            expect(data).toEqual(response);
        });
    });

    it('updateLocation should return correct result', () => {
        const latitude: number = 42.82;
        const longitude: number = 56.74;

        const response: ILocationResponse = {
            latitude: latitude,
            longitude: longitude
        };

        // fake http
        spyOn(fakeHttp, 'put').and.returnValue(
            Observable.of(response)
        );

        user.updateLocation(latitude, longitude).subscribe(data => {
            expect(fakeHttp.put).toHaveBeenCalledWith('/user-locations/me', {
                latitude: latitude,
                longitude: longitude
            });

            expect(data).toEqual(response);
        });
    });

    it('updateAccountType should return correct result', () => {
        const userId: number = 1;
        const accountType: string = 'test';
        const response: IUserResponse = {
            id: 1,
            userName: 'test'
        };

        // fake http
        spyOn(fakeHttp, 'put').and.returnValue(
            Observable.of(response)
        );

        // fake auth
        spyOn(fakeAuth, 'getUserId').and.returnValue(userId);

        user.updateAccountType(accountType).subscribe(data => {
            expect(fakeHttp.put).toHaveBeenCalledWith('/users/' + userId, {
                accountType: accountType
            }, {
                mode: 'completeAccountType'
            });

            expect(data).toEqual(response);
            expect(fakeAuth.getUserId).toHaveBeenCalled();
        });
    });

    it('updateQuestionsData should return correct result', () => {
        const questions: Array<IQuestionData> = [{
            name: 'test',
            value: 'test',
            type: 'test'
        }];

        const response: Array<IQuestionResponse> = [
            {
                id: 'test',
                name: 'test',
                value: 'test',
                type: 'test',
                params: {}
            }
        ];

        // fake http
        spyOn(fakeHttp, 'put').and.returnValue(
            Observable.of(response)
        );

        user.updateQuestionsData(questions).subscribe(data => {
            expect(fakeHttp.put).toHaveBeenCalledWith('/questions-data/me', questions, {
                mode: 'completeRequiredQuestions'
            });

            expect(data).toEqual(response);
        });
    });

    it('createQuestionsData should return correct result', () => {
        const questions: Array<IQuestionData> = [{
            name: 'test',
            value: 'test',
            type: 'test'
        }];

        const response: Array<IQuestionResponse> = [{
            id: 'test',
            name: 'test',
            value: 'test',
            type: 'test',
            params: {}
        }];

        // fake http
        spyOn(fakeHttp, 'post').and.returnValue(
            Observable.of(response)
        );

        user.createQuestionsData(questions).subscribe(data => {
            expect(fakeHttp.post).toHaveBeenCalledWith('/questions-data', questions);
            expect(data).toEqual(response);
        });
    });

    it('loadCompleteProfileQuestions should return correct result', () => {
        const response: Array<IQuestionListResponse> = [{
            order: 1,
            sectionId: 1,
            section: 'test',
            items: [
                {
                    type: 'test',
                    key: 'test',
                    label: 'test',
                    value: 'test'
                }
            ]
        }];

        // fake http
        spyOn(fakeHttp, 'get').and.returnValue(
            Observable.of(response)
        );

        user.loadCompleteProfileQuestions().subscribe(data => {
            expect(fakeHttp.get).toHaveBeenCalledWith('/complete-profile-questions');
            expect(data).toEqual(response);
        });
    });

    it('forgotPasswordValidateEmail should return correct result', () => {
        const email: string = 'test@gmail.com';
        const response: IForgotPasswordResponse = {
            success: true,
            message: ''
        };

        // fake http
        spyOn(fakeHttp, 'post').and.returnValue(
            Observable.of(response)
        );

        user.forgotPasswordValidateEmail(email).subscribe(data => {
            expect(fakeHttp.post).toHaveBeenCalledWith('/forgot-password', {
                email: email
            });

            expect(data).toEqual(response);
        });
    });

    it('forgotPasswordValidateCode should return correct result', () => {
        const code: string = 'test';
        const response: IForgotPasswordValidateResponse = {
            valid: true
        };

        // fake http
        spyOn(fakeHttp, 'post').and.returnValue(
            Observable.of(response)
        );

        user.forgotPasswordValidateCode(code).subscribe(data => {
            expect(fakeHttp.post).toHaveBeenCalledWith('/validators/forgot-password-code', {
                code: code
            });

            expect(data).toEqual(response);
        });
    });

    it('verifyEmail should return correct result', () => {
        const code: string = 'test';
        const response: IVerifyEmailResponse = {
            valid: true
        };

        // fake http
        spyOn(fakeHttp, 'post').and.returnValue(
            Observable.of(response)
        );

        user.verifyEmail(code).subscribe(data => {
            expect(fakeHttp.post).toHaveBeenCalledWith('/validators/verify-email-code', {
                code: code
            });

            expect(data).toEqual(response);
        });
    });

    it('forgotPasswordRestPassword should return correct result', () => {
        const code: string = 'test';
        const email: string = 'test@mail.ru';
        const password: string = 'password';

        const response: IForgotPasswordResponse = {
            success: true,
            message: ''
        };

        // fake http
        spyOn(fakeHttp, 'put').and.returnValue(
            Observable.of(response)
        );

        user.forgotPasswordRestPassword(code, email, password).subscribe(data => {
            expect(fakeHttp.put).toHaveBeenCalledWith('/forgot-password/' + code, {
                email: email,
                password: password
            });

            expect(data).toEqual(response);
        });
    });

    it('updateMe should return correct result', () => {
        const userId: number = 1;
        const response: IUserResponse = {
            id: userId
        };

        const userData: IUserData = {
            email: 'test@mail.ru'
        };

        // fake http
        spyOn(fakeHttp, 'put').and.returnValue(
            Observable.of(response)
        );

        // fake auth
        spyOn(fakeAuth, 'getUserId').and.returnValue(userId);

        user.updateMe(userData).subscribe(data => {
            expect(fakeHttp.put).toHaveBeenCalledWith('/users/' + userId, userData);
            expect(data).toEqual(response);
            expect(fakeAuth.getUserId).toHaveBeenCalled();
        });
    });

    it('watchMe should return a correct result', () => {
        const userId: number = 1;
        const avatarId: number = 1;

        const userData: IUser = {
            id: userId,
            avatar: avatarId
        };

        const avatarData: IAvatarData = {
            id: avatarId,
            userId: userId
        };

        const result: {user: IUser, avatar: IAvatarData} = {
            user: userData, 
            avatar: avatarData
        };

        // fake the method
        spyOn(user, 'watchMe').and.returnValue(
            Observable.of(result)
        );

        user.watchMe().subscribe(response => {
            expect(response).toEqual(result);
        });
    });

    it('watchUserWithAvatar should return a correct result', () => {
        const userId: number = 1;
        const avatarId: number = 1;

        const userData: IUser = {
            id: userId,
            avatar: avatarId
        };

        const avatarData: IAvatarData = {
            id: avatarId,
            userId: userId
        };

        const result: IUserWithAvatar = {
            user: userData, 
            avatar: avatarData
        };

        // fake the method
        spyOn(user, 'watchUserWithAvatar').and.returnValue(
            Observable.of(result)
        );

        user.watchUserWithAvatar(userId).subscribe(response => {
            expect(response).toEqual(result);
        });
    });

    it('watchUserWithFullData should return a correct result', () => {
        const userId: number = 1;

        const userData: IUser = {
            id: userId
        };

        const result: IUserWithFullData = {
            user: userData,
            avatar: undefined,
            bookmark: undefined,
            photos: [],
            matchAction: undefined
        };

        // fake the method
        spyOn(user, 'watchUserWithFullData').and.returnValue(
            Observable.of(result)
        );

        user.watchUserWithFullData(userId).subscribe(response => {
            expect(response).toEqual(result);
        });
    });

    it('loadMe should return correct result and dispatch USERS_LOAD action', () => {
        const userId: number = 1;
        const response: IUserResponse = {
            id: userId
        };

        // spy redux
        spyOn(fakeRedux, 'dispatch');

        // fake http
        spyOn(fakeHttp, 'get').and.returnValue(
            Observable.of(response)
        );

        // fake auth
        spyOn(fakeAuth, 'getUserId').and.returnValue(userId);

        user.loadMe().subscribe(data => {
            // http
            expect(fakeHttp.get).toHaveBeenCalledWith('/users/' + userId, {
                'with[]': [
                    'avatar', 
                    'permissions', 
                    'photos'
                ]
            });
            expect(data).toEqual(response);

            // auth 
            expect(fakeAuth.getUserId).toHaveBeenCalled();

            const payload: IEntitiesPayload = normalize(response, userSchema)

            //redux
            expect(fakeRedux.dispatch).toHaveBeenCalled();
            expect(fakeRedux.dispatch).toHaveBeenCalledWith({
                type: USERS_LOAD,
                payload: payload
            });
        });
    });

    it('searchUsers should return correct result', () => {
        const userId: number = 1;
        const response: IUserResponse = {
            id: userId
        };

        const filters: Array<ISearchFilter> = [{
            name: 'username',
            value: 'test',
            type: 'text'
        }];

        // fake http
        spyOn(fakeHttp, 'post').and.returnValue(
            Observable.of(response)
        );

        user.searchUsers(filters).subscribe(data => {
            // http
            expect(fakeHttp.post).toHaveBeenCalledWith('/users/searches', {
                filters: filters,
                'with[]': [
                    'avatar'
                ]
            });

            expect(data).toEqual(response);
        });
    });

    it('loadFullUserData should return correct result', () => {
        const userId: number = 1;
        const response: IUserResponse = {
            id: userId
        };

        // spy redux
        spyOn(fakeRedux, 'dispatch');

        // fake http
        spyOn(fakeHttp, 'get').and.returnValue(
            Observable.of(response)
        );

        user.loadFullUserData(userId).subscribe(data => {
            // http
            expect(fakeHttp.get).toHaveBeenCalledWith('/users/' + userId, {
                'with[]': [
                    'avatar',
                    'matchAction',
                    'viewQuestions'
                ]
            }, true);

            expect(data).toEqual(response);

            const payload: IEntitiesPayload = normalize(response, userSchema)

            expect(fakeRedux.dispatch).toHaveBeenCalled();
            expect(fakeRedux.dispatch).toHaveBeenCalledWith({
                type: USERS_LOAD,
                payload: payload
            });
        });
    });

    it('tinderSearchUsers should return correct result', () => {
        const excludeIds: Array<string | number> = [1, 2, 3];
        const userId: number = 1;
        const response: IUserResponse = {
            id: userId
        };

        // fake http
        spyOn(fakeHttp, 'get').and.returnValue(
            Observable.of(response)
        );

        // fake site config
        spyOn(fakeSiteConfig, 'isPluginActive').and.returnValue(true);

        user.tinderSearchUsers(excludeIds).subscribe(data => {
            // http
            expect(fakeHttp.get).toHaveBeenCalledWith('/tinder-users', {
                'with[]': [
                    'avatar', 
                    'matchActions'
                ],
                'excludeIds': excludeIds.join(',')
            });

            expect(data).toEqual(response);
        });
    });

    it('createMe should return correct result', () => {
        const userId: number = 1;
        const response: IUserResponse = {
            id: userId
        };

        const userData: IUserData = {
            email: 'test@mail.ru'
        };

        // fake http
        spyOn(fakeHttp, 'post').and.returnValue(
            Observable.of(response)
        );

        user.createMe(userData).subscribe(data => {
            expect(fakeHttp.post).toHaveBeenCalledWith('/users', userData);
            expect(data).toEqual(response);
        });
    });

    it('deleteMe should return correct result', () => {
        const userId: number = 1;
        const response: string = 'ok';

        // fake http
        spyOn(fakeHttp, 'delete').and.returnValue(
            Observable.of(response)
        );

        // fake auth
        spyOn(fakeAuth, 'getUserId').and.returnValue(userId);

        user.deleteMe().subscribe(data => {
            expect(fakeHttp.delete).toHaveBeenCalledWith('/users/' + userId);
            expect(data).toEqual(response);
        });
    });

    it('resendVerificationCode should return correct result', () => {
        const email: string = 'test@gmail.com';
        const response: IResendVerifyEmailResponse = {
            success: true,
            message: ''
        };

        // fake http
        spyOn(fakeHttp, 'post').and.returnValue(
            Observable.of(response)
        );

        user.resendVerificationCode(email).subscribe(data => {
            expect(fakeHttp.post).toHaveBeenCalledWith('/verify-email', {
                email: email
            });

            expect(data).toEqual(response);
        });
    });

    it('loadPreferencesQuestions should return correct result', () => {
        const section: string = 'test';
        const response: Array<IQuestionStructureResponse> = [{
            type: 'test'
        }];

        // fake http
        spyOn(fakeHttp, 'get').and.returnValue(
            Observable.of(response)
        );

        user.loadPreferencesQuestions(section).subscribe(data => {
            expect(fakeHttp.get).toHaveBeenCalledWith('/preferences/questions/' + section);
            expect(data).toEqual(response);
        });
    });

    it('updatePreferencesQuestions should return correct result', () => {
        const questions: Array<IQuestionData> = [{
            name: 'test',
            value: 'test'
        }];

        const response: string = 'ok';

        // fake http
        spyOn(fakeHttp, 'put').and.returnValue(
            Observable.of(response)
        );

        user.updatePreferencesQuestions(questions).subscribe(data => {
            expect(fakeHttp.put).toHaveBeenCalledWith('/preferences/me', questions);
            expect(data).toEqual(response);
        });
    });

    it('loadEmailNotificationsQuestions should return correct result', () => {
        const response: Array<IQuestionStructureResponse> = [{
            type: 'test'
        }];

        // fake http
        spyOn(fakeHttp, 'get').and.returnValue(
            Observable.of(response)
        );

        user.loadEmailNotificationsQuestions().subscribe(data => {
            expect(fakeHttp.get).toHaveBeenCalledWith('/email-notifications/questions');
            expect(data).toEqual(response);
        });
    });

    it('updateEmailNotificationsQuestions should return correct result', () => {
        const questions: Array<IQuestionData> = [{
            name: 'test',
            value: 'test'
        }];

        const response: string = 'ok';

        // fake http
        spyOn(fakeHttp, 'put').and.returnValue(
            Observable.of(response)
        );

        user.updateEmailNotificationsQuestions(questions).subscribe(data => {
            expect(fakeHttp.put).toHaveBeenCalledWith('/email-notifications/me', questions);
            expect(data).toEqual(response);
        });
    });
});
