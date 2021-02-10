import { QuestionBase, QuestionBaseOptions, QuestionBaseParams, QuestionBaseValidator, QuestionBaseValues } from './base';

export class QuestionSelectOptions extends QuestionBaseOptions {
    value: any;
    values: Array<QuestionBaseValues>;
    key: string;
    label: string;
    placeholder: string;
    multiple: boolean;
    validators: Array<QuestionBaseValidator>;
};

export class QuestionSelectParams extends QuestionBaseParams {
    questionClass: string;
    hideErrors: boolean;
    hideWarning: boolean;
    hideEmptyValue: boolean;
};

export class SelectQuestion extends QuestionBase {
    controlType = 'select';
    multiple: boolean;
    hideEmptyValue: boolean = false;

    /**
     * Constructor
     */
    constructor(options: QuestionSelectOptions, params?: QuestionSelectParams) {
        super(options, params);

        this.multiple = options['multiple'] || false;

        // init extra params
        if (params) {
            params.hideEmptyValue ? this.hideEmptyValue = params.hideEmptyValue : false;
        }
    }
}

