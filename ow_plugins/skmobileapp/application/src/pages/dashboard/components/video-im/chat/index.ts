import { Component, OnInit, OnDestroy, ViewChild, ElementRef, ChangeDetectorRef, ChangeDetectionStrategy, NgZone, Renderer2 } from '@angular/core';
import { ToastController, ViewController, NavParams, Toast, Platform } from 'ionic-angular';
import { ISubscription } from 'rxjs/Subscription';
import { Observable } from 'rxjs/Observable';
import adapter from 'webrtc-adapter';

//states
import { IVideoImNotificationData } from 'store/states';

// services
import { TranslateService } from 'ng2-translate';
import { AuthService } from 'services/auth';
import { UserService, IUserWithAvatar } from 'services/user';
import { SiteConfigsService } from 'services/site-configs';
import { PermissionsService, IPermission } from 'services/permissions';
import { ApplicationService } from 'services/application';
import { StringUtilsService } from 'services/string-utils';
import { VideoImService } from 'services/video-im';

// components
import { VideoImTimerComponent } from './components/timer';
import { AvatarComponent } from 'shared/components/avatar';

const enum VideoImCallingState {
    Incoming,
    Outgoing,
    Started,
    Finished,
    NoAnswer,
    Blocked
}

declare const window: any;
declare const cordova: any;
declare const RTCPeerConnection: any;
declare const RTCIceCandidate: any;
declare const RTCSessionDescription: any;

@Component({
    selector: 'video-im-chat',
    templateUrl: 'index.html',
    changeDetection: ChangeDetectionStrategy.OnPush
})
export class VideoImChatComponent implements OnInit, OnDestroy {
    @ViewChild(VideoImTimerComponent) timer: VideoImTimerComponent;
    @ViewChild('interlocutorAvatar') avatar: AvatarComponent;
    @ViewChild('remoteVideo') remoteVideo: ElementRef;
    @ViewChild('localVideo') localVideo: ElementRef;

    callingState: VideoImCallingState;

    interlocutorId: number;
    interlocutorData: IUserWithAvatar;

    isAudioEnabled: boolean = true;
    isVideoEnabled: boolean = true;
    isFullSizeEnabled: boolean = false;

    private isMeInitiator: boolean = false;
    private isSessionStarted: boolean = false;
    private isHangupClicked: boolean = false;

    private activeCallerNotificationsSubscription: ISubscription;
    private videoImPermissionSubscription: ISubscription;
    private timedCallPermission: IPermission;

    private peerConnection: RTCPeerConnection;
    private localStream: MediaStream;

    private trackCreditsTimeoutHandler: any = null;
    private checkForInterlocutorAnswerTimeoutHandler: any = null;

    private trackCreditsTime: number = 60 * 1000; // one minute
    private checkForInterlocutorAnswerTime: number = 120 * 1000; // two minutes

    private isPwaIos: boolean = false;
    private isNativeIos: boolean = false;
    private isExternalBrowser: boolean = false;

    private refreshIosVideosTimeoutHandler: any = null;
    private refreshIosVideosIntervalHandler: any = null;

    /**
     * Constructor
     */
    constructor(
        private zone: NgZone,
        private platform: Platform,
        private renderer: Renderer2,
        private ref: ChangeDetectorRef,
        private navParams: NavParams,
        private toast: ToastController,
        private view: ViewController,
        private user: UserService,
        private auth: AuthService,
        private translate: TranslateService,
        private siteConfigs: SiteConfigsService,
        private permissions: PermissionsService,
        private application: ApplicationService,
        private stringUtils: StringUtilsService,
        private videoIm: VideoImService)
    {
        this.interlocutorId = this.navParams.get('userId');
        this.isMeInitiator = this.navParams.get('isMeInitiator');

        this.isPwaIos = this.platform.is('ios') && this.application.isAppRunningInExternalBrowser();
        this.isNativeIos = this.platform.is('ios') && !this.application.isAppRunningInExternalBrowser();
        this.isExternalBrowser = this.application.isAppRunningInExternalBrowser();
    }

