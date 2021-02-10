import { Injectable }   from '@angular/core';
import { ModalController } from 'ionic-angular';

// import questions
import { QuestionBase, QuestionBaseOptions, QuestionBaseParams } from './questions/base';
import { TextQuestion, QuestionTextOptions, QuestionTextParams } from './questions/text';
import { SelectQuestion, QuestionSelectOptions, QuestionSelectParams } from './questions/select';
import { TextareaQuestion, QuestionTextareaParams } from './questions/textarea';
import { RangeQuestion, QuestionRangeOptions, QuestionRangeParams } from './questions/range';
import { CheckboxQuestion } from './questions/checkbox';
import { DateQuestion, QuestionDateParams } from './questions/date';
import { DateRangeQuestion, QuestionDateRangeOptions } from './questions/date.range';
import { GoogleMapLocationQuestion } from './questions/googlemap.location';
import { ExtendedGoogleMapLocationQuestion, QuestionExtendedGoogleMapLocationParams } from './questions/extended.googlemap.location';

@Injectable()
export class QuestionManager {
    // list of available questions
    static readonly TYPE_LOCATION: string = 'location';
    static readonly TYPE_URL: string = 'url';
    static readonly TYPE_TEXT: string = 'text';
    static readonly TYPE_EMAIL: string = 'email';
    static readonly TYPE_PASSWORD: string = 'password';
    static readonly TYPE_MULTISELECT: string = 'multiselect';
    static readonly TYPE_SELECT: string = 'select';
    static readonly TYPE_FSELECT: string = 'fselect';
    static readonly TYPE_RADIO: string = 'radio';
    static readonly TYPE_TEXTAREA: string = 'textarea';
    static readonly TYPE_RANGE: string = 'range';
    static readonly TYPE_CHECKBOX: string = 'checkbox';
    static readonly TYPE_DATE: string = 'date';
    static readonly TYPE_DATE_RANGE: string = 'date_range';
    static readonly TYPE_AGE: string = 'age';
    static readonly TYPE_BIRTHDATE: string = 'birthdate';
    static readonly TYPE_MULTICHECKBOX: string = 'multicheckbox';
    static readonly TYPE_GOOGLEMAP_LOCATION: string = 'googlemap_location';
    static readonly TYPE_EXTENDED_GOOGLEMAP_LOCATION: string = 'extended_googlemap_location';

    /**
     * Constructor
     */
    constructor(
        private modalController: ModalController) {}

