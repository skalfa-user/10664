import { Injectable } from '@angular/core';
import { Observable } from 'rxjs/Observable';
import { TranslateService } from 'ng2-translate';
import { Config as AppConfig } from 'ionic-angular';
import { Platform,  } from 'ionic-angular';

// services
import { SecureHttpService } from 'services/http';
import { ApplicationService } from 'services/application';

// responses
import { I18nResponse } from './responses';

@Injectable()
export class I18nService {
    /**
     * Constructor
     */
    constructor(
        private platform: Platform,
        private appConfig: AppConfig,
        private translate: TranslateService,
        private http: SecureHttpService, 
        private application: ApplicationService) {}

    /**
     * Reset lang
     */
    resetLang(): void {
        this.translate.resetLang(this.application.getLanguage()); // clear translations
    }
 
    /**
     * Load translations
     */
    loadTranslations(): Observable<I18nResponse> {
        const loadTranslations: Observable<I18nResponse> = this.http.get('/i18n/' + this.application.getLanguage());
 
        loadTranslations.subscribe(response => {
            // init translations
            this.translate.setTranslation(this.application.getLanguage(), response.translations);
            this.translate.use(this.application.getLanguage());
            this.appConfig.set('backButtonText', this.translate.instant('back'));

            const languageDirection: string = response.dir === this.application.rtlDirection
                ? this.application.rtlDirection
                : this.application.ltrDirection;

            // init platform lang
            this.platform.setDir((languageDirection === 'rtl' ? 'rtl' : 'ltr'), true);
            this.platform.setLang(this.application.getLanguage(), true);

            // set application language direction
            this.application.setLanguageDirection(languageDirection);
        }, () => {});

        return loadTranslations;
    }
}
