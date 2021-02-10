<?php

/**
 * Copyright (c) 2020, Skalfa LLC
 * All rights reserved.
 *
 * ATTENTION: This commercial software is intended for use with Oxwall Free Community Software http://www.oxwall.com/
 * and is licensed under Oxwall Store Commercial License.
 *
 * Full text of this license can be found at http://developers.oxwall.com/store/oscl
 */

class CVIDEOUPLOAD_BOL_Service
{
    use OW_Singleton;

    const VIDEO_STATUS_NOT_CONFIRMED = CVIDEOUPLOAD_BOL_VideoDao::VIDEO_STATUS_NOT_CONFIRMED;
    const VIDEO_STATUS_IN_PROCESS = CVIDEOUPLOAD_BOL_VideoDao::VIDEO_STATUS_IN_PROCESS;
    const VIDEO_STATUS_NOT_PROCESSED = CVIDEOUPLOAD_BOL_VideoDao::VIDEO_STATUS_NOT_PROCESSED;
    const VIDEO_STATUS_PROCESSED = CVIDEOUPLOAD_BOL_VideoDao::VIDEO_STATUS_PROCESSED;

    const VIDEO_AUTHORIZATION_APPROVAL = CVIDEOUPLOAD_BOL_VideoDao::VIDEO_AUTHORIZATION_APPROVAL;
    const VIDEO_AUTHORIZATION_APPROVED = CVIDEOUPLOAD_BOL_VideoDao::VIDEO_AUTHORIZATION_APPROVED;
    const VIDEO_AUTHORIZATION_BLOCKED = CVIDEOUPLOAD_BOL_VideoDao::VIDEO_AUTHORIZATION_BLOCKED;

    const VIDEO_PRIVACY_EVERYBODY = CVIDEOUPLOAD_BOL_VideoDao::VIDEO_PRIVACY_EVERYBODY;
    const VIDEO_PRIVACY_ONLY_OWNER = CVIDEOUPLOAD_BOL_VideoDao::VIDEO_PRIVACY_ONLY_OWNER;
    const VIDEO_PRIVACY_CERTAIN_USERS = CVIDEOUPLOAD_BOL_VideoDao::VIDEO_PRIVACY_CERTAIN_USERS;

    const EVENT_VIDEO_EDIT = 'cvideoupload.video_edit';
    const EVENT_VIDEO_CONVERTING_EDIT = 'cvideoupload.video_converting_edit';
    const EVENT_VIDEO_DELETE = 'cvideoupload.video_delete';

    const GET_PARAMS_TO_SUBSCRIBE = ['videoUpload' => 1];

    /**
     * Plugin key
     */
    const PLUGIN_KEY = 'cvideoupload';

    const ACTION_UPLOAD_VIDEO = 'upload_video';

    const LOCAL_PATH_PATTERN = '/^[^*?"<>|:]*$/';

    /**
     * File name length
     */
    const FILE_NAME_LENGTH = 16;

    /**
     * Min file size
     */
    const MIN_FILE_SIZE = 0.5;

    /**
     * Min video duration
     */
    const MIN_VIDEO_DURATION = 10;

    CONST SEARCH_LIMIT = 10;

    const VIDEO_MP4 = 'video/mp4';
    const VIDEO_FLV = 'video/x-flv';
    const VIDEO_MKV = 'video/x-matroska';
    const VIDEO_AVI = 'video/x-msvideo';
    const VIDEO_WMV_EXTRA = 'video/x-ms-wmv';
    const VIDEO_WMV = 'video/x-ms-asf';
    const VIDEO_WEBM = 'video/webm';
    const VIDEO_OGG = 'video/ogg';
    const VIDEO_OGV = 'video/ogv';
    const APPLICATION_OGG = 'application/ogg';
    const VIDEO_QUICKTIME = 'video/quicktime';
    const VIDEO_3GPP = 'video/3gpp';

    /**
     * Allowed mime types
     */
    const ALLOWED_MIME_TYPES = [
        self::VIDEO_MP4,
        self::VIDEO_FLV,
        self::VIDEO_MKV,
        self::VIDEO_WEBM,
        self::VIDEO_OGG,
        self::APPLICATION_OGG,
        self::VIDEO_OGV,
        self::VIDEO_QUICKTIME,
        self::VIDEO_3GPP,
        self::VIDEO_AVI,
        self::VIDEO_WMV,
        self::VIDEO_WMV_EXTRA
    ];

    /**
     * Convert mime types
     *
     * @var array
     */
    const CONVERT_MIME_TYPES = [
        self::VIDEO_MP4  => '.mp4',
        self::VIDEO_WEBM  => '.webm',
    ];

    /**
     * Cover image extension
     */
    const COVER_IMAGE_EXTENSION = '.jpg';

    /**
     * Cover image width
     */
    const COVER_IMAGE_WIDTH = 320;

    /**
     * Cover image height
     */
    const COVER_IMAGE_HEIGHT = 200;

    /**
     * @var OW_Config
     */
    private $config;

    /**
     * @var OW_Language
     */
    private $language;

    /**
     * Video DAO
     *
     * @var CVIDEOUPLOAD_BOL_VideoDao
     */
    private $videoDao;

    /**
     * @var CVIDEOUPLOAD_BOL_PrivacyDao
     */
    private $privacyDao;

