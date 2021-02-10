import {  IUserResponse, IAvatarResponse } from 'services/user/responses';

export interface INotificationResponse {
    id: number;
    type?: string;
    notification?: {[K: string]: any};
    userId?: number;
    sessionId?: string;
    user?: IUserResponse;
    avatar?: IAvatarResponse;
}
