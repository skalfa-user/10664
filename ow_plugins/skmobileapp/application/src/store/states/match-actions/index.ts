
export interface IMatchAction {
    id: number | string;
    type?: 'like' | 'dislike';
    userId?: number | string;
    isMutual?: boolean;
    createStamp?: number;
    isRead?: boolean;
    isNew?: boolean;
}