    /**
     * Storage
     *
     * @var OW_Storage
     */
    private static $storage;

    /**
     * Class constructor
     */
    private function __construct()
    {
        $this->config = OW::getConfig();
        $this->language = OW::getLanguage();
        $this->videoDao = CVIDEOUPLOAD_BOL_VideoDao::getInstance();
        $this->privacyDao = CVIDEOUPLOAD_BOL_PrivacyDao::getInstance();
    }

    public function getLanguageText( $key, array $vars = null )
    {
        return $this->language->text(self::PLUGIN_KEY, $key, $vars);
    }

    public function setLanguageKeyForJs( $key )
    {
        $this->language->addKeyForJs(self::PLUGIN_KEY, $key);
    }

    public function getConfigValue($name)
    {
        return $this->config->getValue(self::PLUGIN_KEY, $name);
    }

    public function saveConfigValue($name, $value)
    {
        $this->config->saveConfig(self::PLUGIN_KEY, $name, $value );
    }

    public function deleteAllUserFriends( $userId )
    {
        $userId = intval($userId);

        $example = new OW_Example();
        $example->andFieldEqual('userId', $userId);

        $this->privacyDao->deleteByExample($example);
    }

    public function deleteAllFriendsById( $userId )
    {
        $userId = intval($userId);

        $example = new OW_Example();
        $example->andFieldEqual('friendId', $userId);

        $this->privacyDao->deleteByExample($example);
    }

    public function deleteUserAndFriends( $userId )
    {
        $userId = intval($userId);

        $this->deleteAllUserFriends($userId);
        $this->deleteAllFriendsById($userId);
    }

    public function getFriendByUserId($userId, $friendId)
    {
        $userId = intval($userId);
        $friendId = intval($friendId);

        $example = new OW_Example();
        $example->andFieldEqual('userId', $userId);
        $example->andFieldEqual('friendId', $friendId);

        return $this->privacyDao->findListByExample($example);

    }

    public function saveFriendByUserId($userId, $list)
    {
        $userId = intval($userId);

        if ( !empty($list) && !is_array($list) )
        {
            $list = array_map('intval', explode(',', $list ));
        }

        if ( !empty($list) )
        {
            $userIdList = BOL_UserService::getInstance()->findUserIdListByIdList($list);

            if ( !empty($userIdList) )
            {
                foreach ( $userIdList as $friendId )
                {
                    $this->addUserFriend($userId, $friendId);
                }
            }
        }
    }

    public function addUserFriend($userId, $friendId)
    {
        $userId = intval($userId);
        $friendId = intval($friendId);

        if ( empty($this->getFriendByUserId($userId, $friendId)) )
        {
            $privacy = new CVIDEOUPLOAD_BOL_Privacy();
            $privacy->setUserId($userId);
            $privacy->setFriendId($friendId);

            $this->privacyDao->save($privacy);
        }
    }

    public function getFriendListByUserId( $userId )
    {
        $userId = intval($userId);

        return $this->privacyDao->getFriendListByUserId($userId);
    }

    public function getUploadFileSizeInBytes()
    {
        $fileSize = floatval(OW::getConfig()->getValue(self::PLUGIN_KEY, 'fileSize'));

        if ( $fileSize )
        {
            return $fileSize;
        }

        return UTIL_File::getFileUploadServerLimitInBytes();
    }

    public function getUploadFileSizeInMegabytes()
    {
        return number_format( (float) $this->getUploadFileSizeInBytes() / 1024 / 1024, 1);
    }

    public function getMaxUploadFileSizeInMegabytes()
    {
        return number_format( (float) UTIL_File::getFileUploadServerLimitInBytes() / 1024 / 1024, 1);
    }

    public function convertMegabytesToBytes($megabytes)
    {
        return (float) $megabytes * 1024 * 1024;
    }

    public function getFFMPgPath()
    {
        return $this->getConfigValue('ffmpegPath');
    }

    public function isServerReadyForUploading()
    {
        if ( !function_exists('exec') )
        {
            return [false, $this->getLanguageText('enable_exec_function')];
        }

        $result = trim(exec($this->getFFMPgPath() . ' -version 2>&1'));

        if ( !$result )
        {
            return [false, $this->getLanguageText('ffmpeg_not_found')];
        }

        return [true, null];
    }

    public function getWatermarkUrl()
    {
        $url = OW::getPluginManager()->getPlugin(CVIDEOUPLOAD_BOL_Service::PLUGIN_KEY)->getStaticUrl() . 'images/default_watermark.png';

        if ( !empty($this->getConfigValue('watermark')) )
        {
            $path = $this->getWatermarkImageUserFilesDir($this->getConfigValue('watermark'));

            if ( file_exists($path) )
            {
                $url = $this->getWatermarkImageUserFilesUrl($this->getConfigValue('watermark'));
            }
        }

        return $url;
    }

    public function getWatermarkPath()
    {
        if ( !empty($this->getConfigValue('watermark')) )
        {
            $path = $this->getWatermarkImageUserFilesDir($this->getConfigValue('watermark'));

            if ( !file_exists($path) )
            {
                $path = null;
            }
        }

        if ( empty($path) )
        {
            $path = OW::getPluginManager()->getPlugin(self::PLUGIN_KEY)->getStaticDir() . 'images/default_watermark.png';
        }

        return $path;
    }

