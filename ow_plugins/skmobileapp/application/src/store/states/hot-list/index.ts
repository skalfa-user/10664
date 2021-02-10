import { IMapType } from 'store/types';

export interface IHotListData {
    id: number|string;
    user?: number;
    _isHidden?: boolean;
    _isJoinPending?: boolean;
}

export interface IHotList {
    isFetched?: boolean;
    byId?: IMapType<IHotListData>;
    allIds?: Array<number|string>;
}
