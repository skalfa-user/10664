import { Component, Input, ChangeDetectionStrategy } from '@angular/core';

@Component({
    selector: 'location',
    templateUrl: 'index.html',
    changeDetection: ChangeDetectionStrategy.OnPush
})

export class LocationComponent {
    @Input() distance: number;
    @Input() unit: string;
}
