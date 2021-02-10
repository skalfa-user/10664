import { Injectable } from '@angular/core';
import { Observable } from 'rxjs/Observable';
import { NgRedux } from '@angular-redux/store';
import { normalize } from 'normalizr';
import { Subject } from 'rxjs/Subject';
import { ISubscription } from 'rxjs/Subscription';

// services
import { SecureHttpService } from 'services/http';
import { StringUtilsService } from 'services/string-utils';

// payloads
import { 
    IMatchActionDataPayload,
    IWrappedEntitiesPayload
} from 'store/payloads';

// responses
import { IMatchResponse } from 'services/user/responses';

// store
import { IAppState } from 'store';
import { getMatch } from 'store/reducers';
import { IMatchAction } from 'store/states';
import { IMapType } from 'store/types';

import {
    MATCH_ACTIONS_BEFORE_DELETE,
    MATCH_ACTIONS_AFTER_DELETE,
    MATCH_ACTIONS_ERROR_DELETE,
    MATCH_ACTIONS_BEFORE_ADD, 
    MATCH_ACTIONS_AFTER_ADD,
    MATCH_ACTIONS_ERROR_ADD
} from 'store/actions';

// schemas
import { matchSchema } from './schemas';

export type MatchType = 'like' | 'dislike';

@Injectable()
export class MatchActionsService {
    public matchCreated$: Subject<number | string> = new Subject();

    private deleteSubscribes: IMapType<ISubscription> = {};
    private createSubscribes: IMapType<ISubscription> = {};

    /**
     * Constructor
     */
    constructor(
        private stringUtils: StringUtilsService,
        private http: SecureHttpService, 
        private ngRedux: NgRedux<IAppState>) {}

    /**
     * Delete match
     */
    deleteMatch(id: string | number, userId: number | string): Observable<any> {
        const payload: IMatchActionDataPayload = {
            id: id,
            userId: userId
        };

        this.ngRedux.dispatch({
            type: MATCH_ACTIONS_BEFORE_DELETE,
            payload: payload
        });

        const match = this.http.delete('/math-actions/user/' + userId);

        this.deleteSubscribes[userId] = match.subscribe(() => {
            this.ngRedux.dispatch({
                type: MATCH_ACTIONS_AFTER_DELETE,
                payload: payload
            });

            delete this.deleteSubscribes[userId];
        }, () => {
            this.ngRedux.dispatch({
                type: MATCH_ACTIONS_ERROR_DELETE,
                payload: payload
            });

            delete this.deleteSubscribes[userId];
        });

        return match;
    }

    /**
     * Create match
     */
    createMatch(userId: number | string, type: MatchType): Observable<IMatchResponse> {
        const randomId: string = this.stringUtils.getRandomString();

        this.matchCreated$.next(userId);

        const payload: IMatchActionDataPayload = {
            id: randomId,
            type: type,
            userId: userId
        };

        this.ngRedux.dispatch({
            type: MATCH_ACTIONS_BEFORE_ADD,
            payload: payload
        });

        const match = this.http.post('/math-actions/user', {
            userId: userId,
            type: type
        });

        this.createSubscribes[userId] = match.subscribe((response: IMatchResponse) => {
            const afterAddPayload: IWrappedEntitiesPayload = {
                id: randomId,
                data: normalize(response, matchSchema)
            };

            this.ngRedux.dispatch({
                type: MATCH_ACTIONS_AFTER_ADD,
                payload: afterAddPayload
            });

            delete this.createSubscribes[userId];
        }, () => {
            this.ngRedux.dispatch({
                type: MATCH_ACTIONS_ERROR_ADD,
                payload: payload
            });

            delete this.createSubscribes[userId];
        });

        return match;
    }

    /**
     * Stop all user subscriptions
     */
    stopAllUserSubscriptions(userId: number | string): void {
        if (this.createSubscribes[userId]) {
            this.createSubscribes[userId].unsubscribe();
        }

        if (this.deleteSubscribes[userId]) {
            this.deleteSubscribes[userId].unsubscribe();
        }
    }

    /**
     * Get match
     */
    getMatch(userId: number): IMatchAction | undefined {
        return getMatch(this.ngRedux.getState(), userId);
    }
}
