import {  IUserResponse, IAvatarResponse, IMatchResponse } from 'services/user/responses';

export interface ICompatibleUserResponse {
    id: number;
    avatar?: IAvatarResponse;
    matchAction?: IMatchResponse;
    user?: IUserResponse
}

