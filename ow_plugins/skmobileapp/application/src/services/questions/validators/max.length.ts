import { Injectable } from '@angular/core';
import { FormControl } from '@angular/forms';
import { BaseValidator, BaseValidatorParams } from './base.validator';

export class MaxLengthValidatorFailedResult {
    maxLength = {
        valid: false
    };
}

export class MaxLengthValidatorParams extends BaseValidatorParams {
    length: number;
}

@Injectable()
export class MaxLengthValidator extends BaseValidator {
    protected params: MaxLengthValidatorParams;

    /**
     * Validate
     */
    validate(): Function {
        return (control: FormControl): MaxLengthValidatorFailedResult | null => {
            if (typeof this.params.length == 'undefined') {
                throw new TypeError(`MaxLengthValidator requires the length param`);
            }

            if (control.value === null || !control.value.trim() || control.value.length <= this.params.length) {
                return null;
            }

            return new MaxLengthValidatorFailedResult;
        };
    }

    /**
     * Add params
     */
    addParams(params: MaxLengthValidatorParams): void {
        this.params = params;
    }
}