    public function getWatermarkData()
    {
        $watermarkData = [];

        if ( $this->getConfigValue('watermarkEnabled') )
        {
            $watermark = $this->getWatermarkPath();

            if ( !empty($watermark) )
            {
                $watermarkSize = getimagesize($watermark);

                $watermarkData['path'] = $watermark;
                $watermarkData['width'] = $watermarkSize[0];
                $watermarkData['height'] = $watermarkSize[1];
            }
        }

        return $watermarkData;
    }

    public function getWatermarkImageUserFilesUrl( $name )
    {
        return OW::getPluginManager()->getPlugin(self::PLUGIN_KEY)->getUserFilesUrl() . $name;
    }

    public function getWatermarkImageUserFilesDir( $name )
    {
        return OW::getPluginManager()->getPlugin(self::PLUGIN_KEY)->getUserFilesDir() . $name;
    }

    public function generateWatermarkName( $name )
    {
        return sprintf('%s.%s', 'watermark', $this->normalizeName($name));
    }

    public function normalizeName($name)
    {
        return pathinfo($name, PATHINFO_EXTENSION);
    }

    /**
     * @param $userId
     * @param bool $isProcessed
     *
     * @return CVIDEOUPLOAD_BOL_Video|null
     */
    public function findUserVideo($userId, $isProcessed = true)
    {
        return $this->videoDao->findUserVideo($userId, $isProcessed);
    }

    public function getSearchResult( $searchVal, $ignoreUser = [], $limit = self::SEARCH_LIMIT )
    {
        if ( strlen($searchVal = trim($searchVal)) === 0 )
        {
            return [];
        }

        if ( preg_match('/^(?:@)\S+/', $searchVal) === 1 )
        {
            switch ( $searchVal[0] )
            {
                case '@':

                    return $this->getSearchResultListByUsername(trim($searchVal, '@'), $ignoreUser, $limit);

                default:
                    return [];
            }
        }

        return [];
    }

    public function getSearchResultListByUsername( $username, $ignoreUser = [], $limit = self::SEARCH_LIMIT )
    {
        $questionName = OW::getConfig()->getValue('base', 'display_name_question');

        return $this->findUserIdListByName($questionName, $username, 0, $limit, $ignoreUser);
    }

    public function findUserIdListByName( $questionName, $username, $first, $count, $ignoreUser = [] )
    {
        $query = $this->findUserIdListByNameValuesQuery($questionName, $username, $ignoreUser);

        return OW::getDbo()->queryForColumnList($query . " LIMIT :first, :count ", array_merge(['first' => $first, 'count' => $count]));
    }

    public function findUserIdListByNameValuesQuery( $questionName, $username, $ignore = [] )
    {
        $prefix = 'qd';
        $counter = 0;
        $join = '';
        $where = '';

        if ( $questionName == 'realname' )
        {
            $queryStr = " LCASE(`" . $prefix . $counter . "`.`textValue`) LIKE '%" . OW::getDbo()->escapeString(strtolower($username)) . "%'";

            $join = " INNER JOIN `" . BOL_QuestionDataDao::getInstance()->getTableName() . "` `" . $prefix . $counter . "`
                                ON ( `user`.`id` = `" . $prefix . $counter . "`.`userId` AND `" . $prefix . $counter . "`.`questionName` = '" .  OW::getDbo()->escapeString($questionName) . "' AND " . $queryStr . " ) ";
            if ( !empty($ignore) )
            {
                $where = "AND `" . $prefix . $counter . "`.`userId` NOT IN (" . OW::getDbo()->mergeInClause($ignore) .") ";
            }
        }
        else
        {
            $notIn = '';
            if ( !empty($ignore) )
            {
                $notIn = " AND `user`.`id` NOT IN (" . OW::getDbo()->mergeInClause($ignore) .") ";
            }

            $where .= ' AND `user`.`' . OW::getDbo()->escapeString($questionName) . '` LIKE \'%' . OW::getDbo()->escapeString($username) . '%\'' . $notIn;
        }

        $queryParts = BOL_UserDao::getInstance()->getUserQueryFilter("user", "id", [
            "method" => "CVIDEOUPLOAD_BOL_Service::findUserIdListByNameValuesQuery"
        ]);

        $order = '`user`.`activityStamp` DESC';

        $usersTableName = "`" .  BOL_UserDao::getInstance()->getTableName() . "`";

        $distinct = 'DISTINCT';

        return "SELECT $distinct `user`.`id` FROM {$usersTableName} `user`
                {$join}
                {$queryParts["join"]}
                WHERE {$queryParts["where"]} {$where}
                ORDER BY {$order}";
    }

    public function isActionAllowed( $action, $allowPromotion = false )
    {
        // check admin status
        if ( OW::getUser()->isAdmin() )
        {
            return [true, null];
        }

        // check permissions
        $isAuthorized = OW::getUser()->isAuthorized(self::PLUGIN_KEY, $action);

        if ( $isAuthorized )
        {
            return [true, null];
        }

        // check the promotion status
        $promotedStatus = BOL_AuthorizationService::getInstance()->getActionStatus(self::PLUGIN_KEY, $action);
        $isPromoted = !empty($promotedStatus['status'])
            && $promotedStatus['status'] == BOL_AuthorizationService::STATUS_PROMOTED;

        $msg = $this->parserUrlByText($promotedStatus['msg']);

        if ( $isPromoted )
        {
            return [$allowPromotion, $msg];
        }

        return [false, $msg];
    }

