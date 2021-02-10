import { NgModule, isDevMode } from '@angular/core';
import { BrowserAnimationsModule } from '@angular/platform-browser/animations';
import { IonicApp, IonicModule } from 'ionic-angular';
import { Keyboard } from '@ionic-native/keyboard';
import { TranslateModule } from 'ng2-translate/ng2-translate';
import { NgRedux, NgReduxModule, DevToolsExtension } from '@angular-redux/store';
import { BrowserModule } from '@angular/platform-browser';
import { App } from './app.component';
import { ProgressHttpModule } from 'angular-progress-http';
import { SwingModule } from 'angular2-swing';
import { VirtualScrollerModule } from 'ngx-virtual-scroller';
import { InAppPurchase } from '@ionic-native/in-app-purchase';
import { InAppPurchase2 } from '@ionic-native/in-app-purchase-2';
import { AdMobFree } from '@ionic-native/admob-free';
import { Push } from '@ionic-native/push';
import { Device } from '@ionic-native/device';
import { InAppBrowser } from '@ionic-native/in-app-browser';
import { AngularFireModule, FirebaseOptionsToken } from 'angularfire2';
import { AngularFireAuthModule } from 'angularfire2/auth';
import { Insomnia } from '@ionic-native/insomnia/ngx';
import { MediaCapture } from '@ionic-native/media-capture';
import { Camera } from '@ionic-native/camera';
import { Transfer } from '@ionic-native/transfer';
import { File } from '@ionic-native/file';

// store
import { rootReducer, IAppState, INITIAL_STATE } from 'store';

// services
import * as appServices from 'app/app.services';
import  { ApplicationService } from 'services/application';

// shared
import * as appShared from 'app/app.shared';

// pages
import * as appPages from 'app/app.pages';

@NgModule({
    declarations: [ // register all components
        ...appPages.declarationsList,
        ...appShared.declarationsList
    ],
    imports: [
        BrowserModule,
        BrowserAnimationsModule,
        SwingModule,
        IonicModule.forRoot(App, {
            scrollAssist: false,
            autoFocusAssist: false
        }),
        TranslateModule.forRoot(),
        NgReduxModule,
        ProgressHttpModule,
        VirtualScrollerModule,
        AngularFireModule.initializeApp({}),
        AngularFireAuthModule
    ],
    bootstrap: [IonicApp],
    entryComponents: [ //  components that are used in router configurations.
        ...appPages.entryComponents,
        ...appShared.entryComponents
    ],
    providers: [ // make globally registered
        Keyboard,
        InAppPurchase,
        InAppPurchase2,
        AdMobFree,
        Push,
        Device,
        Insomnia,
        MediaCapture,
        File,
        Transfer,
        Camera,
        InAppBrowser,
        {
            provide: 'virtualScroller.checkResizeInterval',
            useValue: 0
        },
        ...appServices.list,
        {
            provide: FirebaseOptionsToken,
            useFactory: (application) => {
                return {
                    apiKey: application.getConfig('firebaseApiKey'),
                    authDomain: application.getConfig('firebaseAuthDomain')
                };
            },
            deps: [ApplicationService]
        }
    ]
})

/**
 * App module
 */
export class AppModule {
    constructor(
        private ngRedux: NgRedux<IAppState>, 
        private devTools: DevToolsExtension) 
    {
        let enhancers = [];

        if (isDevMode() && devTools.isEnabled()) {
            enhancers = [ ...enhancers, this.devTools.enhancer() ];
        }

        this.ngRedux.configureStore(rootReducer, INITIAL_STATE, [], enhancers);
    }
}