    /**
     * Component init
     */
    ngOnInit(): void {
        if (!this.isExternalBrowser) {
            // Preventing phone fall asleep
            window.plugins.insomnia.keepAwake();
        }

        this.callingState = this.isMeInitiator
            ? VideoImCallingState.Outgoing
            : VideoImCallingState.Incoming;
        this.ref.markForCheck();

        this.interlocutorData = this.user.getUserWithAvatar(this.interlocutorId);

        // Register iosrtc globals and put remote video behind the webview.
        // In iosrtc context z-index on 'video' tag works differently:
        // it puts video elements regarding webview so -1 means that video will be positioned
        // behind the webview but 1+ on it.
        if (this.isNativeIos) {
            cordova.plugins.iosrtc.registerGlobals();

            this.remoteVideo.nativeElement.style.zIndex = -1;
            this.localVideo.nativeElement.style.zIndex = 2;
        }

        // Is the webRTC enabled in the browser?
        if (!navigator.mediaDevices) {
            this.showMessage(
                this.platform.is('ios') && this.isExternalBrowser
                    ? this.translate.instant('vim_safari_supports_webrtc')
                    : this.translate.instant('vim_user_browser_doesnt_support_webrtc')
            );

            this.videoIm.sendNotification(this.interlocutorId, {
                type: 'not_supported'
            }).subscribe();

            // mark all notifications as accepted
            this.videoIm.markNotifications(this.interlocutorId);

            // Video chat has been finished
            this.callingState = VideoImCallingState.Finished;

            this.ref.detectChanges();

            return;
        }

        // watch the permission updates
        this.videoImPermissionSubscription = this.permissions
            .watchMe('videoim_video_im_timed_call')
            .subscribe((permission: IPermission) => {
                this.timedCallPermission = permission;
            });

        this.initializeLocalStream();
    }

    /**
     * Component destroy
     */
    ngOnDestroy(): void {
        if (!this.isExternalBrowser) {
            // Bringing back default phone sleep behaviour
            window.plugins.insomnia.allowSleepAgain();
        }

        // Hangup if the component dismissed because of another incoming call accepted
        this.hangup();

        // Remove iosrtc fix to ensure the app is visible
        if (this.isNativeIos) {
            this.renderer.removeStyle(document.body, 'visibility');
        }

        if (this.videoImPermissionSubscription) {
            this.videoImPermissionSubscription.unsubscribe();
        }

        if (this.activeCallerNotificationsSubscription) {
            this.activeCallerNotificationsSubscription.unsubscribe();
        }
    }

    /**
     * Is call incoming
     */
    get isCallIncoming(): boolean {
        return this.callingState === VideoImCallingState.Incoming
    }

    /**
     * Is call outgoing
     */
    get isCallOutgoing(): boolean {
        return this.callingState === VideoImCallingState.Outgoing
    }

    /**
     * Is call started
     */
    get isCallStarted(): boolean {
        return this.callingState === VideoImCallingState.Started
    }

    /**
     * Is call finished
     */
    get isCallFinished(): boolean {
        return this.callingState === VideoImCallingState.Finished
    }

    /**
     * Is call not answered
     */
    get isCallNotAnswered(): boolean {
        return this.callingState === VideoImCallingState.NoAnswer
    }

    /**
     * Calling status label
     */
    get callingStatusLabel(): string {
        switch (this.callingState) {
            case VideoImCallingState.Incoming:
                return this.translate.instant('vim_incoming_call');

            case VideoImCallingState.Outgoing:
                return this.translate.instant('vim_calling');

            case VideoImCallingState.Started:
                return this.translate.instant('vim_talking');

            case VideoImCallingState.NoAnswer:
                return this.translate.instant('vim_no_answer');

            case VideoImCallingState.Finished:
            default:
                return this.translate.instant('vim_call_ended');
        }
    }

    /**
     * Calling status CSS class
     */
    get callingStatusCssClass(): string {
        switch (this.callingState) {
            case VideoImCallingState.Incoming:
                return 'sk-videoim-incoming-call';

            case VideoImCallingState.Outgoing:
                return 'sk-videoim-outgoing-call';

            case VideoImCallingState.Started:
                return 'sk-videoim-video-call';

            case VideoImCallingState.NoAnswer:
                return 'sk-videoim-finished-call sk-videoim-no-answer';

            case VideoImCallingState.Finished:
            default:
                return 'sk-videoim-finished-call';
        }
    }

    /**
     * Full screen CSS class
     */
    get fullScreenCssClass(): string {
        return this.isFullSizeEnabled ? 'sk-videoim-video-fullsize' : ''
    }

    /**
     * Background avatar
     */
    get backgroundAvatar(): string {
        return this.interlocutorData.avatar && this.interlocutorData.avatar.active && this.interlocutorData.avatar.url
            ? this.interlocutorData.avatar.url
            : this.avatar.bigDefaultAvatar;
    }

