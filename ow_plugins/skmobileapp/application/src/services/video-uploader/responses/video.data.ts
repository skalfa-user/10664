import {IVideoPreview} from "services/video-uploader/responses";
import {IVideoUserDataResponse} from "services/video-uploader/responses";
export * from './video.preview';
export * from './video.user.data';

export interface IVideoDataResponse {
    params?: {
        uploadUrl?: string;
        maxSizeBytes?: number;
        duration?: number;
    };
    privacyVal?: Array<any>;
    videoForPreview?: IVideoPreview;
    userIdList: Array<IVideoUserDataResponse>;
}