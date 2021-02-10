import {  IUserResponse, IAvatarResponse } from 'services/user/responses';

export interface IConversationResponse {
    id: string;
    isNew?: boolean;
    isReply?: boolean;
    isOpponentRead?: boolean;
    lastMessageTimestamp?: number;
    previewText?: string;
    avatar?: IAvatarResponse;
    user?: IUserResponse
}
