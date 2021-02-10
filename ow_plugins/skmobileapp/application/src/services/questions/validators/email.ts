import { Injectable } from '@angular/core';
import { FormControl } from '@angular/forms';
import { BaseValidator } from './base.validator';

// services
import { SiteConfigsService } from 'services/site-configs';

export class EmailValidatorFailedResult {
    email = {
        valid: false
    };
}

@Injectable()
export class EmailValidator extends BaseValidator {
    protected baseRegexp: RegExp = /^([\w\-\.\+\%]*[\w])@((?:[A-Za-z0-9\-]+\.)+[A-Za-z]{2,})$/;

    /**
     * Constructor
     */
    constructor(private siteConfigs: SiteConfigsService) {
        super();
    }

    /**
     * Validate
     */
    validate(): Function {
        return (control: FormControl): EmailValidatorFailedResult | null => {
            if (control.value === null || !control.value.trim() || this.getRegexp().test(control.value)) {
                return null;
            }

            return new EmailValidatorFailedResult;
        };
    }

    /**
     * Get regexp
     */
    protected getRegexp(): RegExp {
        let apiRegexp: any = this.siteConfigs.getConfig('emailRegexp');
        let emailRegexp: RegExp = apiRegexp
            ? new RegExp(apiRegexp)
            : this.baseRegexp;

        return emailRegexp;
    }
}
