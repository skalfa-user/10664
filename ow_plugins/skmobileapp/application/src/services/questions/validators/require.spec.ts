import { RequireValidator, RequireValidatorFailedResult } from './require';
import { FormControl } from '@angular/forms';

describe('Require validator', () => {
    // testable class
    let requireValidator: RequireValidator; 
    let validatorFunction: Function;
    let failedValidation: RequireValidatorFailedResult;

    beforeEach(() => {
        // init validator instance
        requireValidator = new RequireValidator();
        validatorFunction = requireValidator.validate();
        failedValidation = new RequireValidatorFailedResult;
    });
 
    it('validate should return negative result for an empty string including null', () => {
        expect(validatorFunction(new FormControl(''))).toEqual(failedValidation);
        expect(validatorFunction(new FormControl(null))).toEqual(failedValidation);
    });

    it('validate should return positive result for a non empty string or numbers', () => {
        expect(validatorFunction(new FormControl('test'))).toBeNull();
        expect(validatorFunction(new FormControl(1))).toBeNull();
        expect(validatorFunction(new FormControl(0))).toBeNull();
    });

    it('validate should return negative result for a negative boolean value', () => {
        expect(validatorFunction(new FormControl(false))).toEqual(failedValidation);
    });

    it('validate should return positive result for a positive boolean value', () => {
        expect(validatorFunction(new FormControl(true))).toBeNull();
    });

    it('validate should return negative result for an empty array', () => {
        expect(validatorFunction(new FormControl([]))).toEqual(failedValidation);
    });

    it('validate should return positive result for a not empty array', () => {
        expect(validatorFunction(new FormControl([1, 2, 3]))).toBeNull();
    });

    it('validate should return negative result for an object with some empty properties', () => {
        expect(validatorFunction(new FormControl({a: '', b: 1}))).toEqual(failedValidation);
    });

    it('validate should return positive result for an object with non empty properties', () => {
        expect(validatorFunction(new FormControl({a: true, b: 1}))).toBeNull();
    });

    it('validate should return negative result for an empty object', () => {
        expect(validatorFunction(new FormControl({}))).toEqual(failedValidation);
    });
});
