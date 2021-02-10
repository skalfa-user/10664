import {Component, OnInit, OnDestroy, ChangeDetectorRef, ChangeDetectionStrategy, NgZone, Input} from '@angular/core';
import {Observable} from 'rxjs';
import {ISubscription} from 'rxjs/Subscription';
import {PermissionsService} from 'services/permissions';
import {SiteConfigsService} from 'services/site-configs';
import {IPermission} from 'store/states';

interface ITimer {
    runTimer: boolean;
    displayTime: string;
    seconds: number;
    secondsRemaining: number;
}

@Component({
    selector: 'video-im-timer',
    templateUrl: 'index.html',
    changeDetection: ChangeDetectionStrategy.OnPush
})
export class VideoImTimerComponent implements OnInit, OnDestroy {
    time: number = 0;
    timer: ITimer;

    @Input()
    isMeInitiator: boolean;

    private timerInterval = 1000; // one second
    private timerIntervalHandler = null;
    private isTrackCreditsAllowed = false;
    private cost: number = 0;
    private videoImPermissionSubscription: ISubscription;

    /**
     * Constructor
     */
    constructor(
        private zone: NgZone,
        private ref: ChangeDetectorRef,
        private permissions: PermissionsService,
        private siteConfigs: SiteConfigsService,
    ) {
    }

    /**
     * Component init
     */
    ngOnInit() {
        this.timer = <ITimer> {
            runTimer: false,
            seconds: this.time,
            secondsRemaining: this.time,
        };

        this.timer.displayTime = this.convertToDisplayTime(this.timer.secondsRemaining);

        const trackCreditsType: string = this.siteConfigs.getConfig('videoim_track_credits_type');

        if (trackCreditsType == 'both' ||
          (trackCreditsType == 'initiator' && this.isMeInitiator) ||
          (trackCreditsType == 'interlocutor' && !this.isMeInitiator)) {

            this.isTrackCreditsAllowed = true;
        }

        this.videoImPermissionSubscription = this.permissions.watchMe('videoim_video_im_timed_call')
          .subscribe((permission: IPermission) => {
              if (permission.creditsCost !== 0) {
                  this.cost = permission.creditsCost;
              }
          });
    }

     /**
     * Component init
     */
    ngOnDestroy() {
        if (this.timerIntervalHandler) {
            clearInterval(this.timerIntervalHandler);
        }

        this.videoImPermissionSubscription && this.videoImPermissionSubscription.unsubscribe();
    }

    /**
     * Display time getter
     */
    get displayTime(): string {
        return this.timer.displayTime;
    }

    /**
     * Time tick
     */
    timerTick(): void {
        this.zone.runOutsideAngular(() => {
            this.timerIntervalHandler = setInterval(() => {
                if (!this.timer.runTimer) {
                    return;
                }

                this.timer.secondsRemaining++;
                this.timer.displayTime = this.convertToDisplayTime(this.timer.secondsRemaining);

                this.ref.detectChanges();
            }, this.timerInterval);
        });
    }

    /**
     * Start timer
     */
    startTimer(): void  {
        this.timer.runTimer = true;

        this.timerTick();
    }

    /**
     * Stop timer
     */
    stopTimer(): void  {
        this.timer.runTimer = false;
    }

    /**
     * Convert to display time
     */
    private convertToDisplayTime(inputSeconds: number): string {
        const secNum = parseInt(inputSeconds.toString(), 10);

        const hours   = Math.floor(secNum / 3600);
        const minutes = Math.floor((secNum - (hours * 3600)) / 60);
        const seconds = secNum - (hours * 3600) - (minutes * 60);

        const hoursString = (hours < 10) ? '0' + hours : hours.toString();
        const minutesString = (minutes < 10) ? '0' + minutes : minutes.toString();
        const secondsString = (seconds < 10) ? '0' + seconds : seconds.toString();

        return hoursString + ':' + minutesString + ':' + secondsString;
    }
}
