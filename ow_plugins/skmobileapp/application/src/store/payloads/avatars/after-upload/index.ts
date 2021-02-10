import { IAvatarDataPayload } from 'store/payloads';

export interface IAvatarAfterUploadPayload {
    id: string | number;
    userId: number;
    avatar: IAvatarDataPayload
}
