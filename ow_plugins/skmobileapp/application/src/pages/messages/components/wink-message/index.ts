import { Component, Input, ChangeDetectionStrategy } from '@angular/core';

// services
import { IMessage } from 'services/messages';

@Component({
    selector: 'wink-message',
    templateUrl: 'index.html',
    changeDetection: ChangeDetectionStrategy.OnPush
})

export class WinkMessageComponent {
    @Input() message: IMessage;
    @Input() prevMessage: IMessage;
}
