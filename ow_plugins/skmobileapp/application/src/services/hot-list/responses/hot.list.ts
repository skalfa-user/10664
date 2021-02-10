import {  IUserResponse, IAvatarResponse } from 'services/user/responses';

export interface IHotListResponse {
    id: number;
    avatar?: IAvatarResponse;
    user?: IUserResponse
}
