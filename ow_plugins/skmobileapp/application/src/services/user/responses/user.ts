
import { IPermissionResponse} from './permission';
import { IPhotoResponse} from './photo';
import { IAvatarResponse} from './avatar';
import { IDistanceResponse } from './distance';
import { IViewQuestionResponse } from './view.question';
import { IMatchResponse } from './match.action';
import { IBookmarkResponse } from './bookmark';

export interface IUserResponse {
    id?: string | number;
    aboutMe?: string,
    age?: number;
    email?: string;
    isAdmin?: boolean;
    isOnline?: boolean;
    token?: string;
    type?: string;
    userName?: string;
    avatar?: IAvatarResponse;
    permissions?: Array<IPermissionResponse>;
    photos?: Array<IPhotoResponse>,
    compatibility?: number;
    isBlocked?: boolean;
    distance?: IDistanceResponse;
    viewQuestions?: IViewQuestionResponse;
    matchAction?: IMatchResponse;
    bookmark?: IBookmarkResponse;
}

