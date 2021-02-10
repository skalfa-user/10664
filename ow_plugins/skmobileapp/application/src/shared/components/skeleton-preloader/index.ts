import { Component, ChangeDetectionStrategy, Input } from '@angular/core';

@Component({
    selector: 'skeleton-preloader',
    templateUrl: 'index.html',
    changeDetection: ChangeDetectionStrategy.OnPush
})

export class SkeletonPreloaderComponent {
    @Input() name: string = null;
    @Input() repeatTimes: number | string = 1;

    /**
     * Create range
     */
    createRange(length: string): Array<any> {
        return new Array(parseInt(length));
    }
}
