import { IMapType } from 'store/types';

export interface IVideoImNotificationData {
    id: number;
    type?: string;
    user?: number;
    notification?: IMapType<any>;
    sessionId?: string;
    _isMarked?: boolean;
}

export interface IVideoImActiveInterlocutorData {
    userId: number;
    isMeInitiator: boolean;
}

export interface IVideoImCallData {
    userId: number;
    sessionId: string;
}

export interface IVideoImNotifications {
    byId?: IMapType<IVideoImNotificationData>;
    allIds?: Array<number>;
    activeSessionIds?: IMapType<string>
    activeInterlocutorData?: IVideoImActiveInterlocutorData;
}