    /**
     * Call
     */
    call(): void {
        this.videoIm.setActiveInterlocutorData(this.interlocutorId, true);
    }

    /**
     * Hangup
     */
    hangup(): void {
        if (!this.isHangupClicked) {
            this.isHangupClicked = true;
            this.isMeInitiator = false;

            this.videoIm.sendNotification(this.interlocutorId, {
                type: 'bye'
            }).subscribe();

            this.closePeerConnection();
        }
    }

    /**
     * Close
     */
    close(): void {
        // Clear active interlocutor data
        this.videoIm.removeActiveInterlocutorData(this.interlocutorId);

        this.view.dismiss();
    }

    /**
     * Mute local video
     */
    disableLocalVideo(event: Event): void {
        event.stopPropagation();

        if (this.isNativeIos) {
            this.refreshIosVideos();
        }

        if (this.localStream && this.localStream.getVideoTracks()[0] != null) {
            this.localStream.getVideoTracks()[0].enabled = false;
            this.isVideoEnabled = false;

            this.ref.detectChanges();
        }
    }

    /**
     * Enable local video
     */
    enableLocalVideo(event: Event): void {
        event.stopPropagation();

        if (this.isNativeIos) {
            this.refreshIosVideos();
        }

        if (this.localStream && this.localStream.getVideoTracks()[0] != null) {
            this.localStream.getVideoTracks()[0].enabled = true;
            this.isVideoEnabled = true;

            this.ref.detectChanges();
        }
    }

    /**
     * Mute local audio
     */
    disableLocalAudio(event: Event): void {
        event.stopPropagation();

        if (this.localStream && this.localStream.getAudioTracks()[0] != null) {
            this.localStream.getAudioTracks()[0].enabled = false;
            this.isAudioEnabled = false;

            this.ref.detectChanges();
        }
    }

    /**
     * Enable local audio
     */
    enableLocalAudio(event: Event): void {
        event.stopPropagation();

        if (this.isPwaIos) {
            let subscription: Observable<any> = Observable.fromPromise(
                navigator.mediaDevices.getUserMedia({
                    audio: true,
                })
            );
            subscription.subscribe((stream: MediaStream) => {
                stream.getAudioTracks().forEach((track: MediaStreamTrack) => {
                    (this.peerConnection as any).getSenders().forEach((sender: RTCRtpSender) => {
                        if (sender.track && sender.track.kind == track.kind) {
                            (sender as any).replaceTrack(track);

                            this.localStream.removeTrack(sender.track);
                            this.localStream.addTrack(track);

                            this.isAudioEnabled = true;

                            this.ref.detectChanges();
                        }
                    });
                })
            });
        }
        else {
            if (this.localStream && this.localStream.getAudioTracks()[0] != null) {
                this.localStream.getAudioTracks()[0].enabled = true;
                this.isAudioEnabled = true;

                this.ref.detectChanges();
            }
        }
    }

    /**
     * Toggle Full size
     */
    toggleFullSize(): void {
        if (this.callingState == VideoImCallingState.Started) {
            this.isFullSizeEnabled = !this.isFullSizeEnabled;

            // for native iOS
            if (this.isNativeIos) {
                this.remoteVideo.nativeElement.style.zIndex = this.isFullSizeEnabled ? 1 : -1;

                this.refreshIosVideos();
            }
        }
    }
    /**
     * Refresh iOS videos (required by iosrtc)
     */
    private refreshIosVideos(): void {
        if (this.isNativeIos) {
            this.zone.runOutsideAngular(() => {
                if (this.refreshIosVideosTimeoutHandler != null) {
                    clearTimeout(this.refreshIosVideosTimeoutHandler);
                    clearInterval(this.refreshIosVideosIntervalHandler);

                    this.refreshIosVideosTimeoutHandler = null;
                }

                this.refreshIosVideosTimeoutHandler = setTimeout(() => {
                    clearTimeout(this.refreshIosVideosTimeoutHandler);
                    clearInterval(this.refreshIosVideosIntervalHandler);
                }, 1000);
                this.refreshIosVideosIntervalHandler = setInterval(cordova.plugins.iosrtc.refreshVideos, 100);
            });
        }
    }

