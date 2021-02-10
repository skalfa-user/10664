import { Injectable } from '@angular/core';
import { AdMobFree, AdMobFreeBannerConfig } from '@ionic-native/admob-free';
import { Observable } from 'rxjs/Observable';

@Injectable()
export class AdMobService
{
    isCreated: boolean = false;

    /**
     * Constructor
     */
    constructor(private adMob: AdMobFree) {}

    /**
     * Show banner
     */
    showBanner(): Observable<any> {
        return  Observable.fromPromise(this.adMob.banner.show());
    }

    /**
     * Hide banner
     */
    hideBanner(): Observable<any> {
        return Observable.fromPromise(this.adMob.banner.hide());
    }

    /**
     * Remove banner
     */
    removeBanner(): Observable<any> {
        const removeBanner$ = Observable.fromPromise(this.adMob.banner.remove());

        removeBanner$.subscribe(() =>  {
            this.isCreated = false;
        })

        return removeBanner$;
    }

    /**
     * Create banner
     */
    createBanner(admobId: string): Observable<any> {
        const config: AdMobFreeBannerConfig = {
            id: admobId,
            overlap: false,
            autoShow: false
        };

        this.adMob.banner.config(config);

        const createBanner$ = Observable.fromPromise(this.adMob.banner.prepare());

        createBanner$.subscribe(() =>  {
            this.isCreated = true;
        })

        return createBanner$;
    }

    /**
     * Is banner created
     */
    isBannerCreated(): boolean {
        return this.isCreated;
    }
}
