import { combineReducers } from 'redux';
import { IMapType } from 'store/types';

// states
import {
    IApplication, 
    IUser,
    IAvatars,
    IPhotos,
    IPermission,
    IGuests,
    IMatchAction,
    ICompatibleUsers,
    IBookmarks,
    IHotList,
    IMatchedUsers,
    IConversations,
    IMessage,
    IVideoImNotifications
} from 'store/states'; 

// reducers and initial states
import { 
    application, 
    applicationInitialState, 
    configs, 
    configsInitialState,
    users,
    usersInitialState,
    avatars,
    avatarsInitialState,
    photos,
    photosInitialState,
    permissions,
    permissionsInitialState,
    guests,
    guestsInitialState,
    matchActions,
    matchActionsInitialState,
    compatibleUsers,
    compatibleUsersInitialState,
    bookmarks,
    bookmarksInitialState,
    hotList,
    hotListInitialState,
    matchedUsers,
    matchedUsersInitialState,
    conversations,
    conversationsInitialState,
    messages,
    messagesInitialState,
    messagesQueue,
    messagesQueueInitialState,
    videoImNotifications,
    videoImNotificationsInitialState
} from 'store/reducers'; 

/**
 * App state
 */
export interface IAppState {
    application?: IApplication;
    configs?: IMapType<any>,
    users?: IMapType<IUser>,
    avatars?: IAvatars,
    photos?: IPhotos,
    permissions?: IMapType<IPermission>,
    guests?: IGuests,
    matchActions?: IMapType<IMatchAction>,
    compatibleUsers?: ICompatibleUsers,
    bookmarks?: IBookmarks,
    hotList?: IHotList,
    matchedUsers?: IMatchedUsers,
    conversations?: IConversations,
    messages?: IMapType<IMessage>
    messagesQueue?: Array<IMessage>,
    videoImNotifications?: IVideoImNotifications
}

/**
 * Initial state
 */
export const INITIAL_STATE: IAppState = {
    application: applicationInitialState,
    configs: configsInitialState,
    users: usersInitialState,
    avatars: avatarsInitialState,
    photos: photosInitialState,
    permissions: permissionsInitialState,
    guests: guestsInitialState,
    matchActions: matchActionsInitialState,
    compatibleUsers: compatibleUsersInitialState,
    bookmarks: bookmarksInitialState,
    hotList: hotListInitialState,
    matchedUsers: matchedUsersInitialState,
    conversations: conversationsInitialState,
    messages: messagesInitialState,
    messagesQueue: messagesQueueInitialState,
    videoImNotifications: videoImNotificationsInitialState
};

/**
 * Root reducer
 */
export const rootReducer = combineReducers({
    application: application,
    configs: configs,
    users: users,
    avatars: avatars,
    photos: photos,
    permissions: permissions,
    guests: guests,
    matchActions: matchActions,
    compatibleUsers: compatibleUsers,
    bookmarks: bookmarks,
    hotList: hotList,
    matchedUsers: matchedUsers,
    conversations: conversations,
    messages: messages,
    messagesQueue: messagesQueue,
    videoImNotifications: videoImNotifications
}); 
