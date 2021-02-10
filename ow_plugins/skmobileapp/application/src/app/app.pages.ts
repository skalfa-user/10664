// pages
import { App } from 'app/app.component';
import { AppUrlPage } from 'pages/app-url';
import { LoginPage } from 'pages/user/login';
import { AppNoInternetPage } from 'pages/app-no-internet';
import { AppErrorPage } from 'pages/app-error';
import { UserDisapprovedPage } from 'pages/user/disapproved';
import { VerifyEmailCheckCodePage } from 'pages/user/verify-email/check-code';
import { VerifyEmailCheckEmailPage } from 'pages/user/verify-email/check-email';
import { CompleteProfilePage } from 'pages/user/complete-profile';
import { CompleteAccountTypePage } from 'pages/user/complete-account-type';
import { AppMaintenancePage } from 'pages/app-maintenance';
import { DashboardPage } from 'pages/dashboard';
import { JoinInitialPage } from 'pages/user/join/initial';
import { JoinQuestionsPage } from 'pages/user/join/questions';
import { ForgotPasswordCheckEmailPage } from 'pages/user/forgot-password/check-email';
import { ForgotPasswordCheckCodePage } from 'pages/user/forgot-password/check-code';
import { ForgotPasswordNewPasswordPage } from 'pages/user/forgot-password/new-password';
import { AppSettingsPage } from 'pages/app-settings/settings';
import { EditUserQuestionsPage } from 'pages/user/edit/questions';
import { EditUserPhotosPage } from 'pages/user/edit/photos';
import { GuestsPage } from 'pages/user/guests';
import { BookmarksPage } from 'pages/user/bookmarks';
import { InitialPaymentsPage } from 'pages/payments/initial';
import { ViewMembershipInAppPage } from 'pages/payments/view/inapp';
import { ViewMembershipMobilePage } from 'pages/payments/view/mobile';
import { CreditsInfoPage } from 'pages/payments/credits-info';
import { ViewPaymentsGatewaysPage } from 'pages/payments/gateways/initial';
import { RedirectablePaymentsGatewayPage } from 'pages/payments/gateways/redirectable';
import { NotRedirectablePaymentsGatewayPage } from 'pages/payments/gateways/not-redirectable';
import { NotRedirectablePaymentsGatewayPageStripe, } from 'pages/payments/gateways/not-redirectable/stripe';
import { ProfileViewPage } from 'pages/profile';
import { CompatibleUsersPage } from 'pages/user/compatible-users';
import { MessagesPage } from 'pages/messages';
import { GdprUserDataPage } from 'pages/app-settings/gdpr/gdpr-user-data';
import { GdprThirdPartyPage } from 'pages/app-settings/gdpr/gdpr-third-party';
import { EmailNotificationsPage } from 'pages/app-settings/email-notifications';
import { PreferencesPage } from 'pages/app-settings/preferences';
import { VerifyPhoneCheckNumberPage } from 'pages/user/verify-phone/check-number';
import { VerifyPhoneCheckCodePage } from 'pages/user/verify-phone/check-code';
import { VideoUploadPage } from 'pages/user/video/upload';

// page components
import { ProfileComponent } from 'pages/dashboard/components/profile';
import { HotListComponent } from 'pages/dashboard/components/hot-list';
import { ConversationsComponent } from 'pages/dashboard/components/conversations';
import { UserSearchFilterComponent } from 'pages/dashboard/components/search/components/search-filter';
import { SearchComponent } from 'pages/dashboard/components/search';
import { TinderComponent } from 'pages/dashboard/components/tinder';
import { DashboardTabsComponent } from 'pages/dashboard/components/tabs';
import { MatchedUserPageComponent } from 'pages/dashboard/components/matched-user';
import { VideoImConfirmationComponent } from 'pages/dashboard/components/video-im/confirmation';
import { VideoImChatComponent } from 'pages/dashboard/components/video-im/chat';
import { VideoImTimerComponent } from 'pages/dashboard/components/video-im/chat/components/timer';
import { PlainMessageComponent } from 'pages/messages/components/plain-message';
import { WinkMessageComponent } from 'pages/messages/components/wink-message';
import { OembedMessageComponent } from 'pages/messages/components/oembed-message';
import { GdprMessageComponent } from 'pages/app-settings/gdpr/gdpr-third-party/components/gdpr-message';
import { MembershipsComponent } from 'pages/payments/initial/components/memberships';
import { InappCreditsComponent } from 'pages/payments/initial/components/inapp-credits';
import { MobileCreditsComponent } from 'pages/payments/initial/components/mobile-credits';
import { AuthProvidersComponent } from 'pages/user/login/components/auth-providers';

