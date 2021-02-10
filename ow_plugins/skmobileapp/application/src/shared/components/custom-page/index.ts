import { Component, ChangeDetectionStrategy } from '@angular/core';
import { ViewController, NavParams } from 'ionic-angular';

@Component({
    selector: 'custom-page',
    templateUrl: 'index.html',
    changeDetection: ChangeDetectionStrategy.OnPush
})

export class CustomPageComponent {
    title: string;
    pageName: string;

    /**
     * Constructor
     */
    constructor(
        private view: ViewController,
        private navParams: NavParams)
    {
        this.title = this.navParams.get('title');
        this.pageName = this.navParams.get('pageName');
    }

    /**
     * Close
     */
    close(): void {
        this.view.dismiss();
    }
}
