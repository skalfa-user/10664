import { Observable } from 'rxjs/Observable';
import { IMapType } from 'store/types';

// services
import { SiteConfigsService } from 'services/site-configs';

export class SiteConfigsServiceFake extends SiteConfigsService {    
    loadConfigs(): Observable<IMapType<any>> {
        return Observable.empty();
    }

    setConfigs(configs: IMapType<any>): void {}

    watchConfig(configId: string | number): Observable<any> {
        return Observable.empty();
    }

    getConfig(configId: string | number): any {
        return undefined;
    }
}
