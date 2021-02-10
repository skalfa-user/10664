import { Injectable } from '@angular/core';
import { Observable } from 'rxjs/Observable';

// services
import { SecureHttpService } from 'services/http';

@Injectable()
export class LocationService {
    /**
     * Constructor
     */
    constructor(private http: SecureHttpService) {}

    /**
     * Load autocomplete
     */
    loadAutocomplete(query: string): Observable<Array<string>> {
        return this.http.get('/location-autocomplete', {
            q: query
        });
    }
}
