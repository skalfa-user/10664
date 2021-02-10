import { normalize } from 'normalizr';

// schema
import { matchSchema } from './';

// responses
import { IMatchResponse } from 'services/user/responses';

describe('Match actions schema', () => {
    it('normalizr should correct parse a response', () => {
        const matchId: number = 1;
        const userId: number = 1;

        const response: IMatchResponse = {
            id: matchId,
            type: 'like',
            isMutual: false,
            userId: userId,
            createStamp: 1,
            isRead: false,
            isNew: false,
            user: {
                id: userId
            }
        };

        // normalize data
        expect(normalize(response, matchSchema)).toEqual({
            entities: {
                user: {
                    [userId]: {
                        id: userId,
                        matchAction: matchId
                    }
                },
                matchAction: {
                    [matchId]: {
                        ...response,
                        user: userId
                    }
                }
            },
            result: matchId
        });
    });
});
