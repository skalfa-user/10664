import { Injectable } from '@angular/core';
import { FormControl } from '@angular/forms';
import { BaseValidator } from './base.validator';

export class RequireValidatorFailedResult {
    require = {
        valid: false
    };
}

@Injectable()
export class RequireValidator extends BaseValidator {
    /**
     * Validate
     */
    validate(): Function {
        return (control: FormControl): RequireValidatorFailedResult | null => {
            const isValid = this.isValid(control.value);

            if (isValid) {
                return null;
            }

            return new RequireValidatorFailedResult();
        };
    }

    /**
     * Is valid
     */
     isValid(value: any): boolean {
        if (value === null) {
            return false;
        } 
 
        const varType = typeof value;
        let isValid = false;

        switch (varType) {
            case 'string' :
            case 'number' :
                isValid = value.toString().trim() != '';

                break;

            case 'boolean' :
                isValid = value === true;

                break;

            case 'object' :
                if (Array.isArray(value)) {
                    isValid = value.length > 0;
                } 
                else {
                    const objectProperties: Array<any> = Object.getOwnPropertyNames(value);

                    if (objectProperties.length) {
                        let emptyProperties = false;

                        // check all object's properties
                        objectProperties.forEach((propertyName) => {
                            if (!this.isValid(value[propertyName])) {
                                emptyProperties = true;
                            }
                        });

                        isValid = !emptyProperties;
                    }
                }
                break;

            default :
        }

        return isValid;
    }
}
