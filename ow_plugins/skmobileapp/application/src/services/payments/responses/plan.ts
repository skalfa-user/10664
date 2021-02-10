export interface IMembershipPlanResponse {
    id: number;
    price?: number;
    period?: number;
    periodUnits?: string;
    productId?: string;
    definedProductId?: string;
    isRecurring?: boolean;
}
