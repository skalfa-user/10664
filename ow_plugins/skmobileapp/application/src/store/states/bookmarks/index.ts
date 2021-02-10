import { IMapType } from 'store/types';

export interface IBookmarkData {
    id: number | string;
    user?: number;
    _isHidden?: boolean;
}

export interface IBookmarks {
    isFetched?: boolean;
    byId?: IMapType<IBookmarkData>;
    allIds?: Array<number | string>;
}