    /**
     * Local stream handler
     */
    private localStreamHandler(stream: MediaStream): void {
        // Assigning stream to <video> tag
        // this.isNativeIos
        //     ? this.localVideo.nativeElement.src = URL.createObjectURL(stream)
        //     : this.localVideo.nativeElement.srcObject = stream;
        this.localVideo.nativeElement.srcObject = stream;
        this.localVideo.nativeElement.muted = true;

        this.localStream = stream;

        this.openPeerConnection();

        if (this.isMeInitiator) {
            this.videoIm.setSessionId(
                this.interlocutorId,
                this.stringUtils.getRandomString().substr(0, 20)
            );

            this.sendOffer();
        }

        this.activeCallerNotificationsSubscription = this.videoIm.watchActiveInterlocutorNotifications()
            .subscribe((notifications: Array<IVideoImNotificationData>) => {
                this.processNotifications(notifications);
            });

        this.checkForInterlocutorAnswer();
    }

    /**
     * Local description handler
     */
    private localDescriptionHandler(description: RTCSessionDescription): void {
        this.peerConnection.setLocalDescription(
            description,
            () => {},
            (error: DOMError) => this.errorHandler(error)
        );

        if (description.sdp != null) {
            description.sdp = description.sdp.replace(new RegExp('UDP/TLS/RTP/SAVPF', 'g'), 'RTP/SAVPF');
        }

        this.videoIm.sendNotification(this.interlocutorId, description).subscribe();
    }

    /**
     * Error handler
     */
    private errorHandler(error: DOMError): void {
        throw new Error(error.toString());
    }

    /**
     * Check for interlocutor answer
     */
    private checkForInterlocutorAnswer(): void {
        this.zone.runOutsideAngular(() => {
            clearTimeout(this.checkForInterlocutorAnswerTimeoutHandler);
            this.checkForInterlocutorAnswerTimeoutHandler = setTimeout(() => {
                if (!this.isSessionStarted) {
                    this.closePeerConnection();
                }

                clearTimeout(this.checkForInterlocutorAnswerTimeoutHandler);

                // Answer hasn't been received
                this.callingState = VideoImCallingState.NoAnswer;
                this.ref.markForCheck();
            }, this.checkForInterlocutorAnswerTime);
        });
    }

    /**
     * Track credits
     */
    private trackCredits(): void {
        const trackCreditsType: string = this.siteConfigs.getConfig('videoim_track_credits_type');
        let isTrackCreditsAllowed: boolean = false;

        if (trackCreditsType == 'both' ||
            (trackCreditsType == 'initiator' && this.isMeInitiator) ||
            (trackCreditsType == 'interlocutor' && !this.isMeInitiator)) {

            isTrackCreditsAllowed = true;
        }

        if (isTrackCreditsAllowed) {
            this.zone.runOutsideAngular(() => {
                if (this.timedCallPermission) {
                    if (this.timedCallPermission.isAllowed) {
                        this.permissions.trackAction('videoim', 'video_im_timed_call').subscribe();
                    }
                    else {

                        this.showMessage(this.translate.instant('vim_call_ended_you_ran_out_credits'));

                        this.videoIm.sendNotification(this.interlocutorId, {
                            type: 'credits_out'
                        }).subscribe();

                        this.closePeerConnection();
                    }

                    this.trackCreditsTimeoutHandler = setTimeout(() => this.trackCredits(), this.trackCreditsTime);
                }
            });
        }
    }

    /**
     * Initialize local stream
     */
    private initializeLocalStream(): void {
        // Try to receive user media with both audio and video streams.
        let subscription: Observable<any> = Observable.fromPromise(
            navigator.mediaDevices.getUserMedia({
                audio: true,
                video: true
            })
        );

        // If user media was received then localStreamHandler will be called,
        // otherwise request user media second time without video stream.
        subscription.subscribe((stream: MediaStream) => this.localStreamHandler(stream), (error: any) => {
            subscription = Observable.fromPromise(
                navigator.mediaDevices.getUserMedia({
                    audio: true,
                    video: false
                })
            );

            // If second request failed then show error message
            subscription.subscribe((stream: MediaStream) => this.localStreamHandler(stream), (error: any) => {
                this.showMessage(this.translate.instant('vim_share_media_devices_error'));

                // mark all notifications as accepted
                this.videoIm.markNotifications(this.interlocutorId);

                // Video chat has been finished
                this.callingState = VideoImCallingState.Finished;

                this.ref.detectChanges();
            });
        });
    }

