export interface IMatchResponse {
    id: number;
    type?: 'like' | 'dislike';
    isMutual?: boolean;
    userId?: number;
    createStamp?: number;
    isRead?: boolean;
    isNew?: boolean;
    user?: {
        id: number;
    }
}
