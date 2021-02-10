import { Injectable, NgZone } from '@angular/core';
import { Subject } from 'rxjs/Subject';
import { Observable } from 'rxjs/Observable';

// services
import { ApplicationService } from 'services/application';
import { AuthService } from 'services/auth';
import { PersistentStorageService } from 'services/persistent-storage';

// responses
import { IServerEventsResponse } from './responses';

declare var EventSource: any;

@Injectable()
export class ServerEventsService {
    private serverEventsData$: Subject<IServerEventsResponse> = new Subject();
    private reconnectHandler: any;
    private reconnectTimeout: number = 10000;
    private eventSource: any;
    private isEventsStarted: boolean = false;

    constructor(
        private persistentStorage: PersistentStorageService, 
        private ngZone: NgZone,
        private auth: AuthService, 
        private application: ApplicationService
    ) {
        this.auth.watchSetAuthenticated$.subscribe(() => this.restart());
        this.auth.watchLogout$.subscribe(() => this.restart());
    }

    /**
     * Stop
     */
    stop(): void {
        if (this.isEventsStarted) {
            this.isEventsStarted = false;
            this.eventSource.close();
        }
    }
 
    /**
     * Start server events
     */
    start(): void {
        this.restart();
    }

    /**
     * Watch data
     */
    watchData(channelName: string): Observable<IServerEventsResponse> {
        return  this.serverEventsData$.filter((data: IServerEventsResponse) => data.channel == channelName);
    }

    /**
     * Restart server events
     */
    protected restart(): void {
        this.stop();
        this.isEventsStarted = true;
  
        if (this.reconnectHandler) {
            clearTimeout(this.reconnectHandler);
        }

        const appParams = this.application.getAppUrlParams();
        const apiUrl: string = this.application.getApiUrl();
        const appLang: string = this.application.getLanguage();

        // add app fixtures (needed for the acceptance tests)
        const fixtures = this.persistentStorage.getValue('fixtures', null)
            ? this.persistentStorage.getValue('fixtures') // use dynamic fixtures
            : (appParams['fixtures'] ? appParams['fixtures'] : null); // use startup fixtures

        let url = this.auth.isAuthenticated()
            ? apiUrl + `/server-events/user/${this.auth.getToken()}/?api-language=${appLang}`
            : apiUrl + `/server-events/?api-language=${appLang}`;


        // append fixtures to each SE connection
        if (fixtures) {
            url += '&fixtures=' + fixtures;
        }

        // init connection
        this.eventSource = new EventSource(url);
        this.eventSource.onmessage = (response: IServerEventsResponse) => this.serverEventsData$.next(JSON.parse(response.data));

        // connection error
        this.eventSource.onerror = (e) => {
            // init only once (needed for the acceptance tests)
            if (appParams['se_init_only']) {
                e.target.close();

                return;
            }

            if (e.readyState != EventSource.CONNECTING) {
                this.ngZone.runOutsideAngular(() => 
                        this.reconnectHandler = setTimeout(() => this.restart(), this.reconnectTimeout));
            }
        };
    }
}
