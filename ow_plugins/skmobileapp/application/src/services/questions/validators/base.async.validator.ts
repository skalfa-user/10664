import { BaseValidator } from './base.validator';
import { Events } from 'ionic-angular';

// services
import { SecureHttpService } from 'services/http/index';
import { SiteConfigsService } from 'services/site-configs'; 

export abstract class BaseAsyncValidator extends BaseValidator {
    protected timer: any;
    protected baseValidationDelay: number = 1000;

    /**
     * Constructor
     */
    constructor(
        protected siteConfigs: SiteConfigsService,
        protected http: SecureHttpService,
        protected events: Events)
    {
        super();
    }

    /**
     * Fire event
     */
    protected fireEvent(validatorName: string, value: string, isValid: boolean): void {
        this.events.publish('asyncValidator:finished', {
            name: validatorName,
            value: value,
            isValid: isValid
        });
    }

    /**
     * Get validation delay
     */
    protected getValidationDelay(): number {
        const apiValidationDelay: any = this.siteConfigs.getConfig('validationDelay');
        const validationDelay: number = apiValidationDelay
            ? parseInt(apiValidationDelay)
            : this.baseValidationDelay;

        return validationDelay;
    }
}
