import { Injectable } from '@angular/core';
import { Observable } from 'rxjs/Observable';

// responses
import {
    IVerifyPhoneResponse,
    IResendVerifyPhoneResponse,
    ICountriesResponse,
    IUserDataResponse } from './responses';

export {
    IVerifyPhoneResponse,
    IResendVerifyPhoneResponse,
    ICountriesResponse,
    IUserDataResponse } from './responses';

// services
import { SecureHttpService } from 'services/http';

@Injectable()
export class VerifyPhoneService {

    /**
     * Constructor
     */
    constructor (private http: SecureHttpService) {}

    /**
     * Load search questions
     */
    loadCountries(): Observable<Array<ICountriesResponse>> {
        return this.http.get('/sms-verifications/countries');
    }

    /**
     * Load edit questions
     */
    loadPhonesMe(): Observable<IUserDataResponse> {
        return this.http.get('/sms-verifications/phones/me');
    }

    resendVerificationCode(countryCode: number | string, phoneNumber: number | string): Observable<IResendVerifyPhoneResponse> {
        return this.http.post('/sms-verifications/sms', {
            countryCode: countryCode,
            phoneNumber: phoneNumber
        });
    }

    verificationCode(code: string, userId: number): Observable<IVerifyPhoneResponse> {
        return this.http.post('/validators/verify-sms-code', {
            code: code,
            userId: userId
        });
    }
}
