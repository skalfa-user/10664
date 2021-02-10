import { Injectable } from '@angular/core';
import { Observable } from 'rxjs/Observable';
import { normalize } from 'normalizr';
import { NgRedux } from '@angular-redux/store';
import isEqual from 'lodash/isEqual';

// services
import { SecureHttpService } from 'services/http';
import { AuthService } from 'services/auth';

// responses
import {
    ILocationResponse,
    IJoinQuestionResponse,
    ISearchQuestionResponse,
    IResendVerifyEmailResponse,
    IVerifyEmailResponse,
    IForgotPasswordValidateResponse,
    IQuestionResponse,
    ILoginResponse, 
    IGenderResponse, 
    IUserResponse, 
    IForgotPasswordResponse,
    IQuestionStructureResponse,
    IQuestionListResponse } from './responses';

// schemas
import { userSchema } from './schemas';

// payloads
import {
    IEntitiesPayload,
    IByIdPayload
} from 'store/payloads';

// store
import { IAppState } from 'store';
import { IUser } from 'store/states';

import {
    isUserLoaded,
    getUserWithAvatar,
    getUserWithFullData,
    IUserWithAvatar,
    IUserWithFullData,
    getUser,
    isUserBlocked
} from 'store/reducers';

import {
    USERS_LOAD, 
    USERS_BEFORE_BLOCK,
    USERS_AFTER_BLOCK,
    USERS_ERROR_BLOCK,
    USERS_BEFORE_UNBLOCK,
    USERS_AFTER_UNBLOCK,
    USERS_ERROR_UNBLOCK
} from 'store/actions';

export { 
    IUserResponse, 
    IQuestionResponse, 
    IQuestionStructureResponse, 
    IQuestionListResponse
} from './responses';

export { 
    IUserWithAvatar,
    IUserWithFullData 
} from 'store/reducers';

export interface IQuestionData {
    name: string;
    value: any,
    type?: string
}

export interface IUserData {
    avatarKey?: string;
    email?: string;
    lookingFor?: Array<string|number>;
    password?: string,
    repeatPassword?: string,
    sex?: number,
    userName?: string
}

export interface ISearchFilter {
    name: string;
    value: any;
    type: string;
}

@Injectable()
export class UserService {
    /**
     * Constructor
     */
    constructor (
        private ngRedux: NgRedux<IAppState>,
        private http: SecureHttpService, 
        private auth: AuthService) {}

    /**
     * Is user loaded
     */
    isUserLoaded(user: IUser | undefined): boolean {
        return isUserLoaded(user);
    }

    /**
     * Is user blocked
     */
    isUserBlocked(user: IUser): boolean {
        return isUserBlocked(user);
    }

    /**
     * Login
     */
    login(login: string, password: string): Observable<ILoginResponse> {
        return this.http.post('/login', {
            username: login,
            password: password
        });
    }

    /**
     * Load genders
     */
    loadGenders(): Observable<Array<IGenderResponse>> {
        return this.http.get('/user-genders');
    }

    /**
     * Load join questions
     */
    loadJoinQuestions(gender: number): Observable<IJoinQuestionResponse> {
        return this.http.get('/join-questions/' + gender);
    }

    /**
     * Load search questions
     */
    loadSearchQuestions(): Observable<ISearchQuestionResponse> {
        return this.http.get('/search-questions');
    }

    /**
     * Load edit questions
     */
    loadEditQuestions(): Observable<Array<IQuestionListResponse>> {
        return this.http.get('/edit-questions');
    }

    /**
     * Load complete profile questions
     */
    loadCompleteProfileQuestions(): Observable<Array<IQuestionListResponse>> {
        return this.http.get('/complete-profile-questions');
    }

    /**
     * Update account type
     */
    updateAccountType(accountType: string): Observable<IUserResponse> {
        return this.http.put('/users/' + this.auth.getUserId(), {accountType: accountType}, {
            mode: 'completeAccountType'
        });
    }

