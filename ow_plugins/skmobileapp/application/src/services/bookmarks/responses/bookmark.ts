import {  IUserResponse, IAvatarResponse, IMatchResponse } from 'services/user/responses';

export interface IBookmarkResponse {
    id: number;
    avatar?: IAvatarResponse;
    matchAction?: IMatchResponse;
    user?: IUserResponse
}
