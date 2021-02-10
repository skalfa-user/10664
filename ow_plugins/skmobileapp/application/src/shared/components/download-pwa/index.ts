import { Component, ChangeDetectionStrategy } from '@angular/core';
import { Platform, ViewController } from 'ionic-angular';

@Component({
    selector: 'download-pwa',
    templateUrl: 'index.html',
    changeDetection: ChangeDetectionStrategy.OnPush
})

export class DownloadPwaComponent  {
    /**
     * Constructor
     */
    constructor(
        private platform: Platform, 
        private view: ViewController) {}

    /**
     * Is IOS
     */
    get isIos(): boolean {
        return this.platform.is('ios');
    }

    /**
     * Return back
     */
    returnBack(): void {
        this.view.dismiss();
    }
}