    public function parserUrlByText( $msg )
    {
        if ( empty($msg) )
        {
            return $msg;
        }

        if ( $this->isRouterAdd('membership_subscribe') )
        {
            $urlMembership = OW::getRouter()->urlForRoute('membership_subscribe');
        }

        if ( $this->isRouterAdd('usercredits.buy_credits') )
        {
            $urlUserCredit = OW::getRouter()->urlForRoute('usercredits.buy_credits');
        }

        if ( empty($urlUserCredit) && empty($urlMembership) )
        {
            return $msg;
        }

        $urlMembership = !empty($urlMembership) ? $urlMembership : $urlUserCredit;
        $urlUserCredit = !empty($urlUserCredit) ? $urlUserCredit : $urlMembership;

        $regex = vsprintf('~%s|%s~', [$urlMembership, $urlUserCredit]);
        $msg = preg_replace_callback($regex, function($match)
        {
            return !empty($match[0]) ? $match[0] . '?' . http_build_query(self::GET_PARAMS_TO_SUBSCRIBE) : '';
        }, $msg);

        return $msg;
    }

    public function isRouterAdd( $router )
    {
        $routers = OW::getRouter()->getRoutes();

        if ( !empty($routers['staticRoutes'][$router]) || $routers['routes'][$router] )
        {
            return true;
        }

        return false;
    }

    public function isRequireApproval( $userId = 0 )
    {
        $contentType = BOL_ContentService::getInstance()->getContentTypeByEntityType(self::PLUGIN_KEY);

        if ( empty($contentType) )
        {
            return false;
        }

        if ( $userId )
        {
            $isModerator = OW::getUser()->isAuthorized($contentType['authorizationGroup'], null, ['userId' => $userId]);
        }
        else
        {
            $isModerator = OW::getUser()->isAuthorized($contentType['authorizationGroup']);
        }

        $isAdmin = OW::getUser()->isAdmin();

        if ( $isAdmin || $isModerator )
        {
            return false;
        }

        return true;
    }

    /**
     * @param $id
     *
     * @return CVIDEOUPLOAD_BOL_Video|null
     */
    public function findById( $id )
    {
        $id = intval($id);

        return $this->videoDao->findById($id);
    }

    public function findByIds( $ids )
    {
        if ( !is_array($ids) )
        {
            return [];
        }

        return $this->videoDao->findByIdList($ids);
    }

    public function trackActionAdd( $videoId )
    {
        $videoId = intval($videoId);

        if ( $videoId > 0 )
        {
            $video = $this->findById($videoId);

            if ( !empty($video) )
            {
                /* @var VIDEOI_BOL_Video $video */

                BOL_AuthorizationService::getInstance()->trackActionForUser($video->getUserId(),self::PLUGIN_KEY, self::ACTION_UPLOAD_VIDEO);
            }
        }
    }

    public function getAllowedFileTypes( $format = false )
    {
        $fileTypes = [];
        $typeInputValue = $this->getConfigValue('typeInput');

        if ( !empty($typeInputValue) )
        {
            $typeInputValue = unserialize($typeInputValue);
        }

        foreach ($typeInputValue as $fileType)
        {
            if ( $format )
            {
                $fileType = explode('/', $fileType);
                $fileTypes[] = $fileType[1];
            }
            else
            {
                $fileTypes[] = $fileType;
            }
        }

        return $fileTypes;
    }

    public function uploadVideoFile(array $file, $userId, $isAdmin = false)
    {
        $fileName = $this->generateFileName();
        $destination = $this->getPrivateVideosPath() . $fileName;

        // copy the file (prepare for converting)
        if ( move_uploaded_file($file['tmp_name'], $destination) )
        {
            // create a cover image
            $coverFileName = $fileName . self::COVER_IMAGE_EXTENSION;
            $coverFilePath = $this->getPrivateVideosPath() . $coverFileName;

            try
            {
                $this->generateImage($destination, $coverFilePath);
            }
            catch ( Exception $exception )
            {
                $command = sprintf('%s -ss 00:00:00 -i %s -vframes 1 %s', $this->getFFMPgPath(), $destination, $coverFilePath);
                exec($command);
            }

            if ( file_exists($coverFilePath) )
            {
                // resize cover image
                $image = new UTIL_Image($coverFilePath);
                $image->orientateImage()->resizeImage(self::COVER_IMAGE_WIDTH, self::COVER_IMAGE_HEIGHT, true)->saveImage($coverFilePath);

                // copy cover image
                if ( self::getStorage()->copyFile($coverFilePath, $this->getPublicVideosPath() . $coverFileName) )
                {
                    // delete old user videos
                    $this->deleteVideosByUserId($userId);

                    $videoDto = new CVIDEOUPLOAD_BOL_Video();
                    $videoDto->setFileName($fileName);
                    $videoDto->setReadableFileName($file['name']);
                    $videoDto->setFileType(mime_content_type($this->getPrivateVideosPath() . $fileName));
                    $videoDto->setTimestamp();
                    $videoDto->setStatus(CVIDEOUPLOAD_BOL_VideoDao::VIDEO_STATUS_NOT_CONFIRMED);
                    $videoDto->setAuthorization(CVIDEOUPLOAD_BOL_VideoDao::VIDEO_AUTHORIZATION_APPROVAL);
                    $videoDto->setPrivacy(CVIDEOUPLOAD_BOL_VideoDao::VIDEO_PRIVACY_EVERYBODY);
                    $videoDto->setUserId($userId);

                    $this->videoDao->save($videoDto);

                    // delete cover image
                    unlink($coverFilePath);

                    return $videoDto;
                }

                // delete cover image
                unlink($coverFilePath);
            }

            // delete uploaded video file
            unlink($destination);
        }

        return null;
    }

