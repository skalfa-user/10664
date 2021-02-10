import { IMapType } from 'store/types';

export interface IConversationData {
    id: string | number;
    isNew?: boolean;
    isReply?: boolean;
    isOpponentRead?: boolean;
    lastMessageTimestamp?: number;
    previewText?: string;
    user?: number;
    messages?: Array<number | string>; 
    _isHidden?: boolean;
    _isRead?: boolean;
    _isMessageListFetched?: boolean;
    _isPending?: boolean;
}

export interface IConversations {
    isFetched?: boolean;
    byId?: IMapType<IConversationData>;
    allIds?: Array<string>;
}
