import { TestBed } from '@angular/core/testing';
import { AdMobFree } from '@ionic-native/admob-free';

// services
import { AdMobService } from './';

describe('Admob service', () => {
    // register service's fakes
    let fakeNativeAdmob: AdMobFree;

    let admob: AdMobService; // testable service 

    beforeEach(() => { 
        TestBed.configureTestingModule({
            providers: [
                AdMobFree
            ]}
        );

        // init service's fakes
        fakeNativeAdmob = TestBed.get(AdMobFree);

        // init the tastable service
        admob = new AdMobService(fakeNativeAdmob);
    });

    it('showBanner should return correct value', () => {
        const bannerResponse: null = null;

        // fake the method
        spyOn(fakeNativeAdmob.banner, 'show').and.returnValue(
            Promise.resolve(bannerResponse)
        );

        admob.showBanner().subscribe(response => {
            expect(fakeNativeAdmob.banner.show).toHaveBeenCalled();
            expect(bannerResponse).toEqual(response);
        });
    });

    it('hideBanner should return correct value', () => {
        const bannerResponse: null = null;

        // fake the method
        spyOn(fakeNativeAdmob.banner, 'hide').and.returnValue(
            Promise.resolve(bannerResponse)
        );

        admob.hideBanner().subscribe(response => {
            expect(fakeNativeAdmob.banner.hide).toHaveBeenCalled();
            expect(bannerResponse).toEqual(response);
        });
    });

    it('removeBanner should return correct value', () => {
        const bannerResponse: null = null;

        // fake the method
        spyOn(fakeNativeAdmob.banner, 'remove').and.returnValue(
            Promise.resolve(bannerResponse)
        );

        admob.removeBanner().subscribe(response => {
            expect(fakeNativeAdmob.banner.remove).toHaveBeenCalled();
            expect(bannerResponse).toEqual(response);
        });
    });

    it('createBanner should return correct value', () => {
        const admobId: string = 'test';
        const bannerResponse: null = null;

        // fake the method
        spyOn(fakeNativeAdmob.banner, 'prepare').and.returnValue(
            Promise.resolve(bannerResponse)
        );

        spyOn(fakeNativeAdmob.banner, 'config');

        admob.createBanner(admobId).subscribe(response => {
            expect(fakeNativeAdmob.banner.config).toHaveBeenCalledWith({
                id: admobId,
                overlap: false,
                autoShow: false
            });

            expect(fakeNativeAdmob.banner.prepare).toHaveBeenCalled();
            expect(bannerResponse).toEqual(response);
            expect(admob.isBannerCreated()).toBeTruthy();
        });
    });
});
