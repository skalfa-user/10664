import { IQuestionListResponse } from './question';

export interface ISearchQuestionResponse {
    preferredAccountType: number;
    questions: {
        [K: string]: Array<IQuestionListResponse>
    };
}