    /**
     * Get config
     */
    getQuestion(type: string, options: any = {}, params: any = {}): QuestionBase {
        switch (type) {
            case QuestionManager.TYPE_LOCATION:
            case QuestionManager.TYPE_URL:
            case QuestionManager.TYPE_TEXT:
            case QuestionManager.TYPE_EMAIL:
            case QuestionManager.TYPE_PASSWORD:
                const textOptions = new QuestionTextOptions();

                if (options) {
                    textOptions.value = options.value;
                    textOptions.values = options.values;
                    textOptions.key = options.key;
                    textOptions.label = options.label;
                    textOptions.placeholder = options.placeholder;
                    textOptions.type = type != QuestionManager.TYPE_LOCATION ? type : 'text';
                    textOptions.validators = options.validators;
                }

                const textParams = new QuestionTextParams();

                if (params) {
                    textParams.questionClass = params.questionClass;
                    textParams.hideErrors = params.hideErrors;
                    textParams.hideWarning = params.hideWarning;
                    textParams.stacked = params.stacked;
                }

                return new TextQuestion(textOptions, textParams);

            case QuestionManager.TYPE_RADIO:
            case QuestionManager.TYPE_SELECT:
            case QuestionManager.TYPE_FSELECT:
                const selectOptions = new QuestionSelectOptions();

                if (options) {
                    selectOptions.value = options.value;
                    selectOptions.values = options.values;
                    selectOptions.key = options.key;
                    selectOptions.label = options.label;
                    selectOptions.placeholder = options.placeholder;
                    selectOptions.validators = options.validators;
                }

                const selectParams = new QuestionSelectParams();

                if (params) {
                    selectParams.questionClass = params.questionClass;
                    selectParams.hideErrors = params.hideErrors;
                    selectParams.hideWarning = params.hideWarning;
                    selectParams.hideEmptyValue = params.hideEmptyValue;
                }

                return new SelectQuestion(selectOptions, selectParams);

            case QuestionManager.TYPE_MULTISELECT:
            case QuestionManager.TYPE_MULTICHECKBOX:
                const multiSelectOptions = new QuestionSelectOptions();

                if (options) {
                    multiSelectOptions.value = options.value;
                    multiSelectOptions.values = options.values;
                    multiSelectOptions.key = options.key;
                    multiSelectOptions.label = options.label;
                    multiSelectOptions.placeholder = options.placeholder;
                    multiSelectOptions.multiple = true;
                    multiSelectOptions.validators = options.validators;
                }

                const multiSelectParams = new QuestionSelectParams();

                if (params) {
                    multiSelectParams.questionClass = params.questionClass;
                    multiSelectParams.hideErrors = params.hideErrors;
                    multiSelectParams.hideWarning = params.hideWarning;
                    multiSelectParams.hideEmptyValue = params.hideEmptyValue;
                }

                return new SelectQuestion(multiSelectOptions, multiSelectParams);

            case QuestionManager.TYPE_TEXTAREA:
                const textAreaOptions = new QuestionBaseOptions();

                if (options) {
                    textAreaOptions.value = options.value;
                    textAreaOptions.values = options.values;
                    textAreaOptions.key = options.key;
                    textAreaOptions.label = options.label;
                    textAreaOptions.placeholder = options.placeholder;
                    textAreaOptions.validators = options.validators;
                }

                const textAreaParams = new QuestionTextareaParams();

                if (params) {
                    textAreaParams.questionClass = params.questionClass;
                    textAreaParams.hideErrors = params.hideErrors;
                    textAreaParams.hideWarning = params.hideWarning;
                    textAreaParams.rows = params.rows;
                }

                return new TextareaQuestion(textAreaOptions, textAreaParams);

            case QuestionManager.TYPE_RANGE:
                const rangeOptions = new QuestionRangeOptions();

                if (options) {
                    rangeOptions.value = options.value;
                    rangeOptions.values = options.values;
                    rangeOptions.key = options.key;
                    rangeOptions.label = options.label;
                    rangeOptions.placeholder = options.placeholder;
                    rangeOptions.validators = options.validators;
                }

                const rangeParams = new QuestionRangeParams();

                if (params) {
                    rangeParams.questionClass = params.questionClass;
                    rangeParams.hideErrors = params.hideErrors;
                    rangeParams.hideWarning = params.hideWarning;
                    rangeParams.min = params.min;
                    rangeParams.max = params.max;
                }

                return new RangeQuestion(rangeOptions, rangeParams);

            case QuestionManager.TYPE_CHECKBOX:
                const checkboxOptions = new QuestionBaseOptions();

                if (options) {
                    checkboxOptions.value = options.value;
                    checkboxOptions.values = options.values;
                    checkboxOptions.key = options.key;
                    checkboxOptions.label = options.label;
                    checkboxOptions.placeholder = options.placeholder;
                    checkboxOptions.validators = options.validators;
                }

                const checkboxParams = new QuestionBaseParams();

                if (params) {
                    checkboxParams.questionClass = params.questionClass;
                    checkboxParams.hideErrors = params.hideErrors;
                    checkboxParams.hideWarning = params.hideWarning;
                }

                return new CheckboxQuestion(checkboxOptions, checkboxParams);

            case QuestionManager.TYPE_GOOGLEMAP_LOCATION:
                const googleMapLocationOptions = new QuestionBaseOptions();

                if (options) {
                    googleMapLocationOptions.value = options.value;
                    googleMapLocationOptions.values = options.values;
                    googleMapLocationOptions.key = options.key;
                    googleMapLocationOptions.label = options.label;
                    googleMapLocationOptions.placeholder = options.placeholder;
                    googleMapLocationOptions.validators = options.validators;
                }

                const googleMapLocationParams = new QuestionRangeParams();

                if (params) {
                    googleMapLocationParams.questionClass = params.questionClass;
                    googleMapLocationParams.hideErrors = params.hideErrors;
                    googleMapLocationParams.hideWarning = params.hideWarning;
                }

                const locationQuestion:GoogleMapLocationQuestion = new 
                    GoogleMapLocationQuestion(googleMapLocationOptions, googleMapLocationParams);

                locationQuestion.setModal(this.modalController);

                return locationQuestion;

            case QuestionManager.TYPE_EXTENDED_GOOGLEMAP_LOCATION:
                const extendedGoogleMapLocationOptions = new QuestionBaseOptions();

                if (options) {
                    extendedGoogleMapLocationOptions.value = options.value;
                    extendedGoogleMapLocationOptions.values = options.values;
                    extendedGoogleMapLocationOptions.key = options.key;
                    extendedGoogleMapLocationOptions.label = options.label;
                    extendedGoogleMapLocationOptions.placeholder = options.placeholder;
                    extendedGoogleMapLocationOptions.validators = options.validators;
                }

                const extendedGoogleMapLocationParams = new QuestionExtendedGoogleMapLocationParams();

                if (params) {
                    extendedGoogleMapLocationParams.questionClass = params.questionClass;
                    extendedGoogleMapLocationParams.hideErrors = params.hideErrors;
                    extendedGoogleMapLocationParams.hideWarning = params.hideWarning;
                    extendedGoogleMapLocationParams.min = params.min;
                    extendedGoogleMapLocationParams.max = params.max;
                    extendedGoogleMapLocationParams.step = params.step;
                    extendedGoogleMapLocationParams.unit = params.unit;
                }

                const extendedLocationQuestion:ExtendedGoogleMapLocationQuestion =
                    new ExtendedGoogleMapLocationQuestion(extendedGoogleMapLocationOptions, extendedGoogleMapLocationParams);

                extendedLocationQuestion.setModal(this.modalController);

                return extendedLocationQuestion;

            case QuestionManager.TYPE_DATE_RANGE:
                const dateRangeOptions = new QuestionDateRangeOptions();

                if (options) {
                    dateRangeOptions.value = options.value;
                    dateRangeOptions.values = options.values;
                    dateRangeOptions.key = options.key;
                    dateRangeOptions.label = options.label;
                    dateRangeOptions.placeholder = options.placeholder;
                    dateRangeOptions.validators = options.validators;
                }

                const dateRangeParams = new QuestionDateParams();

                if (params) {
                    dateRangeParams.questionClass = params.questionClass;
                    dateRangeParams.hideErrors = params.hideErrors;
                    dateRangeParams.hideWarning = params.hideWarning;
                    dateRangeParams.minDate = params.minDate;
                    dateRangeParams.maxDate = params.maxDate;
                    dateRangeParams.displayFormat = params.displayFormat;
                }

                return new DateRangeQuestion(dateRangeOptions, dateRangeParams);

            case QuestionManager.TYPE_DATE:
            case QuestionManager.TYPE_AGE:
            case QuestionManager.TYPE_BIRTHDATE:
                const dateOptions = new QuestionBaseOptions();

                if (options) {
                    dateOptions.value = options.value;
                    dateOptions.values = options.values;
                    dateOptions.key = options.key;
                    dateOptions.label = options.label;
                    dateOptions.placeholder = options.placeholder;
                    dateOptions.validators = options.validators;
                }

                const dateParams = new QuestionDateParams();

                if (params) {
                    dateParams.questionClass = params.questionClass;
                    dateParams.hideErrors = params.hideErrors;
                    dateParams.hideWarning = params.hideWarning;
                    dateParams.minDate = params.minDate;
                    dateParams.maxDate = params.maxDate;
                    dateParams.displayFormat = params.displayFormat;
                }

                return new DateQuestion(dateOptions, dateParams);

            default:
                throw new TypeError(`Unsupported type ${type}`);
        }
    }
}
