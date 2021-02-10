export interface IVideoPreview {
    id?: number;
    userId?: number;
    fileName?: string;
    readableFileName?: string;
    status?: string;
    statusLabel?: string;
    privacy?: string;
    coverImage?: string;
    videos?: Array<any>;
}