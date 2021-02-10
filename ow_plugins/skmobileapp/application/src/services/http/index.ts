import { Injectable } from '@angular/core';
import { Headers, Response, RequestOptions } from '@angular/http';
import { Observable} from 'rxjs/Rx'
import { Subject } from 'rxjs/Subject';
import { ReplaySubject } from 'rxjs/ReplaySubject'
import { ProgressHttp } from 'angular-progress-http';

// services
import { ApplicationService } from 'services/application';
import { AuthService } from 'services/auth';
import { PersistentStorageService } from 'services/persistent-storage';

export interface IHttpError {
    code: number;
    type: string;
    shortDescription: string;
    description: string;
}

@Injectable()
export class SecureHttpService {
    public httpError$: Subject<IHttpError> = new Subject();
    protected authHeaderName: string = 'jwt';

    /**
     * Constructor
     */
    constructor(
        private persistentStorage: PersistentStorageService, 
        private application: ApplicationService,
        private http: ProgressHttp,
        private auth: AuthService) {}

    /**
     * Get
     */
    get(uri: string, params = {}, broadcastError: boolean = true): Observable<any> {
        const options = this.getRequestOptions(params);

        return this.http.get(this.getApiUrl(uri), options)
            .share()
            .map(res => res.json())
            .catch(err => {
                if (broadcastError) {
                    this.broadcastError(err);
                }

                return Observable.throw(err);
            });
    }

    /**
     * Post
     */
    post(uri: string, data = {}, params = {}, broadcastError: boolean = true, uploadProgress?: (percentage: number) => any): Observable<any> {
        const options = this.getRequestOptions(params);

        return this.http
            .withUploadProgressListener(progress => uploadProgress ? uploadProgress(progress.percentage) : null)
            .post(this.getApiUrl(uri), data, options)
            .share()
            .map(res => res.json())
            .catch(err => {
                if (broadcastError) {
                    this.broadcastError(err);
                }

                return Observable.throw(err);
            });
    } 

    /**
     * Put
     */
    put(uri: string, data = {}, params = {}, broadcastError: boolean = true): Observable<any> {
        const options = this.getRequestOptions(params);

        return this.http.put(this.getApiUrl(uri), data, options)
            .share()
            .map(res => res.json())
            .catch(err => {
                if (broadcastError) {
                    this.broadcastError(err);
                }

                return Observable.throw(err);
            });
    } 

    /**
     * Delete
     */
    delete(uri: string, params = {}, broadcastError: boolean = true): Observable<any> {
        const options = this.getRequestOptions(params);

        return this.http.delete(this.getApiUrl(uri), options)
            .share()
            .map(res => res.json())
            .catch(err => {
                if (broadcastError) {
                    this.broadcastError(err);
                }

                return Observable.throw(err);
            });
    } 

    /**
     * Validate api url
     */
    validateApiUrl(url: string): Observable<string> {
        const validationResult$: ReplaySubject<string> = new ReplaySubject(1);
        const connectionTimeout: number = 10000;

        // process url
        const domain = url.replace(/(^\w+:|^)\/\//, '').toLocaleLowerCase();

        let urls: Observable<any> = Observable.onErrorResumeNext(
            this.http.get('http://' + domain + this.application.getApiUri() + '/check-api/'),
            this.http.get('https://' + domain + this.application.getApiUri() + '/check-api/'),
            this.http.get('http://www.' + domain + this.application.getApiUri() + '/check-api/'),
            this.http.get('https://www.' + domain + this.application.getApiUri() + '/check-api/')
        );

        let definedUrl: string = null;

        urls.timeout(connectionTimeout).map(res => res.json()).subscribe(response => {
            if (response.status && response.status == 'ok' && response.url) {
                definedUrl = response.url;
            }
        }, 
        () => {
            validationResult$.next(definedUrl);
            validationResult$.complete();
        }, 
        () => {
            validationResult$.next(definedUrl);
            validationResult$.complete();
        });

        return validationResult$;
    }

    /**
     * Get api url
     */
    protected getApiUrl(uri: string): string {
        return this.application.getApiUrl() + uri + '/';
    }

    /**
     * Broadcast error
     */
    broadcastError(err: Response): void {
        let errorCode = err.status || 0;
        let errorType: string = '';
        let errorDescription: string = '';
        let errorDetails: any = '';
        let errorShortDescription: string = '';

        try {
            errorDetails = errorCode ? err.json() : '';
        }
        catch (e) {}

        if (errorDetails) {
            errorType = errorDetails.type ? errorDetails.type : '';
            errorShortDescription = errorDetails.shortDescription ? errorDetails.shortDescription : '';
            errorDescription = errorDetails.description ? errorDetails.description : '';
        }

        const errorResponse: IHttpError = {
            code: errorCode,
            type: errorType,
            shortDescription: errorShortDescription,
            description: errorDescription
        };

        this.httpError$.next(errorResponse);
    }

    /**
     * Get request options
     */
    protected getRequestOptions(params = {}): RequestOptions {
        const headers = new Headers();
        const appParams = this.application.getAppUrlParams();

        // init headers
        if (this.auth.getToken()) {
            headers.append(this.authHeaderName, `Bearer ${this.auth.getToken()}`);
        }

        headers.append('api-language', this.application.getLanguage());

        // add app fixtures (needed for the acceptance tests)
        const fixtures = this.persistentStorage.getValue('fixtures', null)
            ? this.persistentStorage.getValue('fixtures') // use dynamic fixtures
            : (appParams['fixtures'] ? appParams['fixtures'] : null); // use startup fixtures

        // append fixtures to each http query
        if (fixtures) {
            headers.append('fixtures', fixtures);
        }
 
        return new RequestOptions({
            headers: headers,
            params: params
        });
    }
}
