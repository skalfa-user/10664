import { Component, Input, ChangeDetectionStrategy } from '@angular/core';

// services
import { IMessage } from 'services/messages';

@Component({
    selector: 'oembed-message',
    templateUrl: 'index.html',
    changeDetection: ChangeDetectionStrategy.OnPush
})

export class OembedMessageComponent {
    @Input() message: IMessage;
    @Input() prevMessage: IMessage;

    /**
     * Get oembed message
     */
    get getOembedMessage(): string {
        const message: any = JSON.parse(this.message.text);

        if (message.params.message) {
            return message.params.message;
        }
    }
}
