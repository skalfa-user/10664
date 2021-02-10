import { QuestionBaseOptions, QuestionBaseValues, QuestionBaseValidator } from './base';
import { DateQuestion, QuestionDateParams } from './date';
import { convertDataToISO } from 'ionic-angular/util/datetime-util';

export class QuestionDateRangeOptions extends QuestionBaseOptions {
    value: DateRangeValue;
    values: Array<QuestionBaseValues>;
    key: string;
    label: string;
    placeholder: string;
    controlType: string;
    validators: Array<QuestionBaseValidator>;
};

export class DateRangeValue {
    start: string;
    end: string
};

export class DateRangeQuestion extends DateQuestion {
    controlType: string = 'date_range';

    /**
     * Constructor
     */
    constructor(options: QuestionDateRangeOptions, params?: QuestionDateParams) {
        super(options, params);

        // initial value
        this.value = {
            start: options.value && options.value.start 
                ? options.value.start 
                : null,
            end: options.value && options.value.end 
                ? options.value.end 
                : null
        };
    }

    /**
     * Update start value
     */
    updateStartValue(value): void {
        this.setControlValue({
            start: convertDataToISO(value),
            end: this.controlView.value.end
        });
    }

    /**
     * Update end value
     */
    updateEndValue(value): void {
        this.setControlValue({
            start: this.controlView.value.start,
            end: convertDataToISO(value)
        });
    }
}
