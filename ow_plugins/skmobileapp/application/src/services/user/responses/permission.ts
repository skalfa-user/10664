import {  IUserResponse } from 'services/user/responses';

export interface IPermissionResponse {
    id: string;
    permission?: string;
    isPromoted?: boolean;
    isAllowedAfterTracking?: boolean;
    isAllowed?: boolean;
    creditsCost?: number;
    authorizedByCredits?: boolean;
    user?: IUserResponse
}
