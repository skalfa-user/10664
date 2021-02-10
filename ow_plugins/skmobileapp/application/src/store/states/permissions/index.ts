
export interface IPermission {
    id: string;
    userId?: number;
    permission?: string;
    isPromoted?: boolean;
    isAllowedAfterTracking?: boolean;
    isAllowed?: boolean;
    creditsCost?: number;
    authorizedByCredits?: boolean;
}