    public function getMimeTypeExtension($type)
    {
        $mimeTypes = self::CONVERT_MIME_TYPES;

        return in_array($type, $mimeTypes) ? $type : '';
    }

    public function convertToMp4Format($fileName, $fileType, $convertType)
    {
        $convertCommand = null;
        $newFileExtension = $this->getMimeTypeExtension($convertType);
        $privateVideosPath = $this->getPrivateVideosPath();

        $filePath = $privateVideosPath . $fileName;

        // TODO ПРОБУЕМ Через библиотекку создать видео
        try
        {
            $this->convertVideoMp4($filePath, $privateVideosPath . $fileName . $newFileExtension);
        }
        catch ( Exception $exception )
        {

            // convert the file
            switch ( $fileType )
            {
                case self::VIDEO_OGG :
                case self::APPLICATION_OGG :
                case self::VIDEO_OGV :
                case self::VIDEO_QUICKTIME:
                case self::VIDEO_WEBM :
                case self::VIDEO_3GPP:
                case self::VIDEO_MP4:
                case self::VIDEO_WMV_EXTRA:
                case self::VIDEO_WMV:
                case self::VIDEO_AVI:
                case self::VIDEO_FLV:
                case self::VIDEO_MKV:

                    $convertCommand = '%s -y -t %d -i %s -vcodec libx264 -pix_fmt yuv420p -profile:v baseline -level 3 -strict -2 %s';

                    break;

                default :
            }

            $watermark = $this->getWatermarkData();

            // convert video
            $convertCommandParse = vsprintf($convertCommand, [
                $this->getFFMPgPath(),
                intval($this->getConfigValue('maxDuration')),
                $filePath,
                $privateVideosPath . $fileName . $newFileExtension
            ]);

            // TODO если есть watermark
            if ( !empty($watermark) )
            {
                $ffprobe = '/home/sanjayp3/ffmpeg/ffprobe';
                //$ffprobe = str_replace('ffmpeg', 'ffprobe', $this->getFFMPgPath());
                $infoCommand = '%s -v error -show_entries stream=width,height -of default=noprint_wrappers=1:nokey=1 %s';

                $infoCommand = vsprintf($infoCommand, [
                    $ffprobe,
                    $filePath
                ]);

                exec($infoCommand, $videoStream);

                // TODO пробуем получить размер video
                if ( !empty($videoStream[0]) && !empty($videoStream[1]) )
                {
                    $width = $videoStream[0];
                    $height = $videoStream[1];
                    $path = $watermark['path'];
                    $overlayW = $watermark['width'];
                    $overlayH = $watermark['height'];

                    // TODO right bottom
                    $overlay = vsprintf('%s-%s-10:%s-%s-10', [$width, $overlayW, $height, $overlayH]);

                    $convertCommand .= ' -i %s -filter_complex "overlay=%s"';

                    $convertCommandParse = vsprintf($convertCommand, [
                        $this->getFFMPgPath(),
                        intval($this->getConfigValue('maxDuration')),
                        $filePath,
                        $privateVideosPath . $fileName . $newFileExtension,
                        $path,
                        $overlay
                    ]);
                }
            }
            
            $logger = OW::getLogger('cvideoupload');
            $logger->addEntry(print_r($convertCommandParse, true), 'ipn.data-array');
            $logger->writeLog();
            
            //$logger = OW::getLogger('cvideoupload');
            //$logger->addEntry(print_r(exec('/home/sanjayp3/ffmpeg/ffmpeg -y -t 100 -i /home/sanjayp3/public_html/ow_pluginfiles/custom-video-upload/yWuquw2sYRISUrah111 -vcodec libx264 -pix_fmt yuv420p -profile:v baseline -level 3 -strict -2 /home/sanjayp3/public_html/ow_pluginfiles/custom-video-upload/yWuquw2sYRISUrah111.mp4'), true), 'ipn.data-array');
            //$logger->writeLog();

            exec($convertCommandParse);
        }

        return $fileName . $newFileExtension;
    }


