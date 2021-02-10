<?php

use Silex\Application as SilexApplication;
use Symfony\Component\HttpFoundation\RequestMatcher;
use Symfony\Component\Security\Core\Encoder\PlaintextPasswordEncoder;
use Skadate\Mobile\Controller\I18n as I18nController;
use Skadate\Mobile\Controller\Configs as ConfigsController;
use Skadate\Mobile\Controller\Devices as DevicesController;
use Skadate\Mobile\Controller\Avatars as AvatarsController;
use Skadate\Mobile\Controller\Photos as PhotosController;
use Skadate\Mobile\Controller\Users as UsersController;
use Skadate\Mobile\Controller\Tinder as TinderController;
use Skadate\Mobile\Controller\HotList as HotListController;
use Skadate\Mobile\Controller\MatchActions as MatchActionsController;
use Skadate\Mobile\Controller\MatchedUsers as MatchedUsersController;
use Skadate\Mobile\Controller\QuestionsData as QuestionsDataController;
use Skadate\Mobile\Controller\ServerEvents as ServerEventsController;
use Skadate\Mobile\Controller\Login as LoginController;
use Skadate\Mobile\Controller\ForgotPassword as ForgotPasswordController;
use Skadate\Mobile\Controller\VerifyEmail as VerifyEmailController;
use Skadate\Mobile\Controller\Validators as ValidatorsController;
use Skadate\Mobile\Controller\JoinQuestions as JoinQuestionsController;
use Skadate\Mobile\Controller\CompleteProfileQuestions as CompleteProfileQuestionsController;
use Skadate\Mobile\Controller\Logs as LogsController;
use Skadate\Mobile\Controller\EditQuestions as EditQuestionsController;
use Skadate\Mobile\Controller\SearchQuestions as SearchQuestionsController;
use Skadate\Mobile\Controller\Mailbox as MailboxController;
use Skadate\Mobile\Controller\UserGenders as UserGendersController;
use Skadate\Mobile\Controller\LocationAutocomplete as LocationAutocompleteController;
use Skadate\Mobile\Controller\Flags as FlagsController;
use Skadate\Mobile\Controller\Bookmarks as BookmarksController;
use Skadate\Mobile\Controller\Guests as GuestsController;
use Skadate\Mobile\Controller\Permissions as PermissionsController;
use Skadate\Mobile\Controller\InApps as InAppsController;
use Skadate\Mobile\Controller\Memberships as MembershipsController;
use Skadate\Mobile\Controller\Credits as CreditsController;
use Skadate\Mobile\Controller\UserLocations as UserLocationsController;
use Skadate\Mobile\Controller\VideoIm as VideoImController;
use Skadate\Mobile\Controller\Gdpr as GdprController;
use Skadate\Mobile\Provider\User as UserProvider;
use Skadate\Mobile\Middleware\ApiLanguage as ApiLanguageMiddleware;
use Skadate\Mobile\Middleware\Maintenance as MaintenanceMiddleware;
use Skadate\Mobile\Middleware\UserStatus as UserStatusMiddleware;
use Skadate\Mobile\Middleware\ContentEncoding as ContentEncodingMiddleware;
use Skadate\Mobile\Middleware\UserOnline as UserOnlineMiddleware;
use Skadate\Mobile\Controller\BillingGateways as BillingGatewaysController;
use Skadate\Mobile\Controller\MobileBilling as MobileBillingController;
use Skadate\Mobile\Controller\WebPushes as WebPushesController;
use Skadate\Mobile\Controller\Preferences as PreferencesController;
use Skadate\Mobile\Controller\EmailNotifications as EmailNotificationsController;
use Skadate\Mobile\Controller\FireBaseLogin as FireBaseLoginController;
use Skadate\Mobile\Controller\SMSVerification as SMSVerificationController;
use Skadate\Mobile\Controller\Videos as VideosController;

/**
 * Copyright (c) 2016, Skalfa LLC
 * All rights reserved.
 *
 * ATTENTION: This commercial software is intended for use with Oxwall Free Community Software http://www.oxwall.com/
 * and is licensed under Oxwall Store Commercial License.
 *
 * Full text of this license can be found at http://developers.oxwall.com/store/oscl
 */
class SKMOBILEAPP_CTRL_Api extends OW_ActionController
{
    /**
     * Jwt life time
     */
    const JWT_LIFE_TIME =  86400 * 360 * 999; // 999 years

