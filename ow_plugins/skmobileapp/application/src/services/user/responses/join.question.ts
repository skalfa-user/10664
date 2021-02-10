import { IQuestionListResponse } from './question';

export interface IJoinQuestionResponse {
    id: number,
    questions: Array<IQuestionListResponse>
}
