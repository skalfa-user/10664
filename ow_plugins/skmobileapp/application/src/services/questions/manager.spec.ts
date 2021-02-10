
import { ModalController } from 'ionic-angular';

import { QuestionManager } from './manager';
import { QuestionBase, QuestionBaseValidator, QuestionBaseValues } from './questions/base';
import { TextQuestion } from './questions/text';
import { SelectQuestion } from './questions/select';
import { TextareaQuestion } from './questions/textarea';
import { RangeQuestion, RangeValue } from './questions/range';
import { CheckboxQuestion } from './questions/checkbox';
import { GoogleMapLocationQuestion } from './questions/googlemap.location';
import { ExtendedGoogleMapLocationQuestion, ExtendedGoogleMapLocationValue } from './questions/extended.googlemap.location';
import { DateRangeQuestion, DateRangeValue } from './questions/date.range';
import { DateQuestion } from './questions/date';

// fakes
import { ModalControllerMock } from 'ionic-mocks';

describe('Question manager', () => {
    // init service's fakes
    let fakeModalController: ModalController;
    let questionManager: QuestionManager; // testable service

    let questionLabel: string;
    let questionValue: string;
    let questionKey: string;
    let questionValidators: Array<QuestionBaseValidator>;
    let questionValues: Array<QuestionBaseValues>;

    beforeEach(() => {
        // init service's fakes
        fakeModalController = ModalControllerMock.instance();

        // init question manager service
        questionManager = new QuestionManager(fakeModalController);

        questionValue = 'testValue';
        questionKey = 'testKey';
        questionLabel = 'testLabel';
        questionValidators = [{
            name: 'testValidator', 
            message: '', 
            params: {}
        }];

        questionValues = [
            {
                value: 'testValue',
                title: 'testTitle'
            }
        ];
    });

    it('getQuestion should create a location question', () => {
        const questionType = QuestionManager.TYPE_LOCATION;
        const questionOptions = {
            value: questionValue,
            key: questionKey,
            label: questionLabel,
            validators: questionValidators
        };

        const question: QuestionBase = questionManager.getQuestion(questionType, questionOptions);

        expect(question).toEqual(jasmine.any(TextQuestion));
        expect(question.controlType).toEqual('text');
        expect(question.value).toEqual(questionValue);
        expect(question.key).toEqual(questionKey);
        expect(question.label).toEqual(questionLabel);
        expect(question.validators).toEqual(questionValidators);
    });

    it('getQuestion should create a url question', () => {
        const questionType = QuestionManager.TYPE_URL;
        const questionOptions = {
            value: questionValue,
            key: questionKey,
            label: questionLabel,
            validators: questionValidators
        };

        const question: QuestionBase = questionManager.getQuestion(questionType, questionOptions);

        expect(question).toEqual(jasmine.any(TextQuestion));
        expect(question.controlType).toEqual('text');
        expect(question.value).toEqual(questionValue);
        expect(question.key).toEqual(questionKey);
        expect(question.label).toEqual(questionLabel);
        expect(question.validators).toEqual(questionValidators);
    });

    it('getQuestion should create a text question', () => {
        const questionType = QuestionManager.TYPE_TEXT;
        const questionOptions = {
            value: questionValue,
            key: questionKey,
            label: questionLabel,
            validators: questionValidators
        };

        const question: QuestionBase = questionManager.getQuestion(questionType, questionOptions);

        expect(question).toEqual(jasmine.any(TextQuestion));
        expect(question.controlType).toEqual('text');
        expect(question.value).toEqual(questionValue);
        expect(question.key).toEqual(questionKey);
        expect(question.label).toEqual(questionLabel);
        expect(question.validators).toEqual(questionValidators);
    });

    it('getQuestion should create a email question', () => {
        const questionType = QuestionManager.TYPE_EMAIL;
        const questionOptions = {
            value: questionValue,
            key: questionKey,
            label: questionLabel,
            validators: questionValidators
        };

        const question: QuestionBase = questionManager.getQuestion(questionType, questionOptions);

        expect(question).toEqual(jasmine.any(TextQuestion));
        expect(question.controlType).toEqual('text');
        expect(question.value).toEqual(questionValue);
        expect(question.key).toEqual(questionKey);
        expect(question.label).toEqual(questionLabel);
        expect(question.validators).toEqual(questionValidators);
    });

    it('getQuestion should create a password question', () => { 
        const questionType = QuestionManager.TYPE_PASSWORD;
        const questionOptions = {
            value: questionValue,
            key: questionKey,
            label: questionLabel,
            validators: questionValidators
        };

        const question: QuestionBase = questionManager.getQuestion(questionType, questionOptions);

        expect(question).toEqual(jasmine.any(TextQuestion));
        expect(question.controlType).toEqual('text');
        expect(question.value).toEqual(questionValue);
        expect(question.key).toEqual(questionKey);
        expect(question.label).toEqual(questionLabel);
        expect(question.validators).toEqual(questionValidators);
    });

    it('getQuestion should create a radio question', () => { 
        const questionType = QuestionManager.TYPE_RADIO;
        const questionOptions = {
            value: questionValue,
            values: questionValues,
            key: questionKey,
            label: questionLabel,
            validators: questionValidators
        };

        const question: QuestionBase = questionManager.getQuestion(questionType, questionOptions);

        expect(question).toEqual(jasmine.any(SelectQuestion));
        expect(question.controlType).toEqual('select');
        expect(question.value).toEqual(questionValue);
        expect(question.values).toEqual(questionValues);
        expect(question.key).toEqual(questionKey);
        expect(question.label).toEqual(questionLabel);
        expect(question.validators).toEqual(questionValidators);
    });

    it('getQuestion should create a select question', () => {
        const questionType = QuestionManager.TYPE_SELECT;
        const questionOptions = {
            value: questionValue,
            values: questionValues,
            key: questionKey,
            label: questionLabel,
            validators: questionValidators
        };

        const question: QuestionBase = questionManager.getQuestion(questionType, questionOptions);

        expect(question).toEqual(jasmine.any(SelectQuestion));
        expect(question.controlType).toEqual('select');
        expect(question.value).toEqual(questionValue);
        expect(question.values).toEqual(questionValues);
        expect(question.key).toEqual(questionKey);
        expect(question.label).toEqual(questionLabel);
        expect(question.validators).toEqual(questionValidators);
    });

    it('getQuestion should create a fselect question', () => {
        const questionType = QuestionManager.TYPE_FSELECT;
        const questionOptions = {
            value: questionValue,
            values: questionValues,
            key: questionKey,
            label: questionLabel,
            validators: questionValidators
        };

        const question: QuestionBase = questionManager.getQuestion(questionType, questionOptions);

        expect(question).toEqual(jasmine.any(SelectQuestion));
        expect(question.controlType).toEqual('select');
        expect(question.value).toEqual(questionValue);
        expect(question.values).toEqual(questionValues);
        expect(question.key).toEqual(questionKey);
        expect(question.label).toEqual(questionLabel);
        expect(question.validators).toEqual(questionValidators);
    });

    it('getQuestion should create a multi select question', () => {
        const questionType = QuestionManager.TYPE_MULTISELECT;
        const questionOptions = {
            value: questionValue,
            values: questionValues,
            key: questionKey,
            label: questionLabel,
            validators: questionValidators
        };

        const question: QuestionBase = questionManager.getQuestion(questionType, questionOptions);

        expect(question).toEqual(jasmine.any(SelectQuestion));
        expect(question.controlType).toEqual('select');
        expect(question.value).toEqual(questionValue);
        expect(question.values).toEqual(questionValues);
        expect(question.key).toEqual(questionKey);
        expect(question.label).toEqual(questionLabel);
        expect(question.validators).toEqual(questionValidators);
    });

    it('getQuestion should create a multi checkbox question', () => {
        const questionType = QuestionManager.TYPE_MULTICHECKBOX;
        const questionOptions = {
            value: questionValue,
            values: questionValues,
            key: questionKey,
            label: questionLabel,
            validators: questionValidators
        };

        const question: QuestionBase = questionManager.getQuestion(questionType, questionOptions);

        expect(question).toEqual(jasmine.any(SelectQuestion));
        expect(question.controlType).toEqual('select');
        expect(question.value).toEqual(questionValue);
        expect(question.values).toEqual(questionValues);
        expect(question.key).toEqual(questionKey);
        expect(question.label).toEqual(questionLabel);
        expect(question.validators).toEqual(questionValidators);
    });

    it('getQuestion should create a textarea question', () => { 
        const questionType = QuestionManager.TYPE_TEXTAREA;
        const questionOptions = {
            value: questionValue,
            key: questionKey,
            label: questionLabel,
            validators: questionValidators
        };

        const question: QuestionBase = questionManager.getQuestion(questionType, questionOptions);

        expect(question).toEqual(jasmine.any(TextareaQuestion));
        expect(question.controlType).toEqual('textarea');
        expect(question.value).toEqual(questionValue);
        expect(question.key).toEqual(questionKey);
        expect(question.label).toEqual(questionLabel);
        expect(question.validators).toEqual(questionValidators);
    });

    it('getQuestion should create a range question', () => {
        const value: RangeValue = {
            lower: 1,
            upper: 100
        };

        const questionType = QuestionManager.TYPE_RANGE;
        const questionOptions = {
            value: value,
            key: questionKey,
            label: questionLabel,
            validators: questionValidators
        };

        const question: QuestionBase = questionManager.getQuestion(questionType, questionOptions);

        expect(question).toEqual(jasmine.any(RangeQuestion));
        expect(question.controlType).toEqual('range');
        expect(question.value).toEqual(value);
        expect(question.key).toEqual(questionKey);
        expect(question.label).toEqual(questionLabel);
        expect(question.validators).toEqual(questionValidators);
    });

    it('getQuestion should create a checkbox question', () => {
        const value: boolean = true;
        const questionType = QuestionManager.TYPE_CHECKBOX;
        const questionOptions = {
            value: value,
            key: questionKey,
            label: questionLabel,
            validators: questionValidators
        };

        const question: QuestionBase = questionManager.getQuestion(questionType, questionOptions);

        expect(question).toEqual(jasmine.any(CheckboxQuestion));
        expect(question.controlType).toEqual('checkbox');
        expect(question.value).toEqual(value);
        expect(question.key).toEqual(questionKey);
        expect(question.label).toEqual(questionLabel);
        expect(question.validators).toEqual(questionValidators);
    });

    it('getQuestion should create a googlemap location question', () => { 
        const questionType = QuestionManager.TYPE_GOOGLEMAP_LOCATION;
        const questionOptions = {
            value: questionValue,
            key: questionKey,
            label: questionLabel,
            validators: questionValidators
        };

        const question: QuestionBase = questionManager.getQuestion(questionType, questionOptions);

        expect(question).toEqual(jasmine.any(GoogleMapLocationQuestion));
        expect(question.controlType).toEqual('googlemap_location');
        expect(question.value).toEqual(questionValue);
        expect(question.key).toEqual(questionKey);
        expect(question.label).toEqual(questionLabel);
        expect(question.validators).toEqual(questionValidators);
    });

    it('getQuestion should create an extended googlemap location question', () => { 
        const value: ExtendedGoogleMapLocationValue = {
            location: 'test',
            distance: 50
        };
 
        const questionType = QuestionManager.TYPE_EXTENDED_GOOGLEMAP_LOCATION;
        const questionOptions = {
            value: value,
            key: questionKey,
            label: questionLabel,
            validators: questionValidators
        };

        const question: QuestionBase = questionManager.getQuestion(questionType, questionOptions);

        expect(question).toEqual(jasmine.any(ExtendedGoogleMapLocationQuestion));
        expect(question.controlType).toEqual('extended_googlemap_location');
        expect(question.value).toEqual(value);
        expect(question.key).toEqual(questionKey);
        expect(question.label).toEqual(questionLabel);
        expect(question.validators).toEqual(questionValidators);
    });

    it('getQuestion should create a date range question', () => { 
        const value: DateRangeValue = {
            start: '05.06.2018',
            end: '05.06.2018'
        };
 
        const questionType = QuestionManager.TYPE_DATE_RANGE;
        const questionOptions = {
            value: value,
            key: questionKey,
            label: questionLabel,
            validators: questionValidators
        };

        const question: QuestionBase = questionManager.getQuestion(questionType, questionOptions);

        expect(question).toEqual(jasmine.any(DateRangeQuestion));
        expect(question.controlType).toEqual('date_range');
        expect(question.value).toEqual(value);
        expect(question.key).toEqual(questionKey);
        expect(question.label).toEqual(questionLabel);
        expect(question.validators).toEqual(questionValidators);
    });

    it('getQuestion should create a date question', () => { 
        const value: string = '05.06.2018';
 
        const questionType = QuestionManager.TYPE_DATE;
        const questionOptions = {
            value: value,
            key: questionKey,
            label: questionLabel,
            validators: questionValidators
        };

        const question: QuestionBase = questionManager.getQuestion(questionType, questionOptions);

        expect(question).toEqual(jasmine.any(DateQuestion));
        expect(question.controlType).toEqual('date');
        expect(question.value).toEqual(value);
        expect(question.key).toEqual(questionKey);
        expect(question.label).toEqual(questionLabel);
        expect(question.validators).toEqual(questionValidators);
    });

    it('getQuestion should create an age question', () => { 
        const value: string = '05.06.2018';
 
        const questionType = QuestionManager.TYPE_AGE;
        const questionOptions = {
            value: value,
            key: questionKey,
            label: questionLabel,
            validators: questionValidators
        };

        const question: QuestionBase = questionManager.getQuestion(questionType, questionOptions);

        expect(question).toEqual(jasmine.any(DateQuestion));
        expect(question.controlType).toEqual('date');
        expect(question.value).toEqual(value);
        expect(question.key).toEqual(questionKey);
        expect(question.label).toEqual(questionLabel);
        expect(question.validators).toEqual(questionValidators);
    });

    it('getQuestion should create a birth date question', () => { 
        const value: string = '05.06.2018';
 
        const questionType = QuestionManager.TYPE_BIRTHDATE;
        const questionOptions = {
            value: value,
            key: questionKey,
            label: questionLabel,
            validators: questionValidators
        };

        const question: QuestionBase = questionManager.getQuestion(questionType, questionOptions);

        expect(question).toEqual(jasmine.any(DateQuestion));
        expect(question.controlType).toEqual('date');
        expect(question.value).toEqual(value);
        expect(question.key).toEqual(questionKey);
        expect(question.label).toEqual(questionLabel);
        expect(question.validators).toEqual(questionValidators);
    });

    it('getQuestion should trigger an error if question type is unknown', () => {
        const questionType: string = 'unknown';

        expect(() => questionManager
            .getQuestion(questionType))
            .toThrow(new TypeError(`Unsupported type ${questionType}`));
    });
});

