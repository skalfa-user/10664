import { ICreditActionResponse} from './credit.action';

export interface ICreditsInfoResponse {
    earning: Array<ICreditActionResponse>;
    losing: Array<ICreditActionResponse>;
}
