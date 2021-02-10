import { Component, ChangeDetectionStrategy } from '@angular/core';
import { ViewController, NavParams } from 'ionic-angular';

@Component({
    selector: 'view-video',
    templateUrl: './index.html',
    changeDetection: ChangeDetectionStrategy.OnPush
})
export class ViewVideoComponent {
    protected coverImageUrl: string = '';
    protected videoUrls: string[] = [];

    /**
     * Constructor
     */
    constructor(
        private navParams: NavParams,
        private viewCtrl: ViewController)
    {
        this.videoUrls = this.navParams.get('videoUrls');
        this.coverImageUrl = this.navParams.get('coverImageUrl');
    }

    /**
     * Cancel
     */
    cancel() {
        this.viewCtrl.dismiss(null);
    }
}
