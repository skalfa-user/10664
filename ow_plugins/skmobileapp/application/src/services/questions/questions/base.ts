import { AbstractControl } from '@angular/forms';

export class QuestionBaseOptions {
    value: any;
    values: Array<QuestionBaseValues>;
    key: string;
    label: string;
    placeholder: string;
    controlType: string;
    validators: Array<QuestionBaseValidator>;
};

export class QuestionBaseParams {
    questionClass: string;
    hideErrors: boolean;
    hideWarning: boolean;
};

export class QuestionBaseValidator {
    name: string; 
    message?: string; 
    params?: {};
}

export class QuestionBaseValues {
    value: string | number;
    title: string;
}

export abstract class QuestionBase {
    controlView: AbstractControl; // control built by the question
    value: any; // initial value will never be changed
    values: Array<QuestionBaseValues>; // initial values will never be changed
    key: string;
    label: string;
    placeholder: string;
    controlType: string;
    validators: Array<QuestionBaseValidator>;
    params: QuestionBaseParams;
    questionChanged: boolean = false;

    /**
     * Constructor
     */
    constructor(options: QuestionBaseOptions, params: QuestionBaseParams) {
        this.value = options.value;
        this.values = options.values || null;
        this.key = options.key || '';
        this.label = options.label || '';
        this.placeholder = options.placeholder || '';
        this.controlType = options.controlType || '';
        this.validators = options.validators || null;
        this.params = params;
    }

    /**
     * Set control
     */
    setControl(controlView: AbstractControl): void {
        this.controlView = controlView;
    }

    /**
     * Set validators
     */
    setValidators(validators: Array<QuestionBaseValidator>) {
        this.validators = validators;
    }

    /**
     * Get type
     */
    getType(): string {
        return this.controlType;
    }

    /**
     * Set control value
     */
    setControlValue(value: any): void {
        this.controlView.setValue(value);

        // mark question as changed
        if (!this.questionChanged) {
            this.controlView.markAsDirty({
                onlySelf: true
            });

            this.controlView.markAsTouched({
                onlySelf: true
            });

            this.questionChanged = true;
        }

        // trigger manually about update in the question
        this.controlView.updateValueAndValidity({
            onlySelf: true,
            emitEvent: true
        });
    }
}
