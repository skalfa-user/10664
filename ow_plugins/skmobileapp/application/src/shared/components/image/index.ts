import { Component, Input, ChangeDetectionStrategy, ChangeDetectorRef } from '@angular/core';

@Component({
    selector: 'user-image',
    templateUrl: 'index.html',
    changeDetection: ChangeDetectionStrategy.OnPush
})

export class ImageComponent {
    @Input() url: string;
    @Input() cssClass: string = '';

    isImageBroken: boolean = false;

    /**
     * Constructor
     */
    constructor(private ref: ChangeDetectorRef) {}

    /**
     * Mark image as broken
     */
    markImageAsBroken(): void {
        this.isImageBroken = true;
        this.ref.markForCheck();
    }
}
