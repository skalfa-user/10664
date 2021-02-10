// shared components
import { QuestionComponent } from 'shared/components/question';
import { LocationAutocompleteComponent } from 'shared/components/location-autocomplete';
import { UserSearchAutocompleteComponent } from 'shared/components/user-search-autocomplete';
import { PagePreloaderComponent } from 'shared/components/page-preloader';
import { FileUploaderComponent } from 'shared/components/file-uploader';
import { AvatarComponent } from 'shared/components/avatar';
import { CustomPageComponent } from 'shared/components/custom-page';
import { MatchActionsComponent } from 'shared/components/match-actions';
import { PermissionsComponent } from 'shared/components/permissions';
import { SkeletonPreloaderComponent } from 'shared/components/skeleton-preloader';
import { PhotosViewerComponent } from 'shared/components/photos-viewer';
import { ImageComponent } from 'shared/components/image';
import { LocationComponent } from 'shared/components/location';
import { FlagComponent } from 'shared/components/flag';
import { InstallPwaBannerComponent } from 'shared/components/install-pwa-banner';
import { DownloadPwaComponent } from 'shared/components/download-pwa';
import { SassHelperComponent } from 'pages/payments/gateways/not-redirectable/stripe/sass-helper';
import { ViewVideoComponent } from 'shared/components/view-video';

// shared directives
import { AutoSizeDirective } from 'shared/directives/auto-size';
import { ChangeFocusByEnterDirective } from 'shared/directives/change-focus-by-enter';

// pipes
import { TrustHtmlPipe } from 'shared/pipes/trust-html';
import { TrustUrlPipe } from 'shared/pipes/trust-url';
import { TrustStylePipe } from 'shared/pipes/trust-style';
import { UrlifyPipe } from 'shared/pipes/urlify';
import { NlbrPipe } from 'shared/pipes/nlbr';

// services list
export const declarationsList = [
    QuestionComponent,
    LocationAutocompleteComponent,
    PagePreloaderComponent,
    FileUploaderComponent,
    AvatarComponent,
    CustomPageComponent,
    MatchActionsComponent,
    PermissionsComponent,
    SkeletonPreloaderComponent,
    PhotosViewerComponent,
    ImageComponent,
    LocationComponent,
    FlagComponent,
    InstallPwaBannerComponent,
    DownloadPwaComponent,
    AutoSizeDirective,
    ChangeFocusByEnterDirective,
    TrustHtmlPipe,
    TrustUrlPipe,
    TrustStylePipe,
    UrlifyPipe,
    NlbrPipe,
    SassHelperComponent,
    ViewVideoComponent,
    UserSearchAutocompleteComponent
];

export const entryComponents = [
    LocationAutocompleteComponent,
    CustomPageComponent,
    PhotosViewerComponent,
    FlagComponent,
    DownloadPwaComponent,
    ViewVideoComponent,
    UserSearchAutocompleteComponent
];
