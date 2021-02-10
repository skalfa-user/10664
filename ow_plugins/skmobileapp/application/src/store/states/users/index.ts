export interface IUserDistance {
    distance: number;
    unit: string;
}

export interface IUserViewQuestions {
    [index: number]: {
        order: number; 
        section: string; 
        items: Array<{
            name: string, 
            label: string, 
            value: string
        }>
    };
}

export interface IUser {
    id: number;
    type?: string;
    userName?: string;
    aboutMe?: string;
    age?: number;
    avatar?: number;
    matchAction?: number | string;
    matchUser?: number;
    bookmark?: number | string;
    hotList?: number | string;
    conversation?: string;
    email?: string;
    isAdmin?: boolean;
    isOnline?: boolean;
    photos?: Array<number | string>;
    permissions?: Array<string>;
    viewQuestions?: IUserViewQuestions;
    compatibility?: number;
    isBlocked?: boolean;
    distance?: IUserDistance;
    videoImCallPermission?: {
        isPermitted: boolean,
        errorMessage: string,
        errorCode: number
    };
    _isMarkedAsBlocked?: boolean;
    _isDataLoaded?: boolean; 
}
