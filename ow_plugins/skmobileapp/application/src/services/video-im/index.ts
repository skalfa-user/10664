import { Injectable } from '@angular/core';
import { IAppState } from "store/index";
import { NgRedux } from "@angular-redux/store";
import { Observable } from 'rxjs/Observable';
import { normalize } from 'normalizr';
import isEqual from 'lodash/isEqual';

// payloads
import { 
    IByIdPayload, 
    IEntitiesPayload,
    IVideoImCallDataPayload,
    IVideoImActiveInterlocutorDataPayload
} from 'store/payloads';

// states
import {
    IVideoImCallData,
    IVideoImNotificationData,
    IVideoImActiveInterlocutorData
} from 'store/states';

// services
import { SecureHttpService } from 'services/http';

// responses
import { INotificationResponse } from './responses';

// schemas
import { notificationListSchema } from './schemas';

// store
import {
    VIDEO_IM_ADD_NOTIFICATION,
    VIDEO_IM_BEFORE_MARK_NOTIFICATION,
    VIDEO_IM_AFTER_MARK_NOTIFICATION,
    VIDEO_IM_ERROR_MARK_NOTIFICATION,
    VIDEO_IM_SET_ACTIVE_INTERLOCUTOR_DATA,
    VIDEO_IM_SET_INTERLOCUTOR_SESSION_ID,
    VIDEO_IM_REMOVE_INTERLOCUTOR_SESSION_ID
} from 'store/actions';

import {
    getSessionId,
    getFirstCallData,
    getActiveInterlocutorData,
    getActiveInterlocutorNotifications
} from 'store/reducers';

import { IMapType } from 'store/types';

@Injectable()
export class VideoImService {
    /**
     * Constructor
     */
    constructor (
        private ngRedux: NgRedux<IAppState>,
        private http: SecureHttpService) {}

    /**
     * Add notifications
     */
    addNotifications(notifications: Array<INotificationResponse>): void {
        const payload: IEntitiesPayload = normalize(notifications, notificationListSchema);

        this.ngRedux.dispatch({
            type: VIDEO_IM_ADD_NOTIFICATION,
            payload: payload
        });
    }

    /**
     * Marks notifications
     */
    markNotifications(interlocutorId: number, sessionId: string = null): Observable<any> {
        const payload: IByIdPayload = {
            id: interlocutorId
        };

        this.ngRedux.dispatch({
            type: VIDEO_IM_BEFORE_MARK_NOTIFICATION,
            payload: payload
        });

        if (sessionId == null) {
            sessionId = this.getSessionId(interlocutorId);
        }

        const markedNotifications: Observable<any> = this.http.put('/video-im/notifications/me', {
            sessionId: sessionId,
            userId: interlocutorId
        });

        markedNotifications.subscribe(() => {
            this.ngRedux.dispatch({
                type: VIDEO_IM_AFTER_MARK_NOTIFICATION,
                payload: payload
            });
        }, () => {
            this.ngRedux.dispatch({
                type: VIDEO_IM_ERROR_MARK_NOTIFICATION,
                payload: payload
            });
        });

        return markedNotifications;
    }

    /**
     * Sends notification
     */
    sendNotification(interlocutorId: number, notification: IMapType<any>): Observable<any> {
        const sessionId: string = this.getSessionId(interlocutorId);

        const sendNotification: Observable<any> = this.http.post('/video-im/notifications', {
            sessionId: sessionId,
            interlocutorId: interlocutorId,
            notification: notification
        });

        return sendNotification;
    }

    /**
     * Set active interlocutor data
     */
    setActiveInterlocutorData(interlocutorId: number, isMeInitiator: boolean): void {
        const payload: IVideoImActiveInterlocutorDataPayload = {
            userId: interlocutorId,
            isMeInitiator: isMeInitiator
        };

        this.ngRedux.dispatch({
            type: VIDEO_IM_SET_ACTIVE_INTERLOCUTOR_DATA,
            payload: payload
        });
    }

    /**
     * Remove active interlocutor data
     */
    removeActiveInterlocutorData(interlocutorId: number): void {
        const activeInterlocutorData: IVideoImActiveInterlocutorData = this.getActiveInterlocutorData();

        if (activeInterlocutorData.userId == interlocutorId) {
            const payload: IVideoImActiveInterlocutorDataPayload = {
                userId: 0,
                isMeInitiator: false
            };

            this.ngRedux.dispatch({
                type: VIDEO_IM_SET_ACTIVE_INTERLOCUTOR_DATA,
                payload: payload
            });
        }
    }

    /**
     * Get active interlocutor data
     */
    getActiveInterlocutorData(): IVideoImActiveInterlocutorData {
        return getActiveInterlocutorData(this.ngRedux.getState());
    }

    /**
     * Set session id
     */
    setSessionId(userId: number, sessionId: string): void {
        const payload: IVideoImCallDataPayload = {
            userId: userId,
            sessionId: sessionId
        };

        this.ngRedux.dispatch({
            type: VIDEO_IM_SET_INTERLOCUTOR_SESSION_ID,
            payload: payload
        });
    }

    /**
     * Get session id
     */
    getSessionId(interlocutorId): string | undefined {
        return getSessionId(this.ngRedux.getState(), interlocutorId);
    }

    /**
     * Remove session id
     */
    removeSessionId(interlocutorId: number): void {
        const payload: IByIdPayload = {
            id: interlocutorId
        };

        this.ngRedux.dispatch({
            type: VIDEO_IM_REMOVE_INTERLOCUTOR_SESSION_ID,
            payload: payload
        });
    }

    /**
     * Watch first call data
     */
    watchFirstCallingUserId(): Observable<IVideoImCallData> {
        return this.ngRedux.select((appState: IAppState) => getFirstCallData()(appState));
    }

    /**
     * Watch active interlocutor data
     */
    watchActiveInterlocutorData(): Observable<IVideoImActiveInterlocutorData> {
        return this.ngRedux.select((appState: IAppState) => getActiveInterlocutorData(appState));
    }

    /**
     * Watch active interlocutor notifications
     */
    watchActiveInterlocutorNotifications(): Observable<Array<IVideoImNotificationData> | undefined> {
        return this.ngRedux.select((appState: IAppState) => getActiveInterlocutorNotifications()(appState), isEqual);
    }
}
