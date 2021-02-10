import { Component, OnInit, ChangeDetectionStrategy, ChangeDetectorRef } from '@angular/core';
import { ViewController, NavParams, ToastController } from 'ionic-angular';
import { TranslateService } from 'ng2-translate';

// services
import { VideoUploaderService, IVideoUserDataResponse } from 'services/video-uploader';

@Component({
    selector: 'user-search-autocomplete',
    templateUrl: 'index.html',
    changeDetection: ChangeDetectionStrategy.OnPush,
    providers: [
        VideoUploaderService
    ]
})

export class UserSearchAutocompleteComponent implements OnInit {
    userIdListIgnore: Array<any> = [];
    autocompleteItems: Array<IVideoUserDataResponse> = [];
    selectItems: Array<IVideoUserDataResponse> = [];
    autocompleteLoading = false;
    debounceTime: number = 1500;
    searchQuery: string;

    /**
     * Constructor
     */
    constructor(
        private ref: ChangeDetectorRef,
        private videoUploaderService: VideoUploaderService,
        private translate: TranslateService,
        private toast: ToastController,
        private view: ViewController,
        private navParams: NavParams)
    {
        if ( this.navParams.get('userIdListIgnore').length > 0 ) {
            this.userIdListIgnore = this.navParams.get('userIdListIgnore');
        }

        console.log(this.userIdListIgnore);
    }

    /**
     * Component init
     */
    ngOnInit(): void {
        this.updateSearch();
    }

    /**
     * Keep empty
     */
    keepEmpty() {
        this.view.dismiss([]);
    }

    /**
     * Cancel
     */
    cancel() {
        this.view.dismiss([]);
    }

    done() {
        this.view.dismiss(this.selectItems);
    }

    /**
     * Choose item
     */
    chooseItem(user: IVideoUserDataResponse) {

        let autocompleteItems: Array<IVideoUserDataResponse> = [];

        this.selectItems.push(user);
        this.userIdListIgnore.push(user.userId);

        if ( this.autocompleteItems.length > 0 ) {
            this.autocompleteItems.forEach((autocompleteUser: IVideoUserDataResponse) => {
                if ( user.userId != autocompleteUser.userId ) {
                    autocompleteItems.push(autocompleteUser);
                }
            });
        }

        this.autocompleteItems = autocompleteItems;

        this.ref.markForCheck();

        const notificationToaster = this.toast.create({
            message: this.translate.instant('user_has_been_add'),
            closeButtonText: this.translate.instant('ok'),
            showCloseButton: true,
            duration: 3000
        });

        notificationToaster.present();
    }

    /**
     * Update search
     */
    updateSearch() {
        if (!this.searchQuery) {
            this.autocompleteItems = [];

            return;
        }

        this.autocompleteLoading = true;
        this.videoUploaderService.loadAutocomplete(this.searchQuery, this.userIdListIgnore).subscribe((response: Array<IVideoUserDataResponse>) => {
            this.autocompleteLoading = false;

            if ( response != undefined ) {
                this.autocompleteItems = response;
            }

            this.ref.markForCheck();
        });
    }
}
