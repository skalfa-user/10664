import { Injectable } from '@angular/core';
import { Observable } from 'rxjs/Observable';

// services
import { SecureHttpService } from 'services/http';

@Injectable()
export class FlagService {
    /**
     * Constructor
     */
    constructor(private http: SecureHttpService) {}

    /**
     * Flag content
     */
    flagContent(identityId: number, entityType: string, reason: string): Observable<any> {
        const flag: Observable<any> = this.http.post('/flags', {
            identityId: identityId,
            entityType: entityType,
            reason: reason
        });

        return flag; 
    }
}