    public function convertTWebmFormat($fileName, $fileType, $convertType)
    {
        $convertCommand = null;
        $newFileExtension = $this->getMimeTypeExtension($convertType);
        $privateVideosPath = $this->getPrivateVideosPath();

        $filePath = $privateVideosPath . $fileName;

        // TODO ПРОБУЕМ Через библиотекку создать видео
        try
        {
            $this->convertVideoWeb($filePath, $privateVideosPath . $fileName . $newFileExtension);
        }
        catch ( Exception $exception )
        {
            // convert the file
            switch ( $fileType )
            {
                case self::VIDEO_OGG :
                case self::APPLICATION_OGG :
                case self::VIDEO_OGV :
                case self::VIDEO_QUICKTIME:
                case self::VIDEO_WEBM :
                case self::VIDEO_3GPP:
                case self::VIDEO_MP4:
                case self::VIDEO_WMV_EXTRA:
                case self::VIDEO_WMV:
                case self::VIDEO_AVI:
                case self::VIDEO_FLV:
                case self::VIDEO_MKV:

                    $convertCommand = '%s -y -t %d -i %s -c:v libvpx-vp9 -crf 30 -b:v 0 -b:a 128k -c:a libopus %s 2>&1';

                    break;

                default :
            }

            $watermark = $this->getWatermarkData();

            // convert video
            $convertCommandParse = vsprintf($convertCommand, [
                $this->getFFMPgPath(),
                intval($this->getConfigValue('maxDuration')),
                $filePath,
                $privateVideosPath . $fileName . $newFileExtension
            ]);

            // TODO если есть watermark
            if ( !empty($watermark) )
            {
                $ffprobe = '/home/sanjayp3/ffmpeg/ffprobe';
                //$ffprobe = str_replace('ffmpeg', 'ffprobe', $this->getFFMPgPath());
                $infoCommand = '%s -v error -show_entries stream=width,height -of default=noprint_wrappers=1:nokey=1 %s';

                $infoCommand = vsprintf($infoCommand, [
                    $ffprobe,
                    $filePath
                ]);

                exec($infoCommand, $videoStream);

                // TODO пробуем получить размер video
                if ( !empty($videoStream[0]) && !empty($videoStream[1]) )
                {
                    $width = $videoStream[0];
                    $height = $videoStream[1];
                    $path = $watermark['path'];
                    $overlayW = $watermark['width'];
                    $overlayH = $watermark['height'];

                    // TODO right bottom
                    $overlay = vsprintf('%s-%s-10:%s-%s-10', [$width, $overlayW, $height, $overlayH]);

                    $convertCommand .= ' -i %s -filter_complex "overlay=%s"';

                    $convertCommandParse = vsprintf($convertCommand, [
                        $this->getFFMPgPath(),
                        intval($this->getConfigValue('maxDuration')),
                        $filePath,
                        $privateVideosPath . $fileName . $newFileExtension,
                        $path,
                        $overlay
                    ]);
                }
            }


            exec($convertCommandParse);
        }

        return $fileName . $newFileExtension;
    }

    public function saveVideo( CVIDEOUPLOAD_BOL_Video $video )
    {
        $this->videoDao->save($video);

        return $video->getId();
    }

    public function findVideosForConverting($limit, $markAsInProcess = true)
    {
        $videoList = $this->videoDao->findVideosForConverting($limit);

        // mark as in process
        if ( $markAsInProcess )
        {
            foreach ( $videoList as $videoDto )
            {
                /* @var CVIDEOUPLOAD_BOL_Video $videoDto */

                $videoDto->status = self::VIDEO_STATUS_IN_PROCESS;

                $this->videoDao->save($videoDto);
            }
        }

        return $videoList;
    }

    /**
     * @param CVIDEOUPLOAD_BOL_Video $video
     *
     * @return CVIDEOUPLOAD_BOL_Preview|null
     */
    public function getVideoForPreview( CVIDEOUPLOAD_BOL_Video $video )
    {
        switch ($video->getStatus())
        {
            case CVIDEOUPLOAD_BOL_VideoDao::VIDEO_STATUS_NOT_CONFIRMED :

                $statusLabel = OW::getLanguage()->text(self::PLUGIN_KEY, 'video_status_not_confirmed');
                break;

            case CVIDEOUPLOAD_BOL_VideoDao::VIDEO_STATUS_PROCESSED :

                $statusLabel = OW::getLanguage()->text(self::PLUGIN_KEY, 'video_status_processed');
                break;

            case CVIDEOUPLOAD_BOL_VideoDao::VIDEO_STATUS_NOT_PROCESSED :

                $statusLabel = OW::getLanguage()->text(self::PLUGIN_KEY, 'video_status_not_processed');
                break;

            case CVIDEOUPLOAD_BOL_VideoDao::VIDEO_STATUS_IN_PROCESS :

            default :

                $statusLabel = OW::getLanguage()->text(self::PLUGIN_KEY, 'video_status_in_process');
                break;
        }

        $videos = [];

        // generate video urls
        if ( $video->getStatus() == CVIDEOUPLOAD_BOL_VideoDao::VIDEO_STATUS_PROCESSED )
        {
            foreach (self::CONVERT_MIME_TYPES as $type => $extension)
            {
                if ( file_exists($this->getPublicVideosPath() . $video->fileName . $extension) )
                {
                    $url = OW::getRouter()->urlForRoute('cvideoupload.stream', [
                        'name' => $video->getFileName(),
                        'extension' => $extension
                    ]);

                    $videos[] = [
                        'type' => $type,
                        'url' => $url
                    ];
                }
            }
        }

        $data = new CVIDEOUPLOAD_BOL_Preview();
        $data->setId($video->getId());
        $data->setUserId($video->getUserId());
        $data->setFileName($video->getFileName());
        $data->setReadableFileName($video->getReadableFileName());
        $data->setStatus($video->getStatus());
        $data->setStatusLabel($statusLabel);
        $data->setPrivacy($video->getPrivacy());
        $data->setCoverImage(self::getStorage()->getFileUrl($this->getPublicVideosPath() . $video->getFileName() . self::COVER_IMAGE_EXTENSION));

        if ( $videos )
        {
            $data->setVideos($videos);
        }

        return $data;
    }

