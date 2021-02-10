import { QuestionBase, QuestionBaseParams, QuestionBaseOptions } from './base';

export class QuestionTextareaParams extends QuestionBaseParams {
    questionClass: string;
    hideErrors: boolean;
    hideWarning: boolean;
    rows: number;
};

export class TextareaQuestion extends QuestionBase {
    controlType = 'textarea';
    rows: number = 4;

    /**
     * Constructor
     */
    constructor(options: QuestionBaseOptions, params?: QuestionTextareaParams) {
        super(options, params);

        if (params && params.rows) {
            this.rows = params.rows;
        }
    }
}