    /**
     * Open peer connection
     */
    private openPeerConnection(): void {
        // close the peer connection if opened
        if (this.peerConnection) {
            this.peerConnection.close();
            this.peerConnection = null;
        }

        this.peerConnection = new RTCPeerConnection({
            iceServers: this.siteConfigs.getConfig('videoim_server_list')
        }, {
            optional: [{
                DtlsSrtpKeyAgreement: true
            }]
        });

        this.peerConnection.onicecandidate = (event: RTCPeerConnectionIceEvent) => {
            if (event.candidate == null) {
                return;
            }

            this.videoIm.sendNotification(this.interlocutorId, {
                type: 'candidate',
                id: event.candidate.sdpMid,
                label: event.candidate.sdpMLineIndex,
                candidate: event.candidate.candidate
            }).subscribe();
        };

        if (this.isNativeIos) {
            this.peerConnection.onaddstream = (event: MediaStreamEvent) => {
                // Assigning stream to <video> tag
                // this.remoteVideo.nativeElement.src = URL.createObjectURL(event.stream)
                this.remoteVideo.nativeElement.srcObject = event.stream;
            };
        }
        else {
            (this.peerConnection as any).ontrack = (event: any) => {
                if (event.streams.length) {
                    // Assigning stream to <video> tag
                    this.remoteVideo.nativeElement.srcObject = event.streams[0];
                }
            };
        }

        this.peerConnection.oniceconnectionstatechange = (event: Event) => {
            if (this.peerConnection && this.peerConnection.iceConnectionState.toLowerCase() === 'connected') {
                // In order to show remote video properly we need to fix iosrtc video "opacity" issue,
                // so we put video behind the webview and hide body element but show element with controls
                if (this.isNativeIos) {
                    this.renderer.setStyle(document.body, 'visibility', 'hidden');

                    this.refreshIosVideos();

                    // Reroute audio output stream to iOS speaker
                    cordova.plugins.audioroute.overrideOutput('speaker');
                }

                this.isSessionStarted = true;

                // clear connection status timer
                if (this.checkForInterlocutorAnswerTimeoutHandler != null) {
                    clearTimeout(this.checkForInterlocutorAnswerTimeoutHandler);
                }

                if (this.timedCallPermission.creditsCost && !this.user.getMe().user.isAdmin) {
                    // send request each minute
                    this.trackCredits();
                }

                // Video chat has been started
                this.callingState = VideoImCallingState.Started;

                // Start timer
                this.timer.startTimer();

                this.ref.detectChanges();
            }
        };

        if (this.isNativeIos) {
            this.peerConnection.addStream(this.localStream);
        }
        else {
            this.localStream.getTracks().forEach(
                // Dirty solution to avoid "ontrack" typescript error
                (track: any) => (this.peerConnection as any).addTrack(track, this.localStream)
            );
        }

        // this.localStream.getAudioTracks()[0].onended = () => this.initializeLocalStream;
    }

    /**
     * Closes peer connection
     */
    private closePeerConnection(): void {
        // Close the peer connection
        if (this.peerConnection) {
            this.peerConnection.close();
            this.peerConnection = null;
        }

        // Unsubscribe from notifications
        if (this.activeCallerNotificationsSubscription) {
            this.activeCallerNotificationsSubscription.unsubscribe();
        }

        // Stop timer
        this.timer.stopTimer();

        // Video chat has been finished
        this.callingState = VideoImCallingState.Finished;

        // Stop the local stream
        if (this.localStream) {
            try {
                this.localStream.getAudioTracks()[0].stop();
                this.localStream.getVideoTracks()[0].stop();
            }
            catch (e) {}
        }

        // Clear connection timeout handler
        if (this.checkForInterlocutorAnswerTimeoutHandler != null) {
            clearTimeout(this.checkForInterlocutorAnswerTimeoutHandler);
        }

        // Clear track credits interval handler
        if (this.trackCreditsTimeoutHandler != null) {
            clearTimeout(this.trackCreditsTimeoutHandler);
        }

        // Clear refresh videos interval handler in iOS
        if (this.refreshIosVideosIntervalHandler != null) {
            clearInterval(this.refreshIosVideosIntervalHandler);
        }

        // Remove iosrtc fix to make component visible
        if (this.isNativeIos) {
            this.renderer.removeStyle(document.body, 'visibility');
            this.refreshIosVideos();
        }

        this.isHangupClicked = true;
        this.isSessionStarted = false;
        this.isFullSizeEnabled = false;

        this.videoIm.removeActiveInterlocutorData(this.interlocutorId);
        this.videoIm.removeSessionId(this.interlocutorId);

        this.ref.markForCheck();
    }

