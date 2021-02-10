import { GoogleMapLocationQuestion } from './googlemap.location';
import { QuestionBaseOptions, QuestionBaseParams } from './base';

export class QuestionExtendedGoogleMapLocationParams extends QuestionBaseParams {
    questionClass: string;
    hideErrors: boolean;
    hideWarning: boolean;
    min: number;
    max: number;
    step: number;
    unit: string;
};

export class ExtendedGoogleMapLocationValue {
    location: string;
    distance: number
};

export class ExtendedGoogleMapLocationQuestion extends GoogleMapLocationQuestion {
    controlType: string = 'extended_googlemap_location';
    value: ExtendedGoogleMapLocationValue;
    min: number = 5;
    max: number = 100;
    step: number = 10;
    unit: string = 'miles';

    /**
     * Constructor
     */
    constructor(options: QuestionBaseOptions, params?: QuestionExtendedGoogleMapLocationParams) {
        super(options, params);

        // init extra prams
        if (params) {
            params.min
                ? this.min = params.min
                : null;

            params.max
                ? this.max = params.max
                : null;

            params.step
                ? this.step = params.step
                : null;

            params.unit
                ? this.unit = params.unit
                : null;
        }

        // initial value
        this.value = {
            location: options.value && options.value.location 
                ? options.value.location 
                : null,
            distance: options.value && options.value.distance 
                ? options.value.distance 
                : null
        };
    }

    /**
     * Update distance
     */
    updateDistance(distance): void {
        this.setControlValue({
            location: this.controlView.value.location,
            distance: distance.value
        });
    }

    /**
     * Set location
     */
    protected setLocation(location: string): void {
        this.setControlValue({
            location: location,
            distance: this.controlView.value.distance
        });
    }

    /**
     * Get location
     */
    protected getLocation(): string {
        return this.controlView.value.location;
    }
}
