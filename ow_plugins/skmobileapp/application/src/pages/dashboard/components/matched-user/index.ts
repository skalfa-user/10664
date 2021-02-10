import { Component, OnInit, ChangeDetectionStrategy } from '@angular/core';
import { ViewController, NavParams } from 'ionic-angular';

// services
import { UserService, IUserWithAvatar } from 'services/user';
import { MatchedUsersService, IMatchedUserListItem } from 'services/matched-users';

@Component({
    selector: 'matched-user',
    templateUrl: 'index.html',
    changeDetection: ChangeDetectionStrategy.OnPush
})

export class MatchedUserPageComponent implements OnInit {
    me: IUserWithAvatar;
    matchedUser: IMatchedUserListItem;

    /**
     * Constructor
     */
    constructor(
        private matchedUsers: MatchedUsersService,
        private navParams: NavParams,
        private user: UserService, 
        private view: ViewController) 
    {
        this.matchedUser = this.navParams.get('matchedUser');
    }

    /**
     * Component init
     */
    ngOnInit(): void {
        this.me = this.user.getMe();

        // mark as notified
        this.matchedUsers.markMatchedUserAsNotified(this.matchedUser.matchedUser.id).subscribe();
    }

    /**
     * Close
     */
    close(isShowChat: false): void {
        this.view.dismiss({
            isShowChat: isShowChat
        });
    }
}
