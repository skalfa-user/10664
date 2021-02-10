import { QuestionBase, QuestionBaseOptions, QuestionBaseParams} from './base';

export class CheckboxQuestion extends QuestionBase {
    controlType = 'checkbox';

    /**
     * Constructor
     */
    constructor(options: QuestionBaseOptions, params?: QuestionBaseParams) {
        super(options, params);

        // initial value
        this.value = options.value && options.value === true ? true : false;
    }
}