    /**
     * Update location
     */
    updateLocation(latitude: number|string, longitude: number|string): Observable<ILocationResponse> {
        return this.http.put('/user-locations/me', {
            latitude: latitude,
            longitude: longitude
        });
    }

    /**
     * Update questions data
     */
    updateQuestionsData(questions: Array<IQuestionData>, 
            isCompleteMode: boolean = true): Observable<Array<IQuestionResponse>> {

        const params = isCompleteMode
            ? {mode: 'completeRequiredQuestions'}
            : {};

        return this.http.put('/questions-data/me', questions, params);
    }

    /**
     * Create questions data
     */
    createQuestionsData(questions: Array<IQuestionData>): Observable<Array<IQuestionResponse>> {
        return this.http.post('/questions-data', questions);
    }

    /**
     * Verify email
     */
    verifyEmail(code: string): Observable<IVerifyEmailResponse> { 
        return this.http.post('/validators/verify-email-code', {
            code: code
        });
    }
 
    /**
     * Resend verification code
     */
    resendVerificationCode(email: string): Observable<IResendVerifyEmailResponse> {
        return this.http.post('/verify-email', {
            email: email
        });
    }

    /**
     * Forgot password validate code
     */
    forgotPasswordValidateCode(code: string): Observable<IForgotPasswordValidateResponse> {
        return this.http.post('/validators/forgot-password-code', {
            code: code
        });
    }

    /**
     * Forgot password validate email
     */
    forgotPasswordValidateEmail(email: string): Observable<IForgotPasswordResponse> {
        return this.http.post('/forgot-password', {
            email: email
        });
    }

    /**
     * Forgot password reset password
     */
    forgotPasswordRestPassword(code: string, email: string, password: string): Observable<IForgotPasswordResponse> {
        return this.http.put('/forgot-password/' + code, {
            email: email,
            password: password
        });
    }

    /**
     * Watch me
     */
    watchMe(): Observable<IUserWithAvatar> {
        return this.ngRedux.select((appState: IAppState) => getUserWithAvatar(appState, this.auth.getUserId()), isEqual);
    }

    /**
     * Get me
     */
    getMe(): IUserWithAvatar | undefined {
        return getUserWithAvatar(this.ngRedux.getState(), this.auth.getUserId());
    }

    /**
     * Get user
     */
    getUser(userId: number): IUser | undefined {
        return getUser(this.ngRedux.getState(), userId);
    }

    /**
     * Watch user with full data
     */
    watchUserWithFullData(userId: number): Observable<IUserWithFullData> {
        return this.ngRedux.select((appState: IAppState) => getUserWithFullData(userId)(appState), isEqual);
    }

    /**
     * Get user with full data
     */
    getUserWithFullData(userId: number): IUserWithFullData | undefined {
        return getUserWithFullData(userId)(this.ngRedux.getState());
    }

    /**
     * Watch user with avatar
     */
    watchUserWithAvatar(userId: number): Observable<IUserWithAvatar> {
        return this.ngRedux.select((appState: IAppState) => getUserWithAvatar(appState, userId), isEqual);
    }

    /**
     * Get user with avatar
     */
    getUserWithAvatar(userId: number): IUserWithAvatar | undefined {
        return getUserWithAvatar(this.ngRedux.getState(), userId);
    }
 
    /**
     * Load me
     */
    loadMe(): Observable<IUserResponse> {
        const user = this.http.get('/users/' + this.auth.getUserId(), {
            'with[]': [
                'avatar', 
                'permissions', 
                'photos'
            ]
        });

        // normalize user response
        user.subscribe(response => {
            const payload: IEntitiesPayload = normalize(response, userSchema);

            this.ngRedux.dispatch({
                type: USERS_LOAD,
                payload: payload
            });
        }, () => {});

        return user;
    }

