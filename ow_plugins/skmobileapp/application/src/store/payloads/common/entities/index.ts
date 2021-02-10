import { IMapType } from 'store/types';

import { 
    IAvatarDataPayload,
    IUserDataPayload,
    IBookmarkDataPayload,
    IMatchActionDataPayload,
    IPermissionDataPayload,
    IPhotoDataPayload,
    IConversationDataPayload,
    IHotListDataPayload,
    ICompatibleUserDataPayload,
    IGuestDataPayload,
    IMatchedUserDataPayload,
    IVideoNotificationDataPayload,
    IMessageDataPayload
} from 'store/payloads';

export interface IEntitiesPayload {
    entities: {
        avatar?: IMapType<IAvatarDataPayload>;
        avatars?: IMapType<IAvatarDataPayload>;
        user?: IMapType<IUserDataPayload>;
        users?: IMapType<IUserDataPayload>
        bookmark?: IMapType<IBookmarkDataPayload>;
        bookmarks?: IMapType<IBookmarkDataPayload>;
        matchAction?: IMapType<IMatchActionDataPayload>;
        matchActions?: IMapType<IMatchActionDataPayload>;
        viewQuestions?: any;
        permissions?: IMapType<IPermissionDataPayload>;
        photos?: IMapType<IPhotoDataPayload>;
        conversations?: IMapType<IConversationDataPayload>;
        hotList?: IMapType<IHotListDataPayload>;
        compatibleUsers?: IMapType<ICompatibleUserDataPayload>;
        guests?: IMapType<IGuestDataPayload>;
        matchedUsers?: IMapType<IMatchedUserDataPayload>;
        notifications?: IMapType<IVideoNotificationDataPayload>;
        messages?: IMapType<IMessageDataPayload>;
    },
    result?: any
}