    public function markVideo($id, $processed = true)
    {
        $video = $this->videoDao->findById($id);

        if ( $video )
        {
            $video->status = $processed
                ? self::VIDEO_STATUS_PROCESSED
                : self::VIDEO_STATUS_NOT_PROCESSED;

            $this->videoDao->save($video);
        }
    }

    public function findVideoByName($name, $isProcessed = false)
    {
        return $this->videoDao->findVideoByName($name, $isProcessed);
    }

    public function deleteVideosByUserId($userId)
    {
        $videos = $this->videoDao->findAllUserVideos($userId);

        // delete videos
        foreach ( $videos as $video )
        {
            $this->deleteVideo($video);
        }
    }

    public function deleteVideo( CVIDEOUPLOAD_BOL_Video $video )
    {
        switch( $video->getStatus() )
        {
            case CVIDEOUPLOAD_BOL_VideoDao::VIDEO_STATUS_NOT_CONFIRMED:
            case CVIDEOUPLOAD_BOL_VideoDao::VIDEO_STATUS_NOT_PROCESSED:

                $this->deleteNotProcessedVideo($video->fileName);

                break;

            case CVIDEOUPLOAD_BOL_VideoDao::VIDEO_STATUS_PROCESSED:

                $this->deleteProcessedVideo($video->fileName);

                break;
        }

        $event = new OW_Event(self::EVENT_VIDEO_DELETE, ['id' => $video->getId()]);
        OW::getEventManager()->trigger($event);

        $this->videoDao->delete($video);
    }

    public function deleteNotProcessedVideo($fileName, $includeImage = true)
    {
        if ( file_exists($this->getPrivateVideosPath() . $fileName) )
        {
            unlink($this->getPrivateVideosPath() . $fileName);
        }

        // delete cover image
        if ( $includeImage && self::getStorage()->isFile($this->getPublicVideosPath() . $fileName . self::COVER_IMAGE_EXTENSION) )
        {
            self::getStorage()->removeFile($this->getPublicVideosPath() . $fileName . self::COVER_IMAGE_EXTENSION);
        }
    }

    public function deleteProcessedVideo($fileName)
    {
        foreach ( self::CONVERT_MIME_TYPES as $extension )
        {
            if ( self::getStorage()->isFile($this->getPublicVideosPath() . $fileName . $extension) )
            {
                self::getStorage()->removeFile($this->getPublicVideosPath() . $fileName . $extension);
            }
        }

        // delete cover image
        if ( self::getStorage()->isFile($this->getPublicVideosPath() . $fileName . self::COVER_IMAGE_EXTENSION) )
        {
            self::getStorage()->removeFile($this->getPublicVideosPath() . $fileName . self::COVER_IMAGE_EXTENSION);
        }
    }

    /**
     * @return string
     */
    public function generateFileName()
    {
        while ( true )
        {
            $fileName = UTIL_String::generatePassword(self::FILE_NAME_LENGTH);

            if ( !file_exists($this->getPrivateVideosPath() . $fileName)
                && !self::getStorage()->fileExists($this->getPublicVideosPath() . $fileName) )
            {
                return $fileName;
            }
        }
    }

    public function getPrivateVideosPath()
    {
        return OW::getPluginManager()->getPlugin(self::PLUGIN_KEY)->getPluginFilesDir();
    }

    public static function getStorage()
    {
        if ( self::$storage === null )
        {
            self::$storage = new BASE_CLASS_FileStorage();
        }

        return self::$storage;
    }

    public function getPublicVideosPath()
    {
        return OW::getPluginManager()->getPlugin(self::PLUGIN_KEY)->getUserFilesDir();
    }

    /**
     * @param $userId
     * @param null $viewerId
     * @param CVIDEOUPLOAD_BOL_Video|null $video
     *
     * @return bool
     */
    public function isViewVideo( $viewerId = null, CVIDEOUPLOAD_BOL_Video $video = null )
    {
        if ( empty($video) )
        {
            return false;
        }

        if ( OW::getUser()->isAdmin() )
        {
            return true;
        }

        if ( $video->getStatus() != self::VIDEO_STATUS_PROCESSED ||
            $video->getAuthorization() != self::VIDEO_AUTHORIZATION_APPROVED )
        {
            return false;
        }

        switch ( $video->getPrivacy() )
        {
            case self::VIDEO_PRIVACY_ONLY_OWNER:
                return false;
                break;

            case self::VIDEO_PRIVACY_EVERYBODY:
                return true;
                break;

            case self::VIDEO_PRIVACY_CERTAIN_USERS:

                if ( !empty($viewerId) && intval($viewerId) )
                {
                    $viewerId = intval($viewerId);

                    return $this->isFriend($video->userId, $viewerId);
                }

                break;
        }

        return false;
    }

