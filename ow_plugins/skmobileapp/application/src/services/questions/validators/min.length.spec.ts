import { MinLengthValidator, MinLengthValidatorFailedResult } from './min.length';
import { FormControl } from '@angular/forms';

describe('Min length validator', () => {
    // testable class
    let minLengthValidator: MinLengthValidator; 
    let validatorFunction: Function;
    let failedValidation: MinLengthValidatorFailedResult;

    beforeEach(() => {
        // init validator instance
        minLengthValidator = new MinLengthValidator();
        validatorFunction = minLengthValidator.validate();
        failedValidation = new MinLengthValidatorFailedResult;
    });

    it('validate should return positive result for an empty string including null', () => {
        minLengthValidator.addParams({
            length: 1
        });

        expect(validatorFunction(new FormControl(''))).toBeNull();
        expect(validatorFunction(new FormControl(null))).toBeNull();
    });

    it('validate should return negative result for a string that less than min length parameter', () => {
        minLengthValidator.addParams({
            length: 10
        });

        expect(validatorFunction(new FormControl('test'))).toEqual(failedValidation);
    });

    it('validate should return positive result for a string that more or equal to the min length parameter', () => {
        minLengthValidator.addParams({
            length: 1
        });

        expect(validatorFunction(new FormControl('test'))).toBeNull();
    });

    it('validate should trigger an error if min length parameter is not passed', () => {
        expect(() => validatorFunction(new FormControl('test')))
            .toThrow(new TypeError(`MinLengthValidator requires the length param`));
    });
});