export const declarationsList = [
    App,
    AppUrlPage,
    LoginPage,
    AppNoInternetPage,
    AppErrorPage,
    UserDisapprovedPage,
    VerifyEmailCheckCodePage,
    VerifyEmailCheckEmailPage,
    CompleteProfilePage,
    CompleteAccountTypePage,
    AppMaintenancePage,
    DashboardPage,
    JoinInitialPage,
    JoinQuestionsPage,
    ForgotPasswordCheckEmailPage,
    ForgotPasswordCheckCodePage,
    ForgotPasswordNewPasswordPage,
    AppSettingsPage,
    GuestsPage,
    EditUserQuestionsPage,
    EditUserPhotosPage,
    BookmarksPage,
    InitialPaymentsPage,
    ViewMembershipInAppPage,
    ViewMembershipMobilePage,
    CreditsInfoPage,
    ViewPaymentsGatewaysPage,
    RedirectablePaymentsGatewayPage,
    NotRedirectablePaymentsGatewayPage,
    NotRedirectablePaymentsGatewayPageStripe,
    ProfileViewPage,
    CompatibleUsersPage,
    MessagesPage,
    GdprUserDataPage,
    GdprThirdPartyPage,
    EmailNotificationsPage,
    PreferencesPage,
    ProfileComponent,
    HotListComponent,
    ConversationsComponent,
    UserSearchFilterComponent,
    SearchComponent,
    TinderComponent,
    DashboardTabsComponent,
    MatchedUserPageComponent,
    VideoImConfirmationComponent,
    VideoImChatComponent,
    VideoImTimerComponent,
    PlainMessageComponent,
    WinkMessageComponent,
    OembedMessageComponent,
    GdprMessageComponent,
    MembershipsComponent,
    InappCreditsComponent,
    MobileCreditsComponent,
    AuthProvidersComponent,
    VerifyPhoneCheckNumberPage,
    VerifyPhoneCheckCodePage,
    VideoUploadPage
];

export const entryComponents = [
    App,
    AppUrlPage,
    LoginPage,
    AppNoInternetPage,
    AppErrorPage,
    UserDisapprovedPage,
    VerifyEmailCheckCodePage,
    VerifyEmailCheckEmailPage,
    CompleteProfilePage,
    CompleteAccountTypePage,
    AppMaintenancePage,
    DashboardPage,
    JoinInitialPage,
    JoinQuestionsPage,
    ForgotPasswordCheckEmailPage,
    ForgotPasswordCheckCodePage,
    ForgotPasswordNewPasswordPage,
    AppSettingsPage,
    EditUserQuestionsPage,
    EditUserPhotosPage,
    GuestsPage,
    BookmarksPage,
    InitialPaymentsPage,
    ViewMembershipInAppPage,
    ViewMembershipMobilePage,
    CreditsInfoPage,
    ViewPaymentsGatewaysPage,
    RedirectablePaymentsGatewayPage,
    NotRedirectablePaymentsGatewayPage,
    NotRedirectablePaymentsGatewayPageStripe,
    ProfileViewPage,
    CompatibleUsersPage,
    MessagesPage,
    GdprUserDataPage,
    GdprThirdPartyPage,
    EmailNotificationsPage,
    PreferencesPage,
    MatchedUserPageComponent,
    VideoImConfirmationComponent,
    VideoImChatComponent,
    UserSearchFilterComponent,
    GdprMessageComponent,
    VideoImConfirmationComponent,
    VideoImChatComponent,
    UserSearchFilterComponent,
    AuthProvidersComponent,
    VerifyPhoneCheckNumberPage,
    VerifyPhoneCheckCodePage,
    VideoUploadPage
];
