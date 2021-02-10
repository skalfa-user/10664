import { QuestionBase, QuestionBaseOptions, QuestionBaseParams, QuestionBaseValues, QuestionBaseValidator } from './base';

export class QuestionTextOptions extends QuestionBaseOptions {
    value: any;
    values: Array<QuestionBaseValues>;
    key: string;
    label: string;
    placeholder: string;
    type: string;
    validators: Array<QuestionBaseValidator>;
};

export class QuestionTextParams extends QuestionBaseParams {
    questionClass: string;
    hideErrors: boolean;
    hideWarning: boolean;
    stacked: boolean;
};

export class TextQuestion extends QuestionBase {
    controlType = 'text';
    stackedInput: boolean = false;
    type: string;

    /**
     * Constructor
     */
    constructor(options: QuestionTextOptions, params?: QuestionTextParams) {
        super(options, params);

        this.type = options['type'] || '';

        if (params && params.stacked) {
            this.stackedInput = true;
        }
    }

    /**
     * Get type
     */
    getType(): string {
        return this.type ? this.type : this.controlType;
    }
}
