import { Component, ChangeDetectionStrategy, ViewChild } from '@angular/core';
import { ViewController, NavParams, Slides } from 'ionic-angular';

// services
import { ApplicationService } from 'services/application';

@Component({
    selector: 'photos-viewer',
    templateUrl: 'index.html',
    changeDetection: ChangeDetectionStrategy.OnPush
})

export class PhotosViewerComponent {
    @ViewChild('photosViewerSlider') slider: Slides = null;

    activeIndex: number = 0;
    urls: Array<string> = [];
    isFlagActive: boolean = false;

    private onPhotoViewedCallback: (url: string) => {};
    private onPhotoFlaggedCallback: (url: string) => {};

    /**
     * Constructor
     */
    constructor(
        public application: ApplicationService,
        private view: ViewController, 
        private navParams: NavParams) 
    {
        this.urls = this.navParams.get('urls');

        if (this.navParams.get('activeIndex')) {
            this.activeIndex = this.navParams.get('activeIndex');
        }

        if (this.navParams.get('onPhotoViewedCallback')) {
            this.onPhotoViewedCallback = this.navParams.get('onPhotoViewedCallback');
        }

        if (this.navParams.get('isFlagActive')) {
            this.isFlagActive = this.navParams.get('isFlagActive');
        }

        if (this.navParams.get('onPhotoFlaggedCallback')) {
            this.onPhotoFlaggedCallback = this.navParams.get('onPhotoFlaggedCallback');
        }
    }

    /**
     * Flag photo
     */
    flagPhoto(): void {
        this.onPhotoFlaggedCallback(this.urls[this.slider.getActiveIndex()]);
    }

    /**
     * Photos slider did change
     */
    photosSliderDidChange(): void {
        if (this.onPhotoViewedCallback && this.urls[this.slider.getActiveIndex()]) {
            this.onPhotoViewedCallback(this.urls[this.slider.getActiveIndex()]);
        }
    }

    /**
     * Cancel
     */
    cancel() {
        this.view.dismiss();
    }
}
