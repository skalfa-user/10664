import { Observable } from 'rxjs/Observable';
import 'rxjs/add/observable/empty' 

// services
import { ApplicationService } from 'services/application';

// store
import { IApplicationLocation } from 'store/states';

export class ApplicationServiceFake extends ApplicationService {
    getConfig(): string {
        return '';
    };

    getGenericApiUrl(): string {
        return '';
    }

    setGenericApiUrl(url: string): void {}

    getApiUri(): string {
        return '';
    }

    getApiUrl(): string {
        return '';
    }

    watchLanguage(): Observable<string> {
        return Observable.empty<string>();
    }

    getLanguage(): string {
        return '';
    }
 
    setLanguage(language: string): void {}

    setLanguageDirection(direction: string): void {}

    watchLanguageDirection(): Observable<string> {
        return Observable.empty<string>();
    }

    getLanguageDirection(): string {
        return '';
    }

    watchLocation(): Observable<IApplicationLocation> {
        return Observable.empty<IApplicationLocation>();
    }

    getLocation(): IApplicationLocation {
        return null;
    }

    setLocation(latitude: number, longitude: number): void {}

    resetApplication(): void {}

    isAppRunningInExternalBrowser(): boolean {
        return false;
    }

    getAppUrl(): string {
        return '';
    }

    getAppUuid(): string {
        return '';
    }
}
