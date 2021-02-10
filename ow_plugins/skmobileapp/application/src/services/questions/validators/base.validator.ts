export class BaseValidatorParams {}

export interface IValidator {
    /**
     * Validate
     */
    validate(): Function;

    /**
     * Add params
     */
    addParams(params: BaseValidatorParams): void;
}

export abstract class BaseValidator implements IValidator {
    protected params: BaseValidatorParams;

    /**
     * Constructor
     */
    constructor() {
        this.params = {};
    }

    /**
     * Validate
     */
    abstract validate(): Function;

    /**
     * Add params
     */
    addParams(params: BaseValidatorParams): void {
        this.params = params;
    }
}
