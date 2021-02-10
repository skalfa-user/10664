import { IMapType } from 'store/types';

export interface IGuestData {
    id: number;
    viewed?: boolean;
    visitTimestamp?: number;
    user?: number;
    _isHidden?: boolean;
    _isRead?: boolean;
    _isNotified?: boolean;
}

export interface IGuests {
    isFetched?: boolean;
    byId?: IMapType<IGuestData>;
    allIds?: Array<number>;
}
