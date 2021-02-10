import { Injectable } from '@angular/core';
import { FormControl } from '@angular/forms';
import { BaseValidator, BaseValidatorParams } from './base.validator';

export class RegExpValidatorFailedResult {
    regExp = {
        valid: false
    };
}

export class RegExpValidatorParams extends BaseValidatorParams {
    pattern: RegExp;
}

@Injectable()
export class RegExpValidator extends BaseValidator {

    protected params: RegExpValidatorParams;

    /**
     * Validate
     */
    validate(): Function {
        return (control: FormControl): RegExpValidatorFailedResult | null => {

            if (typeof this.params.pattern == 'undefined') {
                throw new TypeError(`RegExpValidator requires the pattern param`);
            }

            const regExp: RegExp = new RegExp(this.params.pattern);

            if (control.value === null || !control.value.trim() || regExp.test(control.value)) {
                return null;
            }

            return new RegExpValidatorFailedResult;
        };
    }

    /**
     * Add params
     */
    addParams(params: RegExpValidatorParams): void {
        this.params = params;
    }
}