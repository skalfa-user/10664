import {  IUserResponse, IAvatarResponse } from 'services/user/responses';

export interface IMatchedUserResponse {
    id: number;
    isViewed?: boolean;
    isNew?: boolean;
    avatar?: IAvatarResponse;
    user?: IUserResponse
}
