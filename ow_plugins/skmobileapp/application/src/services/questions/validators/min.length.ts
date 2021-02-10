import { Injectable } from '@angular/core';
import { FormControl } from '@angular/forms';
import { BaseValidator, BaseValidatorParams } from './base.validator';

export class MinLengthValidatorFailedResult {
    minLength = {
        valid: false
    };
}

export class MinLengthValidatorParams extends BaseValidatorParams {
    length: number;
}

@Injectable()
export class MinLengthValidator extends BaseValidator {
    protected params: MinLengthValidatorParams;

    /**
     * Validate
     */
    validate(): Function {
        return (control: FormControl): MinLengthValidatorFailedResult | null => {
            if (typeof this.params.length == 'undefined') {
                throw new TypeError(`MinLengthValidator requires the length param`);
            }

            if (control.value === null || !control.value.trim() || control.value.length >= this.params.length) {
                return null;
            }

            return new MinLengthValidatorFailedResult;
        };
    }

    /**
     * Add params
     */
    addParams(params: MinLengthValidatorParams): void {
        this.params = params;
    }
}