    /**
     * Sends offer
     */
    private sendOffer(): void {
        this.peerConnection.createOffer()
            .then((description: RTCSessionDescription) => this.localDescriptionHandler(description))
            .catch((error: DOMError) => this.errorHandler(error));
    }

    /**
     * Applies offer and send answer
     */
    private applyOfferAndSendAnswer(description: RTCSessionDescription): void {
        this.peerConnection.setRemoteDescription(new RTCSessionDescription(description))
            .then(() => {
                this.peerConnection.createAnswer()
                    .then((description: RTCSessionDescription) => this.localDescriptionHandler(description))
                    .catch((error: DOMError) => this.errorHandler(error));
            })
            .catch((error: DOMError) => this.errorHandler(error));
    }

    /**
     * Applies answer
     */
    private applyAnswer(description: RTCSessionDescription): void {
        this.peerConnection.setRemoteDescription(
            new RTCSessionDescription(description),
            () => {}
        );
    }

    /**
     * Adds ice candidate
     */
    private addIceCandidate(iceCandidate: RTCIceCandidate): void {
        this.peerConnection.addIceCandidate(
            iceCandidate
        );
    }

    /**
     * Process notifications
     */
    private processNotifications(notifications: Array<IVideoImNotificationData>): void {
        if (!notifications) {
            return;
        }

        // Mark all notifications as accepted
        this.videoIm.markNotifications(this.interlocutorId);

        notifications.forEach((notificationData: IVideoImNotificationData) => {
            switch (notificationData.type) {
                case 'not_supported':
                    this.showMessage(this.translate.instant('vim_send_request_error_webrtc_not_supported'));

                    this.closePeerConnection();

                    break;

                case 'not_permitted':
                    this.showMessage(this.translate.instant('vim_does_not_accept_incoming_calls'));

                    this.closePeerConnection();

                    break;

                case 'bye':
                    this.showMessage(this.translate.instant('vim_session_closed'));

                    this.closePeerConnection();

                    break;

                case 'credits_out':
                    this.showMessage(this.translate.instant('vim_mobile_call_ended_user_ran_out_credits'));

                    this.closePeerConnection();

                    break;

                case 'blocked':
                    this.showMessage(this.translate.instant('vim_request_blocked'));

                    this.closePeerConnection();

                    // Initiator has been blocked
                    this.callingState = VideoImCallingState.Blocked;
                    this.ref.markForCheck();

                    break;

                case 'declined':
                    this.showMessage(this.translate.instant('vim_request_declined'));

                    this.closePeerConnection();

                    break;

                case 'candidate':
                    if (notificationData.sessionId == this.videoIm.getSessionId(this.interlocutorId)) {
                        const iceCandidate: RTCIceCandidate = new RTCIceCandidate({
                            sdpMid: notificationData.notification.id,
                            sdpMLineIndex: notificationData.notification.label,
                            candidate: notificationData.notification.candidate
                        });

                        this.addIceCandidate(iceCandidate);
                    }

                    break;

                case 'answer':
                    this.applyAnswer(<RTCSessionDescription> notificationData.notification);

                    break;

                case 'offer':
                    const iceConnectionState: string = this.peerConnection.iceConnectionState.toLowerCase();

                    // We need to check connection state in order to resolve trio connection.
                    // Without this clause third connecting person reconnects connection between
                    // already connected first two persons, what is unacceptable.
                    if (iceConnectionState === 'new' || iceConnectionState === 'checking') {
                        if (this.isMeInitiator && this.auth.getUserId() > notificationData.user) {
                            // Reconnection process

                            // Now I'm not initiator
                            this.isMeInitiator = false;

                            // Reopen peer connection
                            this.openPeerConnection();

                            // Change session id
                            this.videoIm.setSessionId(this.interlocutorId, notificationData.sessionId);
                        }
                        else {
                            // Mark interlocutor's incoming offer by its session id
                            // because interlocutor will accept my offer and I don't need his one
                            this.videoIm.markNotifications(this.interlocutorId, notificationData.sessionId);
                        }
                    }

                    if (!this.isMeInitiator) {
                        this.applyOfferAndSendAnswer(<RTCSessionDescription> notificationData.notification);
                    }

                    break;

                default:
            }
        });
    }

    /**
     * Show message
     */
    private showMessage(message: string): void {
        const toast: Toast = this.toast.create({
            message: message,
            closeButtonText: this.translate.instant('ok'),
            showCloseButton: true
        });

        toast.present();
    }
}
