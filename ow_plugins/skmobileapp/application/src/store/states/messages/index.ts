export interface IMessageAttachment {
    downloadUrl?: string;
    fileName?: string;
    fileSize?: string | number;
    type?: string;
}

export interface IMessage {
    id?: number | string;
    text?: string;
    conversation?: string;
    tempId?: string;
    isSystem?: boolean;
    date?: string;
    dateLabel?: string;
    isAuthor?: boolean;
    attachments?: Array<IMessageAttachment>;
    isAuthorized?: boolean;
    timeStamp?: number;
    updateStamp?: number;
    isRecipientRead?: boolean;
    opponentId?: number;
    file?: File | Blob;
    _isPending?: boolean;
    _isRead?: boolean;
    _isError?: boolean;
    _errorDescription?: string;
}
