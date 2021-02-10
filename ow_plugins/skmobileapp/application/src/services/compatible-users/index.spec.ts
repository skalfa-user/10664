import { Observable } from 'rxjs/Rx';
import { NgRedux } from '@angular-redux/store';
import { normalize } from 'normalizr';

// services
import { CompatibleUsersService } from './'

// payloads
import {
    IEntitiesPayload
} from 'store/payloads';

// store
import { IAppState } from 'store';

import { 
    IUser, 
    IAvatarData, 
    IMatchAction, 
    ICompatibleUserData 
} from 'store/states';

import { ICompatibleUserListItem } from 'store/reducers';

import { COMPATIBLE_USERS_SET } from 'store/actions'; 

// schemas
import { userListSchema } from './schemas';

// fakes
import {
    ReduxFake
} from 'test/fake';

// responses
import { ICompatibleUserResponse } from './responses';

describe('Compatible users service', () => {
    // register service's fakes
    let fakeRedux: ReduxFake;

    let compatibleUsers: CompatibleUsersService; // testable service

    beforeEach(() => {
        // init service's fakes
        fakeRedux = new ReduxFake();

        // init service
        compatibleUsers = new CompatibleUsersService(fakeRedux as NgRedux<IAppState>);
    });

    it('setCompatibleUsers should dispatch COMPATIBLE_USERS_SET action', () => {
        const userId: number = 1;
        const response: Array<ICompatibleUserResponse> = [{
            id: userId
        }];

        const payload: IEntitiesPayload = normalize(response, userListSchema);

        const expectedArgs = {
            type: COMPATIBLE_USERS_SET,
            payload: payload
        };

        spyOn(fakeRedux, 'dispatch');
        compatibleUsers.setCompatibleUsers(response);

        expect(fakeRedux.dispatch).toHaveBeenCalled();
        expect(fakeRedux.dispatch).toHaveBeenCalledWith(expectedArgs);
    });

    it('watchIsCompatibleUsersFetched should return a correct boolean value', () => {
        const isFetched: boolean = true;

        // fake the method
        spyOn(compatibleUsers, 'watchIsCompatibleUsersFetched').and.returnValue(
            Observable.of(isFetched)
        );

        compatibleUsers.watchIsCompatibleUsersFetched().subscribe(isDataFetched => {
            expect(isDataFetched).toEqual(isFetched); 
        });
    });

    it('watchCompatibleUserList should return a correct result', () => {
        const compatibleUserId: number = 1;
        const userId: number = 1;
        const avatarId: number = 1;
        const matchActionId: number = 1;

        const compatibleUserData: ICompatibleUserData = {
            id: compatibleUserId,
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

        const result: ICompatibleUserListItem = {
            compatibleUser: compatibleUserData,
            user: userData, 
            avatar: avatarData,
            matchAction: matchActionData
        };

        // fake the method
        spyOn(compatibleUsers, 'watchCompatibleUserList').and.returnValue(
            Observable.of(result)
        );

        compatibleUsers.watchCompatibleUserList().subscribe(response => {
            expect(response).toEqual(result);
        });
    });

    it('watchCompatibleUserList should return an undefined value if guest list empty', () => {
        // fake the method
        spyOn(compatibleUsers, 'watchCompatibleUserList').and.returnValue(
            Observable.of(undefined)
        );

        compatibleUsers.watchCompatibleUserList().subscribe(response => {
            expect(response).toBeUndefined();
        });
    });
});
