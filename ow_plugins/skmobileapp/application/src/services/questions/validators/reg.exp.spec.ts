import { RegExpValidator, RegExpValidatorFailedResult } from './reg.exp';
import { FormControl } from '@angular/forms';

describe('Reg Exp validator', () => {
    // testable class
    let regExpValidator: RegExpValidator;
    let validatorFunction: Function;
    let failedValidation: RegExpValidatorFailedResult;

    beforeEach(() => {
        // init validator instance
        regExpValidator = new RegExpValidator();
        validatorFunction = regExpValidator.validate();
        failedValidation = new RegExpValidatorFailedResult;
    });

    it('validate should return positive result for an empty string including null', () => {
        regExpValidator.addParams({
            pattern: /^\d+$/
        });

        expect(validatorFunction(new FormControl(''))).toBeNull();
        expect(validatorFunction(new FormControl(null))).toBeNull();
    });

    it('validate should return negative result for a string that less pattern parameter', () => {
        regExpValidator.addParams({
            pattern: /^\d+$/
        });

        expect(validatorFunction(new FormControl('test'))).toEqual(failedValidation);
    });

    it('validate should return positive result for a string that more or equal to the pattern parameter', () => {
        regExpValidator.addParams({
            pattern: /^\d+$/
        });

        expect(validatorFunction(new FormControl('895421368'))).toBeNull();
    });

    it('validate should trigger an error if pattern parameter is not passed', () => {
        expect(() => validatorFunction(new FormControl('895421368')))
            .toThrow(new TypeError(`RegExpValidator requires the pattern param`));
    });
});
