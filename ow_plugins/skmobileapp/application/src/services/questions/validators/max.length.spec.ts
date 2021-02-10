import { MaxLengthValidator, MaxLengthValidatorFailedResult } from './max.length';
import { FormControl } from '@angular/forms';

describe('Max length validator', () => {
    // testable class
    let maxLengthValidator: MaxLengthValidator; 
    let validatorFunction: Function;
    let failedValidation: MaxLengthValidatorFailedResult;

    beforeEach(() => {
        // init validator instance
        maxLengthValidator = new MaxLengthValidator();
        validatorFunction = maxLengthValidator.validate();
        failedValidation = new MaxLengthValidatorFailedResult;
    });

    it('validate should return positive result for an empty string including null', () => {
        maxLengthValidator.addParams({
            length: 1
        });

        expect(validatorFunction(new FormControl(''))).toBeNull();
        expect(validatorFunction(new FormControl(null))).toBeNull();
    });
 
    it('validate should return negative result for a string that more than max length parameter', () => {
        maxLengthValidator.addParams({
            length: 2
        });

        expect(validatorFunction(new FormControl('test'))).toEqual(failedValidation);
    });

    it('validate should return positive result for a string that less or equal to the max length parameter', () => {
        maxLengthValidator.addParams({
            length: 4
        });

        expect(validatorFunction(new FormControl('test'))).toBeNull();
    });

    it('validate should trigger an error if max length parameter is not passed', () => {
        expect(() => validatorFunction(new FormControl('test')))
            .toThrow(new TypeError(`MaxLengthValidator requires the length param`));
    });
});
