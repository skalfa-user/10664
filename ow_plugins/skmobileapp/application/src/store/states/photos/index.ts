import { IMapType } from 'store/types';

export interface IPhotoData {
    id: number | string;
    url?: string;
    bigUrl?: string;
    approved?: boolean;
    userId?: number;
    _isHidden?: boolean;
    _isPending?: boolean;
}

export interface IPhotos {
    active?: IMapType<IPhotoData>;
    uploading?: Array<IPhotoData>;
}
