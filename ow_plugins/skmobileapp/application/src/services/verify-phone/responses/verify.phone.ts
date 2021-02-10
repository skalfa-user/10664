export interface IVerifyPhoneResponse {
    valid: boolean;
}

export interface IResendVerifyPhoneResponse {
    success: boolean;
    message?: string | null
}