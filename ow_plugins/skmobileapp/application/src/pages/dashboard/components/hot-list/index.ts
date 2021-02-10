import { Component, ChangeDetectionStrategy, OnInit, OnDestroy, ChangeDetectorRef, ViewChild, Input } from '@angular/core';
import { Observable } from 'rxjs/Observable';
import { NavController, AlertController, ToastController } from 'ionic-angular';
import { ISubscription } from 'rxjs/Subscription';
import { TranslateService } from 'ng2-translate';

// services
import { HotListService, IHotListItem } from 'services/hot-list';
import { PermissionsService, IPermission } from 'services/permissions';
import { SiteConfigsService } from 'services/site-configs';

// components
import { PermissionsComponent } from 'shared/components/permissions';

// pages
import { ProfileViewPage } from 'pages/profile';

@Component({
    selector: 'hot-list',
    templateUrl: 'index.html',
    changeDetection: ChangeDetectionStrategy.OnPush
})

export class HotListComponent implements OnInit, OnDestroy {
    @Input() isDashboardLoading: boolean;
    @ViewChild(PermissionsComponent) permissionsComponent: PermissionsComponent;

    isHotListFetched$: Observable<boolean>;
    hotList$: Observable<Array<IHotListItem>>;
    isMeInHotList$: Observable<boolean>;

    hotListPermission: IPermission;
    requestInProcessing: boolean = false;

    private hotListPermissionSubscription: ISubscription;   

    /**
     * Constructor
     */
    constructor(
        public ref: ChangeDetectorRef,
        private siteConfigs: SiteConfigsService,
        private toast: ToastController,
        private alert: AlertController,
        private translate: TranslateService,
        private permissions: PermissionsService,
        private hotList: HotListService, 
        private nav: NavController) {}

    /**
     * Component init
     */
    ngOnInit(): void {
        // init watchers
        this.isHotListFetched$ = this.hotList.watchIsHotListFetched();
        this.hotList$ = this.hotList.watchHotList();
        this.isMeInHotList$ = this.hotList.watchMeInHotList();

        // watch the permission updates
        this.hotListPermissionSubscription = this.permissions
            .watchMe('hotlist_add_to_list')
            .subscribe((permission: IPermission) => {
                this.hotListPermission = permission;
                this.ref.markForCheck();
            });
    }

    /**
     * Component destroy
     */
    ngOnDestroy(): void {
        this.hotListPermissionSubscription.unsubscribe();
    }

    /**
     * View profile
     */
    viewProfile(hotListData: IHotListItem): void {
        this.nav.push(ProfileViewPage, {
            userId: hotListData.user.id
        });
    }

    /**
     * Track hot list
     */
    trackHotList(index: number, hotListData: IHotListItem): number|string {
        return hotListData.hotList.id;
    }

    /**
     * Join me to hot list
     */
    joinMeToHotList(): void {
        if (this.hotListPermission.isPromoted) {
            this.permissionsComponent.showAccessDeniedAlert();

            return;
        }

        // show a confirmation window
        if (this.hotListPermission.creditsCost < 0) {
            const buttons: any[] = [{
                text: this.translate.instant('no')
            }, {
                text: this.translate.instant('yes'),
                handler: () => this.joinMeToHotListRequest()
            }];

            const confirm = this.alert.create({
                message: this.translate.instant('hot_list_join_confirmation', {
                    count: Math.abs(this.hotListPermission.creditsCost)
                }),
                buttons: buttons
            });

            confirm.present();

            return;
        }

        this.joinMeToHotListRequest();
    }

    /**
     * Delete me from hot list
     */
    deleteMeFromHotList(): void {
        // show a confirmation window
        const buttons: any[] = [{
            text: this.translate.instant('no')
        }, {
            text: this.translate.instant('yes'),
            handler: () => {
                this.requestInProcessing = true;
                this.ref.markForCheck();

                this.hotList.deleteMeFromHotList().subscribe(() => {
                    this.requestInProcessing = false;
                    this.ref.markForCheck();
                });
            }
        }];

        const confirm = this.alert.create({
            message: this.translate.instant('hot_list_delete_confirmation'),
            buttons: buttons
        });

        confirm.present();
    }

    /**
     * Join me to hot list request
     */
    private joinMeToHotListRequest(): void {
        this.requestInProcessing = true;
        this.ref.markForCheck();

        this.hotList.addMeToHotList().subscribe(() => {
            this.requestInProcessing = false;
            this.ref.markForCheck();

            if (this.hotListPermission.creditsCost) {
                const toast = this.toast.create({
                    message: this.hotListPermission.creditsCost > 0
                        ? this.translate.instant('increase_credits_notification', {
                            count: this.hotListPermission.creditsCost
                        })
                        : this.translate.instant('decrease_credits_notification', {
                            count: Math.abs(this.hotListPermission.creditsCost)
                        }),
                    closeButtonText: this.translate.instant('ok'),
                    showCloseButton: true,
                    duration: this.siteConfigs.getConfig('toastDuration')
                });
    
                toast.present();
            }
        });
    }
}
