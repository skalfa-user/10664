import { Component, Input, ChangeDetectionStrategy, ChangeDetectorRef, OnInit, OnDestroy, AfterViewInit } from '@angular/core';
import { AlertController, Events } from 'ionic-angular';
import { FormGroup, AbstractControl } from '@angular/forms';
import { QuestionBase } from 'services/questions/questions/base';
import { sprintf } from 'sprintf-js';
import { TranslateService } from 'ng2-translate';

// validators
import { Validators } from 'services/questions/validators';
import { RequireValidator } from 'services/questions/validators/require';

@Component({
    selector: 'question',
    templateUrl: 'index.html',
    changeDetection: ChangeDetectionStrategy.OnPush
})

export class QuestionComponent implements OnInit, AfterViewInit, OnDestroy {
    @Input() question: QuestionBase;
    @Input() form: FormGroup;

    private static readonly REQUIRE_VALIDATOR_NAME: string = 'require';

    private baseQuestionClass: string = 'sk-base-question-presentation';
    private baseQuestionWarningClass: string = 'sk-question-warning';
    private asyncValidatorFinishedHandler: () => void;
    private isQuestionRequired: boolean = false;

    constructor(
        private events: Events,
        private ref: ChangeDetectorRef,
        private validators: Validators,
        private requireValidator: RequireValidator,
        private alert: AlertController,
        private translate: TranslateService)
    {
        // -- init callbacks --//

        // async validator finished validation process handler
        this.asyncValidatorFinishedHandler = (): void => {
            this.ref.markForCheck();
        };
    }

    /**
     * Component init
     */
    ngOnInit() {
        // an async validator is finished
        this.events.subscribe('asyncValidator:finished', this.asyncValidatorFinishedHandler);

        this.form.valueChanges.subscribe(() => {
            this.ref.markForCheck();
        });

        // check if the question is required
        if (this.question.validators && this.question.validators.length) {
            this.question.validators.forEach((validator: any) => {
                if (validator.name == QuestionComponent.REQUIRE_VALIDATOR_NAME) {
                    this.isQuestionRequired = true;
                }
            });
        }
    }

    /**
     * Component fully loaded
     */
    ngAfterViewInit(): void {
        setTimeout(() => this.ref.markForCheck());
    }

    /**
     * Component destroy
     */
    ngOnDestroy(): void {
        this.events.unsubscribe('asyncValidator:finished', this.asyncValidatorFinishedHandler);
    }

    /**
     * Is question valid
     */
    get isValid(): boolean {
        const control: AbstractControl = this.form.controls[this.question.key];

        if ((!control.valid && control.dirty && !control.pending)
            || (this.hasGroupError() && control.dirty && !control.pending)) {

            return false;
        }

        return true;
    }

    /**
     * Get question class
     */
    get getQuestionClass(): string {
        const params: any = this.question.params;
        const control: AbstractControl = this.form.controls[this.question.key];
        const hideWarning: boolean = params && params.hideWarning && params.hideWarning == true;

        const warning = !hideWarning && this.isQuestionRequired && !this.requireValidator.isValid(control.value)
            ? this.baseQuestionWarningClass
            : '';

        if (params && params.questionClass) {
            return `${this.baseQuestionClass} ${warning} ${params.questionClass}`;
        }

        return `${this.baseQuestionClass} ${warning}`;
    }

    /**
     * Show errors
     */
    showErrors(event): void {
        event.stopPropagation();

        let errors: string = '';

        this.getErrors().forEach((error) => {
            errors += `${error}<br />`;
        });

        const alert = this.alert.create({
            subTitle: errors,
            buttons: [this.translate.instant('ok')]
        });

        alert.present();
    }

    /**
     * Is error
     */
    get isError(): boolean {
        const params: any = this.question.params;
        const hideErrors = params && params.hideErrors && params.hideErrors == true;

        return !hideErrors && !this.isValid;
    }

    /**
     * Is pending
     */
    get isPending(): boolean {
        const control: AbstractControl = this.form.controls[this.question.key];

        return control.pending;
    }

    /**
     * Get list of errors
     */
    protected getErrors(): Array<string> {
        const control: AbstractControl = this.form.controls[this.question.key];
        const errors: Array<string> = [];

        // check all assigned question's validators
        this.question.validators.forEach((validator) => {
            if (control.hasError(validator.name)) {
                const message = !validator.message
                    ? this.validators.getDefaultMessage(validator.name)
                    : validator.message;

                errors.push(sprintf(message, control.value));
            }
        });

        // check a group error
        if (this.hasGroupError()) {
            const groupError: any = control.parent.errors;

            errors.push(sprintf(groupError.message, control.value));
        }

        return errors;
    }

    /**
     * Has group error
     */
    protected hasGroupError(): boolean {
        const control: AbstractControl = this.form.controls[this.question.key];

        if (control.parent) {
            const groupError: any = control.parent.errors;

            if (groupError
                && groupError.question
                && groupError.message
                && groupError.question == this.question.key) {

                return true;
            }
        }

        return false;
    }
}
