import { Component, OnInit, OnDestroy, ChangeDetectionStrategy } from '@angular/core';
import { ViewController, AlertController, Alert, NavParams } from 'ionic-angular';
import { TranslateService } from "ng2-translate";

// services
import { UserService, IUserWithAvatar } from 'services/user';
import { VideoImService } from 'services/video-im';

@Component({
    selector: 'video-im-confirmation',
    templateUrl: 'index.html',
    changeDetection: ChangeDetectionStrategy.OnPush
})

export class VideoImConfirmationComponent implements OnInit, OnDestroy {
    callerId: number;
    sessionId: string;
    callerData: IUserWithAvatar;
    audio: any;
    
    /**
     * Constructor
     */
    constructor(
        private view: ViewController,
        private alert: AlertController,
        private user: UserService,
        private videoIm: VideoImService,
        private translate: TranslateService,
        private navParams: NavParams
    )
    {
        this.callerId = this.navParams.get('userId');
        this.sessionId = this.navParams.get('sessionId');
    }

    /**
     * Component init
     */
    ngOnInit(): void {
        this.videoIm.setSessionId(this.callerId, this.sessionId);
        this.callerData = this.user.getUserWithAvatar(this.callerId);

        this.audio = new Audio();
        this.audio.src = './assets/sound/ring.mp3';
        this.audio.loop = true;
        this.audio.play();
    }

    /**
     * Component destroy
     */
    ngOnDestroy(): void {
        if (this.audio) {
            this.audio.pause();
            this.audio = null;
        }
    }

    /**
     * Accept
     */
    accept(): void {
        this.videoIm.setActiveInterlocutorData(this.callerId, false);

        this.view.dismiss();
    }

    /**
     * Decline
     */
    decline(): void {
        this.videoIm.markNotifications(this.callerId);
        this.videoIm.sendNotification(this.callerId, {
            type: 'declined'
        }).subscribe();
        this.videoIm.removeSessionId(this.callerId);

        this.view.dismiss();
    }

    /**
     * Block
     */
    block(): void {
        const blockConfirmation: Alert = this.alert.create({
            subTitle: this.translate.instant('vim_block_user_confirmation'),
            buttons: [{
                role: 'ok',
                text: this.translate.instant('ok'),
                handler: () => {
                    this.user.blockUser(this.callerId);
                    this.videoIm.markNotifications(this.callerId);
                    this.videoIm.sendNotification(this.callerId, {
                        type: 'declined'
                    }).subscribe();
                    this.videoIm.removeSessionId(this.callerId);

                    this.view.dismiss();
                }
            },{
                role: 'cancel',
                text: this.translate.instant('cancel'),
            }],
            cssClass: 'sk-videoim-alert'
        });

        blockConfirmation.present();
    }
}
