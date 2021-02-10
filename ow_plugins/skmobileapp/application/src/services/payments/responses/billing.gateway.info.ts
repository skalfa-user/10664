import { IQuestionListResponse } from 'services/user/responses';

export interface IBillingGatewayOptionsResponse {
    key: string;
    value: string;
}

export interface IBillingGatewayInfoResponse {
    options?: Array<IBillingGatewayOptionsResponse>;
    formUrl?: string;
    questions?: Array<IQuestionListResponse>;
}
