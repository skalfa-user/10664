import {  IUserResponse, IAvatarResponse, IMatchResponse } from 'services/user/responses';

export interface IGuestResponse {
    id: number;
    viewed?: boolean;
    visitTimestamp?: number;
    visitDate?: string;
    avatar?: IAvatarResponse;
    matchAction?: IMatchResponse;
    user?: IUserResponse
}
