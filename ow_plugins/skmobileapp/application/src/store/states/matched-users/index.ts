import { IMapType } from 'store/types';

export interface IMatchedUserData {
    id: number;
    isViewed?: boolean;
    isNew?: boolean;
    user?: number;
    _isNotified?: boolean;
    _isRead?: boolean;
}

export interface IMatchedUsers {
    isFetched?: boolean;
    byId?: IMapType<IMatchedUserData>;
    allIds?: Array<number>;
}
