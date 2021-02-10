export interface IVideoUserDataResponse {
    userId?: number;
    userName?: string;
    avatar?: {
        active?: boolean;
        bigUrl?: string;
        id: number;
        pendingBigUrl?: string;
        pendingUrl?: string;
        url?: string;
        userId?: number;
    };
}