    /**
     * Api index
     */
    public function index()
    {
        $apiRoute = OW::getRouter()->getRoute('skmobileapp.api');
        $apiUri  = preg_replace('|^.+' . $apiRoute->getRoutePath() . '|i', '', $_SERVER['REQUEST_URI']);
        $uriPath = parse_url ( $apiUri,  PHP_URL_PATH);

        // checking for the trailing slash
        if ( substr($uriPath, -1) != '/') {
            // redirect the route using the trailing slash
            $this->redirect(OW_URL_HOME . $apiRoute->
                getRoutePath() . $uriPath . '/?' . parse_url ( $apiUri,  PHP_URL_QUERY));
        }

        // clear route
        $_SERVER['REQUEST_URI'] = $apiUri;

        // init silex framework
        $app = new SilexApplication();
        $app['debug'] = OW_DEBUG_MODE !== false;
        $app['security.jwt'] = [
            'secret_key' => OW_PASSWORD_SALT,
            'life_time'  => self::JWT_LIFE_TIME,
            'options'    => [ // jwt header options
                'username_claim' => 'name',
                'header_name' => 'jwt',
                'token_prefix' => 'Bearer',
            ]
        ];

        $app['users'] = function () use ($app) {
            return new UserProvider($app);
        };

        $app['security.encoder.skadate'] = function () {
            return new PlaintextPasswordEncoder();
        };

        // anybody can call these controllers
        $publicControllers = [
            'validators',
            'login',
            'firebase',
            'forgot-password',
            'join-questions',
            'user-genders',
            'configs',
            'i18n',
            'check-api',
            'location-autocomplete',
            'server-events',
            'logs',
            'web-pushes'
        ];

        $app['security.firewalls'] = [
            'public' => [
                'pattern' => new RequestMatcher(implode('|', $publicControllers)), // not secured controllers
                'anonymous' => true
            ],
            'avatars' => [
                'pattern' => new RequestMatcher('/avatars/$', null, ['POST']), // anonymous can create avatar only
                'anonymous' => true
            ],
            'users' => [
                'pattern' => new RequestMatcher('/users/$', null, ['POST']), // anonymous can create profile only
                'anonymous' => true
            ],
            'secured' =>[ // all other are secured
                'pattern' => new RequestMatcher('^.*$', null, ['GET', 'POST', 'PUT', 'DELETE']),
                'users' => $app['users'], // users provider
                'jwt' => [
                    'use_forward' => true,
                    'require_previous_session' => false,
                    'stateless' => true,
                ]
            ]
        ];

        $app['startup.controllers'] = [
            'check-api',
            'configs',
            'i18n'
        ];

        // init middlewares
        $mddlewares = [
            new ApiLanguageMiddleware($app),
            new MaintenanceMiddleware($app),
            new UserStatusMiddleware($app),
            new UserOnlineMiddleware($app)
        ];

        if ( OW_DEBUG_MODE === false ) 
        {
            $mddlewares[] = new ContentEncodingMiddleware($app);
        }

        foreach ( $mddlewares as $mddleware ) 
        {
            if ( $mddleware->callBefore() ) 
            {
                $app->before($mddleware->getMiddleware(), $mddleware->getPriority());

                continue;
            }

            $app->after($mddleware->getMiddleware(), $mddleware->getPriority());
        }

        // init silex core modules
        $app->register(new JDesrosiers\Silex\Provider\CorsServiceProvider());
        $app->register(new Silex\Provider\SecurityJWTServiceProvider());
        $app->register(new Silex\Provider\SecurityServiceProvider());
        $app->after($app['cors']);

        // check api
        $app->get('/check-api/', function() use($app) {
            return $app->json([
                'status' => 'ok',
                'url' => OW_URL_HOME,
                'api' => SKMOBILEAPP_BOL_Service::API_VERSION
            ]);
        });

        // init controllers
        $app->mount('/validators', new ValidatorsController);
        $app->mount('/login', new LoginController);
        $app->mount('/forgot-password', new ForgotPasswordController);
        $app->mount('/verify-email', new VerifyEmailController);
        $app->mount('/complete-profile-questions', new CompleteProfileQuestionsController);
        $app->mount('/join-questions', new JoinQuestionsController);
        $app->mount('/edit-questions', new EditQuestionsController);
        $app->mount('/search-questions', new SearchQuestionsController);
        $app->mount('/users', new UsersController);
        $app->mount('/tinder-users', new TinderController);
        $app->mount('/hotlist-users', new HotListController);
        $app->mount('/questions-data', new QuestionsDataController);
        $app->mount('/user-genders', new UserGendersController);
        $app->mount('/user-locations', new UserLocationsController);
        $app->mount('/math-actions', new MatchActionsController);
        $app->mount('/avatars', new AvatarsController);
        $app->mount('/photos', new PhotosController);
        $app->mount('/inapps', new InAppsController);
        $app->mount('/memberships', new MembershipsController);
        $app->mount('/credits', new CreditsController);
        $app->mount('/configs', new ConfigsController);
        $app->mount('/devices', new DevicesController);
        $app->mount('/i18n', new I18nController);
        $app->mount('/location-autocomplete', new LocationAutocompleteController);
        $app->mount('/flags', new FlagsController);
        $app->mount('/bookmarks', new BookmarksController);
        $app->mount('/permissions', new PermissionsController);
        $app->mount('/server-events', new ServerEventsController);
        $app->mount('/matched-users', new MatchedUsersController);
        $app->mount('/mailbox', new MailboxController);
        $app->mount('/guests', new GuestsController);
        $app->mount('/logs', new LogsController);
        $app->mount('/video-im', new VideoImController);
        $app->mount('/gdpr', new GdprController);
        $app->mount('/billing-gateways', new BillingGatewaysController);
        $app->mount('/mobile-billings', new MobileBillingController);
        $app->mount('/web-pushes', new WebPushesController);
        $app->mount('/preferences', new PreferencesController);
        $app->mount('/email-notifications', new EmailNotificationsController);
        $app->mount('/firebase', new FireBaseLoginController);
        $app->mount('/sms-verifications', new SMSVerificationController);
        $app->mount('/videos', new VideosController);

        $app->run();

        exit;
    }
}
