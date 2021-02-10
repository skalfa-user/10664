import { Component, ChangeDetectionStrategy, Output, EventEmitter } from '@angular/core';
import { TranslateService } from 'ng2-translate';
import { AlertController } from 'ionic-angular';

// services
import { PersistentStorageService } from 'services/persistent-storage';
import { MatchActionsService, MatchType } from 'services/match-actions';

@Component({
    selector: 'match-actions',
    templateUrl: 'index.html',
    changeDetection: ChangeDetectionStrategy.OnPush
})

export class MatchActionsComponent {
    @Output() userLiked = new EventEmitter<number | string>();
    @Output() userDisliked = new EventEmitter<number | string>();
    @Output() matchDeleted = new EventEmitter<any>();

    static readonly TYPE_LIKE: MatchType = 'like';
    static readonly TYPE_DISLIKE: MatchType = 'dislike';

    /**
     * Constructor
     */
    constructor(
        private matchActions: MatchActionsService,
        private alert: AlertController,
        private translate: TranslateService,
        private persistentStorage: PersistentStorageService) {}

    /**
     * Delete match
     */
    deleteMatch(userId: number | string, id: number | string): void {
        this.matchActions.stopAllUserSubscriptions(userId);
        this.matchActions.deleteMatch(id, userId);
        this.matchDeleted.emit();
    }

    /**
     * Like user
     */
    likeUser(userId: number | string, name: string): void {
        // show a confirmation window
        if (!this.persistentStorage.getValue('user_like_pressed', false)) {
            const confirm = this.alert.create({
                enableBackdropDismiss: false,
                message: this.translate.instant('like_confirmation', {
                    name: name
                }),
                buttons: [{
                    text: this.translate.instant('cancel'),
                    handler: () => this.persistentStorage.setValue('user_like_pressed', true)
                }, {
                    text: this.translate.instant('ok'),
                    handler: () => {
                        this.matchActions.stopAllUserSubscriptions(userId);
                        this.persistentStorage.setValue('user_like_pressed', true);

                        this.matchActions.createMatch(userId, MatchActionsComponent.TYPE_LIKE);
                        this.userLiked.emit(userId);
                    }
                }]
            });

            confirm.present();

            return;
        }

        this.matchActions.stopAllUserSubscriptions(userId);
        this.matchActions.createMatch(userId, MatchActionsComponent.TYPE_LIKE);
        this.userLiked.emit(userId);
    }

    /**
     * Dislike user
     */
    dislikeUser(userId: number | string, name: string): void {
        // show a confirmation window
        if (!this.persistentStorage.getValue('user_dislike_pressed', false)) {
            const confirm = this.alert.create({
                enableBackdropDismiss: false,
                message: this.translate.instant('dislike_confirmation', {
                    name: name
                }),
                buttons: [{
                    text: this.translate.instant('cancel'),
                    handler: () => this.persistentStorage.setValue('user_dislike_pressed', true)
                }, {
                    text: this.translate.instant('ok'),
                    handler: () => {
                        this.matchActions.stopAllUserSubscriptions(userId);
                        this.persistentStorage.setValue('user_dislike_pressed', true);

                        this.matchActions.createMatch(userId, MatchActionsComponent.TYPE_DISLIKE);
                        this.userDisliked.emit(userId);
                    }
                }]
            });

            confirm.present();

            return;
        }

        this.matchActions.stopAllUserSubscriptions(userId);
        this.matchActions.createMatch(userId, MatchActionsComponent.TYPE_DISLIKE);
        this.userDisliked.emit(userId);
    }
}
