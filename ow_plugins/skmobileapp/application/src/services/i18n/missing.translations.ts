import { MissingTranslationHandler, MissingTranslationHandlerParams } from 'ng2-translate/ng2-translate';

export class MissingTranslations implements MissingTranslationHandler {
    /**
     * Fallback langs
     */
    protected fallbackLangs = {
        app_error_page_header: 'Oops. Something went wrong.',
        app_error_page_description: 'Please try again later',
        ok: 'OK',
        site_address_input: '://Site URL',
        site_address_input_require_error: 'You have to enter your site address',
        no_internet: 'No internet connection',
        site_address_error: 'Invalid site url',
        error_occurred: 'Error Occurred',
        next: 'Next',
        change_site_url_page_header: 'Change site url'
    };

    /**
     * Handle missing translations
     */
    handle(params: MissingTranslationHandlerParams) {
        console.warn(`Translation is missing for key: ${params.key}`);

        if (this.fallbackLangs[params.key]) {
            return this.fallbackLangs[params.key];
        }
    }
}
