import { normalize } from 'normalizr';

// schema
import { permissionListSchema } from './';

// responses
import { IPermissionResponse } from 'services/user/responses';

describe('User permissions schema', () => {
    it('normalizr should correct parse a response', () => {
        const userId1: number = 1;
        const permissionId1: string = '1_test';

        const userId2: number = 2;
        const permissionId2: string = '2_test';

        const permission1: IPermissionResponse = {
            id: permissionId1,
            permission: 'test',
            isPromoted: false,
            isAllowedAfterTracking: true,
            isAllowed: true,
            creditsCost: 10,
            authorizedByCredits: false,
            user: {
                id: userId1 
            }
        };

        const permission2: IPermissionResponse = {
            id: permissionId2,
            permission: 'test',
            isPromoted: false,
            isAllowedAfterTracking: true,
            isAllowed: true,
            creditsCost: 10,
            authorizedByCredits: false,
            user: {
                id: userId2
            }
        };

        const response: Array<IPermissionResponse> = [
            permission1,
            permission2
        ];

        // normalize data
        expect(normalize(response, permissionListSchema)).toEqual({
            entities: {
                users: {
                    [userId1]: {
                        id: userId1,
                        permissions: [permissionId1]
                    },
                    [userId2]: {
                        id: userId2,
                        permissions: [permissionId2]
                    }
                },
                permissions: {
                    [permissionId1]: {
                        ...permission1,
                        user: userId1
                    },
                    [permissionId2]: {
                        ...permission2,
                        user: userId2
                    }
                }
            },
            result: [permissionId1, permissionId2]
        });
    });
});
