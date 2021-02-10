import { Component, Input, ChangeDetectionStrategy, ChangeDetectorRef } from '@angular/core';

// services
import { SiteConfigsService } from 'services/site-configs';

@Component({
    selector: 'user-avatar',
    templateUrl: 'index.html',
    changeDetection: ChangeDetectionStrategy.OnPush
})

export class AvatarComponent {
    @Input() url: string;
    @Input() set isAvatarActive (value: any) {
        this.isAvatarStateActive = value === 'true' ||  value === true;
    }

    @Input() set isUseBigAvatar (value: any) {
        this.isBigAvatar = value === 'true' ||  value === true;
    }

    isAvatarStateActive: boolean = true;
    isBigAvatar: boolean = false;
    isAvatarBroken: boolean = false;

    /**
     * Constructor
     */
    constructor(
        private siteConfigs: SiteConfigsService,
        private ref: ChangeDetectorRef) {}

    /**
     * Get default avatar
     */
    get defaultAvatar(): string {
        return this.siteConfigs.getConfig('defaultAvatar');
    }

    /**
     * Get big default avatar
     */
    get bigDefaultAvatar(): string {
        return this.siteConfigs.getConfig('bigDefaultAvatar');
    }

    /**
     * Mark avatar as broken
     */
    markAvatarAsBroken(): void {
        this.isAvatarBroken = true;
        this.ref.markForCheck();
    }
}
