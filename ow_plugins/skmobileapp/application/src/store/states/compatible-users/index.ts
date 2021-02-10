import { IMapType } from 'store/types';

export interface ICompatibleUserData {
    id: number;
    user?: number;
}

export interface ICompatibleUsers {
    isFetched?: boolean;
    byId?: IMapType<ICompatibleUserData>;
    allIds?: Array<number>;
}