    public function isFriend( $userId, $friendId )
    {
        $userId = intval($userId);
        $friendId = intval($friendId);

        if ( $friendId && $userId )
        {
            if ( !empty($this->getFriendByUserId($userId, $friendId)) )
            {
                return true;
            }
        }

        return false;
    }

    public function setSessionBackUrl()
    {
        $session = OW::getSession();

        $key = self::PLUGIN_KEY . '.billing.back_url';

        $session->set($key, true);
    }

    public function getSessionBackUrl()
    {
        $session = OW::getSession();
        $key = self::PLUGIN_KEY . '.billing.back_url';

        if ( $session->isKeySet($key) )
        {
            return $session->get($key);
        }

        return null;
    }

    public function unsetSessionBackUrl()
    {
        $key = self::PLUGIN_KEY . '.billing.back_url';
        $session = OW::getSession();

        if ( $session->isKeySet($key) )
        {
            $session->delete($key);
        }

        return true;
    }

    public function getParamToSubscribeKey()
    {
        $var = self::GET_PARAMS_TO_SUBSCRIBE;

        return key($var);
    }

    public function isAddVideoAfterTracking( $userId )
    {
        $video = $this->findUserVideo($userId, false);

        if ( !empty($video) && $video->getStatus() != self::VIDEO_STATUS_NOT_CONFIRMED )
        {
            return true;
        }

        // нет видео или не сохранили

        return false;
    }

    public function findUsersVideoList($userIds, $isProcessed = true)
    {
        return $this->videoDao->findUsersVideoList($userIds, $isProcessed);
    }


    /***
     * @return \FFMpeg\FFMpeg
     */
    public function initFFMpeg()
    {
        $ffmpeg = FFMpeg\FFMpeg::create([
            'ffmpeg.binaries' => $this->getFFMPgPath(),
            'ffprobe.binaries' => '/home/sanjayp3/ffmpeg/ffprobe'
            //'ffprobe.binaries' => str_replace('ffmpeg', 'ffprobe', $this->getFFMPgPath())
        ]);

        return $ffmpeg;
    }

    /**
     * @return \FFMpeg\FFProbe
     */
    public function initFFProbe()
    {
        $ffprobe = FFMpeg\FFProbe::create([
            'ffmpeg.binaries' => $this->getFFMPgPath(),
            'ffprobe.binaries' => '/home/sanjayp3/ffmpeg/ffprobe'
            //'ffprobe.binaries' => str_replace('ffmpeg', 'ffprobe', $this->getFFMPgPath())
        ]);

        return $ffprobe;
    }

    public function generateImage( $videoPath, $coverFilePath )
    {
        $ffmpeg = $this->initFFMpeg();

        $video = $ffmpeg->open($videoPath);

        $duration = $this->getVideoDuration($videoPath);
        $quantity = 0;

        if ( $duration > 1 )
        {
            $quantity = rand(1, intval($duration));
        }

        $video
            ->frame(FFMpeg\Coordinate\TimeCode::fromSeconds($quantity))
            ->save($coverFilePath);
    }

    public function getVideoDuration( $videoPath )
    {
        $ffprobe = $this->initFFProbe();

        return $ffprobe->format($videoPath)->get('duration');
    }

    public function convertVideoMp4( $filePath, $newFile )
    {
        $duration = $this->getVideoDuration($filePath);
        $watermark = $this->getWatermarkData();

        $ffmpeg = $this->initFFMpeg();
        $video = $ffmpeg->open($filePath);

        if ( $duration > intval($this->getConfigValue('maxDuration')) )
        {
            $video->filters()->clip(FFMpeg\Coordinate\TimeCode::fromSeconds(0), FFMpeg\Coordinate\TimeCode::fromSeconds(intval($this->getConfigValue('maxDuration'))));
        }

        if ( !empty($watermark) )
        {
            $relative = [
                'position' => 'relative',
                'bottom' => 10,
                'right' => 10
            ];

            $video->filters()->watermark($watermark['path'], $relative);
        }

        $format = new FFMpeg\Format\Video\X264();
        $format->setAudioCodec("libfdk_aac");
        $format->setAudioCodec("libmp3lame");
        $format->setVideoCodec('libx264');

        $video->save($format, $newFile);
    }

    public function convertVideoWeb( $filePath, $newFile )
    {
        $duration = $this->getVideoDuration($filePath);
        $watermark = $this->getWatermarkData();

        $ffmpeg = $this->initFFMpeg();
        $video = $ffmpeg->open($filePath);

        if ( $duration > intval($this->getConfigValue('maxDuration')) )
        {
            $video->filters()->clip(FFMpeg\Coordinate\TimeCode::fromSeconds(0), FFMpeg\Coordinate\TimeCode::fromSeconds(intval($this->getConfigValue('maxDuration'))));
        }

        if ( !empty($watermark) )
        {
            $relative = [
                'position' => 'relative',
                'bottom' => 10,
                'right' => 10
            ];

            $video->filters()->watermark($watermark['path'], $relative);
        }

        $format = new FFMpeg\Format\Video\WebM('libvorbis', 'libvpx-vp9');

        $video->save($format, $newFile);
    }
}
