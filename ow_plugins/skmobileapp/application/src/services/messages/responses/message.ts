import {  IConversationResponse, IMessageAttachmentResponse } from './';

export interface IMessageResponse {
    id: number;
    text?: string;
    conversation?: IConversationResponse;
    tempId?: string;
    isSystem?: boolean;
    date?: string;
    dateLabel?: string;
    time?: string;
    isAuthor?: boolean;
    attachments?: Array<IMessageAttachmentResponse>;
    isAuthorized?: boolean;
    timeStamp?: number;
    updateStamp?: number;
    isRecipientRead?: boolean;
    opponentId?: number;
}
