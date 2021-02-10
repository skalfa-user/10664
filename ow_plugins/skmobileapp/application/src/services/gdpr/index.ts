import { Injectable } from '@angular/core';
import { Observable } from 'rxjs/Observable';

// services
import { SecureHttpService } from 'services/http';

@Injectable()
export class GdprService {

    /**
     * Constructor
     */
    constructor (private http: SecureHttpService) {}

    /**
     * Request user data to download
     */
    requestUserDataToDownload(): Observable<any> {
        const request: Observable<any> = this.http.post('/gdpr/downloads');

        return request;
    }

    /**
     * Request user data to delete
     */
    requestUserDataToDelete(): Observable<any> {
        const request: Observable<any> = this.http.post('/gdpr/deletions');

        return request;
    }

    /**
     * Send message to admin
     */
    sendMessageToAdmin(message: string): Observable<any> {
        const request: Observable<any> = this.http.post('/gdpr/messages', {
            message: message
        });

        return request;
    }
}
