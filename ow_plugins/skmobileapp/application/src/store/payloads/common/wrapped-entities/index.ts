import { IEntitiesPayload } from 'store/payloads';

export interface IWrappedEntitiesPayload {
    id?: string | number;
    data: IEntitiesPayload;
}
