import { Injectable }   from '@angular/core';
import { FormControl, FormGroup } from '@angular/forms';
import { QuestionBase } from './questions/base';
import { QuestionManager } from './manager';

// import validators
import { Validators } from './validators';
import { QuestionBaseValidator } from './questions/base';

@Injectable()
export class QuestionControlService {
    constructor(private validators: Validators) { }

    /**
     * Assign questions to a group
     */
    toFormGroup(questions: QuestionBase[], groupValidator?: any): FormGroup {
        const group: any = {};

        // process questions
        questions.forEach(question => {
            const validators = [];
            const asyncValidators = [];
            const hardCodedValidators: Array<QuestionBaseValidator> = [];

            switch (question.getType()) {
                case QuestionManager.TYPE_URL:
                    hardCodedValidators.push(
                        {name: 'url'}
                    );
                    break;

                case QuestionManager.TYPE_EMAIL:
                    hardCodedValidators.push(
                        {name: 'email'}
                    );
                    break;
            }

            // add hard coded validators
            if (hardCodedValidators.length) {
                const allValidators: Array<QuestionBaseValidator> = question.validators
                    ? question.validators.concat(hardCodedValidators)
                    : hardCodedValidators;

                question.setValidators(allValidators);
            }

            // add validators
            if (question.validators) {
                question.validators.forEach((validatorData) => {
                    if (!this.validators.isValidatorExists(validatorData.name)) {
                        throw new TypeError(`Unsupported validator ${validatorData.name}`);
                    }

                    let validator = this.validators.getValidator(validatorData.name);

                    // add params inside validator
                    if (validatorData.params) {
                        validator.addParams(validatorData.params);
                    }

                    this.validators.isAsyncValidator(validatorData.name)
                        ? asyncValidators.push(validator.validate())
                        : validators.push(validator.validate());
                });
            }

            const control = new FormControl((question.value != null ? question.value : ''), validators, asyncValidators);
            question.setControl(control);

            group[question.key] = control;
        });

        return new FormGroup(group, groupValidator);
    }

    /**
     * Validate form
     */
    validateForm(form: FormGroup): void {
        for (const i in form.controls) {
            form.controls[i].markAsDirty();
            form.controls[i].updateValueAndValidity();
        }
    }

    /**
     * Is form pending
     */
    isFormPending(form: FormGroup): boolean {
        for (const i in form.controls) {
            if (form.controls[i].pending === true) {
                return true;
            }
        }

        return false;
    }
}
