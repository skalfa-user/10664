import {ChangeDetectionStrategy, Component} from '@angular/core';

// property prefix
export const PROPERTY_PREFIX = '--';

@Component({
    selector: 'sass-helper',
    templateUrl: 'index.html',
    changeDetection: ChangeDetectionStrategy.OnPush
})
export class SassHelperComponent {

    constructor() {}

    /**
     * Read the custom property of body section by name
     *
     * @param name
     */
    readProperty(name: string): string {
        let bodyStyles = window.getComputedStyle(document.body);
        return bodyStyles.getPropertyValue(PROPERTY_PREFIX + name);
    }
}
