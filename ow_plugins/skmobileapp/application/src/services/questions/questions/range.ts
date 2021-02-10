import { QuestionBase, QuestionBaseOptions, QuestionBaseParams, QuestionBaseValues, QuestionBaseValidator } from './base';

export class QuestionRangeOptions extends QuestionBaseOptions {
    value: RangeValue;
    values: Array<QuestionBaseValues>;
    key: string;
    label: string;
    placeholder: string;
    controlType: string;
    validators: Array<QuestionBaseValidator>;
};

export class QuestionRangeParams extends QuestionBaseParams {
    questionClass: string;
    hideErrors: boolean;
    hideWarning: boolean;
    min: number;
    max: number;
};

export class RangeValue {
    lower: number;
    upper: number
};

export class RangeQuestion extends QuestionBase {
    controlType = 'range';
    min = 0;
    max = 100;

    /**
     * Constructor
     */
    constructor(options: QuestionRangeOptions, params?: QuestionRangeParams) {
        super(options, params);

        // init extra prams
        if (params) {
            params.min ? this.min = params.min : null;
            params.max ? this.max = params.max : null;
        }

        // initial value
        this.value = {
            lower: options.value && options.value.lower  ? options.value.lower : this.min,
            upper: options.value && options.value.upper ? options.value.upper  : this.max
        };
    }
}
