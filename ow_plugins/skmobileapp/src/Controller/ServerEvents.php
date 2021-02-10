<?php

/**
 * Copyright (c) 2016, Skalfa LLC
 * All rights reserved.
 *
 * ATTENTION: This commercial software is intended for use with Oxwall Free Community Software http://www.oxwall.com/
 * and is licensed under Oxwall Store Commercial License.
 *
 * Full text of this license can be found at http://developers.oxwall.com/store/oscl
 */
namespace Skadate\Mobile\Controller;

use Silex\Application as SilexApplication;
use Skadate\Mobile\ServerEventsChannel\Configs as ConfigsChannel;
use Skadate\Mobile\ServerEventsChannel\Permissions as PermissionsChannel;
use Skadate\Mobile\ServerEventsChannel\Conversations as ConversationsChannel;
use Skadate\Mobile\ServerEventsChannel\MatchedUsers as MatchedUsersChannel;
use Skadate\Mobile\ServerEventsChannel\Messages as MessagesChannel;
use Skadate\Mobile\ServerEventsChannel\Guests as GuestsChannel;
use Skadate\Mobile\ServerEventsChannel\CompatibleUsers as CompatibleUsersChannel;
use Skadate\Mobile\ServerEventsChannel\HotList as HotListChannel;
use Skadate\Mobile\ServerEventsChannel\VideoIm as VideoImChannel;
use SKMOBILEAPP_CLASS_AuthAdapter;
use SKMOBILEAPP_BOL_Service;
use OW;

class ServerEvents extends Base
{
    /**
     * Max execution time
     */
    const MAX_EXECUTION_TIME = 30;

    /**
     * Detect chnages delay in seconds
     */
    const DETECT_CHANGES_DELAY_SEC = 5;

    /**
     * Channels
     *
     * @var array
     */
    protected $channels = [];

    /**
     * Stream headers
     *
     * @var array
     */
    protected $streamHeaders = [
        'Content-Type' => 'text/event-stream',
        'Cache-Control' => 'no-cache',
        'Connection' => 'keep-alive',
        'X-Accel-Buffering' => 'no',
        'Access-Control-Allow-Origin' => '*'
    ];

    /**
     * ServerEvents constructor.
     */
    public function __construct()
    {
        $this->channels = [
            new PermissionsChannel,
            new ConfigsChannel,
            new ConversationsChannel,
            new MatchedUsersChannel,
            new MessagesChannel,
            new GuestsChannel,
            new CompatibleUsersChannel,
            new HotListChannel,
            new VideoImChannel
        ];
    }

    /**
     * Start streaming
     *
     * @param integer $userId
     * @return callback
     */
    public function startStreaming($userId = null) {
        return function() use ($userId) {
            session_write_close();
            $endTime = time() + $this->getMaxExecutionTime();

            while (time() < $endTime) {
                // detect changes in channels
                foreach ($this->channels as $channel) {
                    $data = $channel->detectChanges($userId);

                    if (!is_null($data)) {
                        echo sprintf("data: %s\n", json_encode([
                            'channel' => $channel->getName(),
                            'data' => $data
                        ]));

                        echo sprintf("id: %s\n\n", date('c'));
                        ob_flush();
                        flush();
                    }
                }

                sleep(self::DETECT_CHANGES_DELAY_SEC);
            }
        };
    }

    /**
     * Connect methods
     *
     * @param SilexApplication $app
     * @return mixed
     */
    public function connect(SilexApplication $app)
    {
        // creates a new controller based on the default route
        $controllers = $app['controllers_factory'];

        // connect to server events (for guests)
        $controllers->get('/', function (SilexApplication $app) {
            return $app->stream($this->startStreaming(), 200, $this->streamHeaders);
        });

        // connect to server events (for logged users)
        $controllers->get('/user/{token}/', function (SilexApplication $app, $token) {
            $user = $app['security.jwt.encoder']->decode($token);

            // internal user authentication
            SKMOBILEAPP_BOL_Service::getInstance()->internalUserAuthenticate($user->id);

            return $app->stream($this->startStreaming($user->id), 200, $this->streamHeaders);
        });

        return $controllers;
    }

    /**
     * Get max execution time
     */
    protected function getMaxExecutionTime() {
        $iniMaxExecutionTime = (int) ini_get('max_execution_time');

        if ($iniMaxExecutionTime && self::MAX_EXECUTION_TIME > $iniMaxExecutionTime) {
            return $iniMaxExecutionTime;
        }

        return self::MAX_EXECUTION_TIME;
    }
}
