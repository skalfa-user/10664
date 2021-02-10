import { Injectable } from '@angular/core';
import { FormControl } from '@angular/forms';
import { Events } from 'ionic-angular';

// services
import { SecureHttpService } from 'services/http';
import { AuthService } from 'services/auth';
import { SiteConfigsService } from 'services/site-configs';

// import base async validator
import { BaseAsyncValidator } from './base.async.validator';

// responses
import { IValidatorResponse } from './responses';

export class UserNameValidatorFailedResult {
    userName: boolean = true;
}

@Injectable()
export class UserNameValidator extends BaseAsyncValidator {
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
        return (control: FormControl): Promise<UserNameValidatorFailedResult | null> => {
            clearTimeout(this.timer);

            return new Promise((resolve) => {
                this.timer = setTimeout(() => {

                    if (control.value !== null && control.value.trim()) {
                        const username: string = control.value;
                        const options = this.auth.isAuthenticated()
                            ? {userName: username, oldUserName: this.auth.getUser().name}
                            : {userName: username};

                        this.http.post('/validators/user-name', options)
                            .subscribe((data: IValidatorResponse) => {
                                if (!data.valid || control.value != username) {
                                    this.fireEvent('userName', control.value, false);
                                    resolve(new UserNameValidatorFailedResult);

                                    return;
                                }

                                this.fireEvent('userName', control.value, true);
                                resolve(null);
                            }, () => {
                                this.fireEvent('userName', control.value, false);
                                resolve(new UserNameValidatorFailedResult);
                            });
                    }
                    else {
                        this.fireEvent('userName', control.value, false);
                        resolve(new UserNameValidatorFailedResult); // user name cannot be empty
                    }

                }, this.getValidationDelay());
            });

        };
    }
}
