import { IPhotoDataPayload } from 'store/payloads';

export interface IPhotosAfterUploadPayload {
    id: string | number;
    userId: number;
    photo: IPhotoDataPayload
}
