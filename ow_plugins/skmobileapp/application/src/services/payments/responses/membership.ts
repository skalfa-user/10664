import { IMembershipActionResponse} from './action';
import { IMembershipPlanResponse } from './plan';

export interface IMembershipResponse {
    id: number;
    title?: string;
    isActive?: boolean;
    isActiveAndTrial?: boolean;
    isPlansAvailable?: boolean;
    expire?: string;
    isRecurring?: boolean;
    actions?: Array<IMembershipActionResponse>,
    plans?: Array<IMembershipPlanResponse>
}
