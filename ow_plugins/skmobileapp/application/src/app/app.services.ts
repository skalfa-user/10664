import { MissingTranslationHandler } from 'ng2-translate/ng2-translate';
import { ErrorHandler } from '@angular/core';
import { TranslateService } from 'ng2-translate';
import { ModalController, ActionSheetController } from 'ionic-angular';

// services
import { ApplicationService } from 'services/application';
import { AuthService } from 'services/auth';
import { PersistentStorageService } from 'services/persistent-storage';
import { persistentStorageFactory } from 'services/persistent-storage/factory';
import { JwtService } from 'services/jwt';
import { BootstrapService } from 'services/bootstrap';
import { SecureHttpService } from 'services/http';
import { SiteConfigsService } from 'services/site-configs';
import { I18nService } from 'services/i18n';
import { MissingTranslations } from 'services/i18n/missing.translations'
import { questionManagerFactory } from 'services/questions/factory';
import { QuestionManager } from 'services/questions/manager';
import { ServerEventsService } from 'services/server-events';
import { PermissionsService } from 'services/permissions';
import { UserService } from 'services/user';
import { GuestsService } from 'services/guests';
import { MessagesService } from 'services/messages';
import { MatchActionsService } from 'services/match-actions';
import { CompatibleUsersService } from 'services/compatible-users';
import { HotListService } from 'services/hot-list';
import { VideoImService } from 'services/video-im';
import { MatchedUsersService } from 'services/matched-users';
import { StringUtilsService } from 'services/string-utils';
import { DateUtilsService } from 'services/date-utils';
import { FileUploaderService } from 'services/file-uploader';
import { PaymentsService } from 'services/payments';
import { AppErrorHandlerService } from 'services/error-handler';
import { AdMobService } from 'services/admob';
import { PushNotificationsService } from 'services/push';
import { VerifyPhoneService } from 'services/verify-phone';
import { VideoUploaderService } from 'services/video-uploader';

// validators
import { Validators } from 'services/questions/validators';
import { UserEmailValidator } from 'services/questions/validators/user.email';
import { UserNameValidator } from 'services/questions/validators/user.name';
import { RequireValidator } from 'services/questions/validators/require';
import { EmailValidator } from 'services/questions/validators/email';
import { UrlValidator } from 'services/questions/validators/url';
import { MinLengthValidator } from 'services/questions/validators/min.length';
import { MaxLengthValidator } from 'services/questions/validators/max.length';
import { RegExpValidator } from 'services/questions/validators/reg.exp';

// load app config
import { APPLICATION_CONFIG, APPLICATION_CONFIG_PROVIDER } from './app.config';

// services list
export const list = [{
        provide: APPLICATION_CONFIG_PROVIDER,
        useValue: APPLICATION_CONFIG
    }, {
        provide: PersistentStorageService,
        useFactory: persistentStorageFactory
    }, {
        provide: MissingTranslationHandler,
        useClass: MissingTranslations
    }, {
        provide: ErrorHandler,
        useClass: AppErrorHandlerService
    }, {
        provide: QuestionManager,
        useFactory: questionManagerFactory,
        deps: [
            ModalController,
            TranslateService,
            ActionSheetController
        ]
    },
    AppErrorHandlerService,
    JwtService,
    ApplicationService,
    AuthService,
    BootstrapService,
    SecureHttpService,
    I18nService,
    SiteConfigsService,
    ServerEventsService,
    PermissionsService,
    UserService,
    GuestsService,
    MessagesService,
    MatchActionsService,
    CompatibleUsersService,
    HotListService,
    VideoImService,
    MatchedUsersService,
    StringUtilsService,
    DateUtilsService,
    FileUploaderService,
    PaymentsService,
    AdMobService,
    PushNotificationsService,
    Validators,
    UserEmailValidator,
    UserNameValidator,
    RequireValidator,
    EmailValidator,
    UrlValidator,
    MinLengthValidator,
    MaxLengthValidator,
    RegExpValidator,
    VerifyPhoneService,
    VideoUploaderService
];
