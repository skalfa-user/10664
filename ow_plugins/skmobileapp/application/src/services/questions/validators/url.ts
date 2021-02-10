import { Injectable } from '@angular/core';
import { FormControl } from '@angular/forms';
import { BaseValidator } from './base.validator';

// services
import { SiteConfigsService } from 'services/site-configs';

export class UrlValidatorFailedResult {
    url = {
        valid: false
    };
}

@Injectable()
export class UrlValidator extends BaseValidator {
    protected baseRegexp: RegExp = /^(http(s)?:\/\/)?((\d+\.\d+\.\d+\.\d+)|(([\w-]+\.)+([a-z,A-Z][\w-]*)))(:[1-9][0-9]*)?(\/?([\w-.\,\/:%+@&*=~]+[\w- \,.\/?:%+@&=*|]*)?)?(#(.*))?$/i;

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
        return (control: FormControl): UrlValidatorFailedResult | null => {
            if (control.value === null || !control.value.trim() || this.getRegexp().test(control.value)) {
                return null;
            }

            return new UrlValidatorFailedResult;
        };
    }

    /**
     * Get regexp
     */
    protected getRegexp(): RegExp {
        const apiRegexp: any = this.siteConfigs.getConfig('urlRegexp');
        const urlRegexp: RegExp = apiRegexp
            ? new RegExp(apiRegexp, 'i')
            : this.baseRegexp;

        return urlRegexp;
    }
}
