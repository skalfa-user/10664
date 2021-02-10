import { Injectable } from '@angular/core';
import { FormControl } from '@angular/forms';
import { Events } from 'ionic-angular';

// services
import { SecureHttpService } from 'services/http';
import { SiteConfigsService } from 'services/site-configs';
import { AuthService } from 'services/auth';

// import base async validator
import { BaseAsyncValidator } from './base.async.validator';

// responses
import { IValidatorResponse } from './responses';

export class UserEmailValidatorFailedResult {
    userEmail: boolean = true;
}

@Injectable()
export class UserEmailValidator extends BaseAsyncValidator {
    /**
     * Constructor
     */
    constructor(
        protected auth: AuthService,
        protected http: SecureHttpService,
        protected siteConfigs: SiteConfigsService,
        protected events: Events)
    {
        super(siteConfigs, http, events);
    }

    /**
     * Validate
     */
    validate(): Function {
        return (control: FormControl): Promise<UserEmailValidatorFailedResult | null> => {
            clearTimeout(this.timer);

            return new Promise((resolve) => {
                this.timer = setTimeout(() => {

                    if (control.value !== null && control.value.trim()) {
                        const email: string = control.value;
                        const options = this.auth.isAuthenticated()
                            ? {email: email, user: this.auth.getUser().name}
                            : {email: email};

                        this.http.post('/validators/user-email', options)
                            .subscribe((data: IValidatorResponse) => {
                                if (!data.valid || control.value !== email) {
                                    this.fireEvent('userEmail', control.value, false);
                                    resolve(new UserEmailValidatorFailedResult);

                                    return;
                                }

                                this.fireEvent('userEmail', control.value, true);
                                resolve(null);
                            }, () => {
                                this.fireEvent('userEmail', control.value, false);
                                resolve(new UserEmailValidatorFailedResult);
                            });
                    }
                    else {
                        this.fireEvent('userEmail', control.value, false);
                        resolve(new UserEmailValidatorFailedResult); // user email cannot be empty
                    }

                }, this.getValidationDelay());
            });

        };
    }
}
