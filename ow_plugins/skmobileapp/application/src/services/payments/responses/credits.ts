import { ICreditPackResponse} from './credit.pack';

export interface ICreditsResponse {
    isInfoAvailable: boolean;
    packs: Array<ICreditPackResponse>;
    balance: number;
}
