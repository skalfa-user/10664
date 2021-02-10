
import { IMapType } from 'store/types';

export interface IAvatarData {
    id: number | string;
    active?: boolean;
    bigUrl?: string;
    pendingBigUrl?: string;
    pendingUrl?: string;
    url?: string;
    userId?: number;    
    _isHidden?: boolean;
    _isPending?: boolean;
}

export interface IAvatars {
    active?: IMapType<IAvatarData>;
    uploading?: Array<IAvatarData>;
}
