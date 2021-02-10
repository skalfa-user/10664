import { QuestionBase, QuestionBaseOptions, QuestionBaseParams } from './base';
import { ModalController } from 'ionic-angular';

// shared
import { LocationAutocompleteComponent } from 'shared/components/location-autocomplete';

export class GoogleMapLocationQuestion extends QuestionBase {
    controlType = 'googlemap_location';
    
    protected modalController: ModalController;

    /**
     * Constructor
     */
    constructor(options: QuestionBaseOptions, params?: QuestionBaseParams) {
        super(options, params);

        // initial value
        if (!this.value) {
            this.value = '';
        }
    }

    /**
     * Set modal
     */
    setModal(modalController: ModalController): void {
        this.modalController = modalController;
    }

    /**
     * Show address modal
     */
    showAddressModal(isQuestionRequired: boolean): void {
        const modal = this.modalController.create(LocationAutocompleteComponent, {
            q: this.getLocation(),
            isQuestionRequired: isQuestionRequired
        });

        modal.onDidDismiss((location: string) => {
            if (location !== null) {
                this.setLocation(location);
            }
        });

        modal.present();
    }

    /**
     * Set location
     */
    protected setLocation(location: string): void {
        this.setControlValue(location);
    }

    /**
     * Get location
     */
    protected getLocation(): string {
        return this.controlView.value;
    }
}