    /**
     * Load full user data
     */
    loadFullUserData(userId: number, extraRelations: Array<string> = [], broadCastError: boolean = true): Observable<IUserResponse> {
        const relations: string[] = [
            'avatar',
            'matchAction',
            'viewQuestions'
        ];

        const user = this.http.get('/users/' + userId, {
            'with[]': [
                ...relations,
                ...extraRelations
            ]
        }, broadCastError);

        // normalize user response
        user.subscribe(response => {
            const payload: IEntitiesPayload = normalize(response, userSchema);

            this.ngRedux.dispatch({
                type: USERS_LOAD,
                payload: payload
            });
        }, () => {});

        return user;
    }

    /**
     * Search users
     */
    searchUsers(filters: Array<ISearchFilter>): Observable<Array<IUserResponse>> {
        const search = this.http.post('/users/searches', {
            filters: filters,
            'with[]': [
                'avatar'
            ]
        });

        return search;
    }

    /**
     * Tinder search users
     */
    tinderSearchUsers(excludeIds: Array<string | number>): Observable<Array<IUserResponse>> {
        const relations = [
            'avatar',
            'matchActions'
        ];

        const search = this.http.get('/tinder-users', {
            'with[]': relations,
            'excludeIds': excludeIds.join(',')
        });

        return search;
    }

    /**
     * Update me
     */
    updateMe(data: IUserData): Observable<IUserResponse> {
        return this.http.put('/users/' + this.auth.getUserId(), data);
    }

    /**
     * Create me
     */
    createMe(data: IUserData): Observable<IUserResponse> {
        return this.http.post('/users', data);
    }

    /**
     * Delete me
     */
    deleteMe(): Observable<any> {
        return this.http.delete('/users/' + this.auth.getUserId());
    }

    /**
     * Block user
     */
    blockUser(userId: number): Observable<any> {
        const payload: IByIdPayload = {
            id: userId
        };

        this.ngRedux.dispatch({
            type: USERS_BEFORE_BLOCK,
            payload: payload
        });

        const blockUser: Observable<any> = this.http.post('/users/blocks/' + userId);

        blockUser.subscribe(() => {
            this.ngRedux.dispatch({
                type: USERS_AFTER_BLOCK,
                payload: payload
            });
        }, () => {
            this.ngRedux.dispatch({
                type: USERS_ERROR_BLOCK,
                payload: payload
            });
        });

        return blockUser;
    }

    /**
     * Unblock user
     */
    unblockUser(userId: number): Observable<any> {
        const payload: IByIdPayload = {
            id: userId
        };

        this.ngRedux.dispatch({
            type: USERS_BEFORE_UNBLOCK,
            payload: payload
        });

        const unblockUser: Observable<any> = this.http.delete('/users/blocks/' + userId);

        unblockUser.subscribe(() => {
            this.ngRedux.dispatch({
                type: USERS_AFTER_UNBLOCK,
                payload: payload
            });
        }, () => {
            this.ngRedux.dispatch({
                type: USERS_ERROR_UNBLOCK,
                payload: payload
            });
        });

        return unblockUser;
    }

    /**
     * Load preferences questions
     */
    loadPreferencesQuestions(section: string): Observable<Array<IQuestionStructureResponse>> {
        return this.http.get('/preferences/questions/' + section);
    }

    /**
     * Update preferences questions
     */
    updatePreferencesQuestions(questions: Array<IQuestionData>): Observable<any> {
        return this.http.put('/preferences/me', questions);
    }

    /**
     * Load email notifications questions
     */
    loadEmailNotificationsQuestions(): Observable<Array<IQuestionStructureResponse>> {
        return this.http.get('/email-notifications/questions');
    }

    /**
     * Update email notifications questions
     */
    updateEmailNotificationsQuestions(questions: Array<IQuestionData>): Observable<any> {
        return this.http.put('/email-notifications/me', questions);
    }
}
