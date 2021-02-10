<?php

class CUSTOMPROFILEVIEW_CMP_Gallery extends OW_Component
{
    const PHOTO_COUNT = 7;
    const PHOTO_CHANGE_INTERVAL = 1000;
    const PHOTO_LIMIT = 200;

    const SECTION_BASIC = 'f90cde5913235d172603cc4e7b9726e3';
    const SECTION_EXTRA = '915d2ec7a70f5ba00a4f8b3e4c0c108c';
    const SECTION_DETAILED = 'bc69f33cca98ed9f54c5e9ecb0f8ff35';
    const SECTION_PARTNER_PREFERENCE = '467f708c065429c8448d297b8a1dae87';
    const QUESTION_AGE = 'birthdate';
    const QUESTION_LOCATION = 'googlemap_location';
//    const QUESTION_GENDER = 'sex';
    const QUESTION_RELIGION = 'field_eb5d72a73ab37736e9882a28fc0803c1';
    const QUESTION_HEIGHT = 'field_b5e5e6a9a08ae854231c5761f719347e';
    const QUESTION_JOIN_DATE = 'joinStamp';
    const QUESTION_USERNAME = 'username';
    const QUESTION_EMAIL = 'email';
    const QUESTION_DISPLAY_NAME = 'realname';
    const QUESTION_PARTNER_DESCRIPTION = 'field_8e9f5b3083f3e1cc02ff48cca1324a5f';
    const QUESTION_ABOUT_ME = 'aboutme';
    const QUESTION_INTEREST = 'field_5ca46470a4dc55c8d7ff2dbaa4315c98';
    const QUESTION_MUSIC = 'field_ec75771c400c096688c62131e7564263';
    const QUESTION_PTYPE = 'field_5d455f945ae721abeb92530c54bf25b7';

    const COUNT_PHOTOS = 3;

    protected $userId;
    protected $uniqId;
    
    /**
     *
     * @var BOL_Avatar
     */
    protected $avatarDto;

    /**
     * @var CVIDEOUPLOAD_BOL_Service
     */
    protected $videoService;

    public function __construct( $userId )
    {
        parent::__construct();

        $this->userId = $userId;
        if (OW::getPluginManager()->isPluginActive('cvideoupload')) {
            $this->videoService = CVIDEOUPLOAD_BOL_Service::getInstance();
        }
        $this->uniqId = uniqid('pcgallery-');

        if ( !PCGALLERY_CLASS_PhotoBridge::getInstance()->isActive() )
        {
            $this->setVisible(false);
        }
        
        $this->avatarDto = BOL_AvatarService::getInstance()->findByUserId($userId);

        OW::getDocument()->addScript(OW::getPluginManager()->getPlugin('customprofileview')->getStaticJsUrl() . 'customprofileview.js');

        $ownerMode = false;
        if ($userId == OW::getUser()->getId()) {
            $ownerMode = true;
        }

        $this->assign("ownerMode", $ownerMode);

        $arrowLeft = OW::getPluginManager()->getPlugin('customprofileview')->getStaticUrl() . 'img/arrow_left.svg';
        $this->assign('arrowLeft', $arrowLeft);

        $arrowRight = OW::getPluginManager()->getPlugin('customprofileview')->getStaticUrl() . 'img/arrow_right.svg';
        $this->assign('arrowRight', $arrowRight);

        $playButton = OW::getPluginManager()->getPlugin('customprofileview')->getStaticUrl() . 'img/play-button.svg';
        $this->assign('playButton', $playButton);

        if (OW::getPluginManager()->isPluginActive('cvideoupload')) {
            $video = $this->videoService->findUserVideo($userId);
            $isViewVideo = $this->videoService->isViewVideo(OW::getUser()->getId(), $video);

            $this->assign('isViewVideo', $isViewVideo);
            if ($isViewVideo) {
                $this->assign('video', ($video ? $this->videoService->getVideoForPreview($video) : ''));
            }
        }

        $photoList = PHOTO_BOL_PhotoService::getInstance()->findPhotoListByUserId($userId, 1, 500);

        $photoBlocks = [];
        $item = 0;
        $block = 0;

        if ($photoList) {
            foreach ($photoList as $key => $photo) {
                // if album protected then not show photos in this album
                if ((PROTECTEDPHOTOS_BOL_PasswordDao::getInstance()->isAlbumProtected($photo['albumId']) && $userId != OW::getUser()->getId()) && !OW::getUser()->isAdmin()) {
                    continue;
                }

                $photo['original_url'] = PHOTO_BOL_PhotoDao::getInstance()->getPhotoFullsizeUrl($photo['id'], $photo['hash']);
                $photoBlocks[$block][$key] = $photo;
                $item = $item + 1;

                if (($item >= self::COUNT_PHOTOS) || ($block == 0 && $item >= self::COUNT_PHOTOS - 1 && $video && $isViewVideo)) {
                    $block = $block + 1;
                    $item = 0;
                }
            }

            $this->assign('photoBlocks', $photoBlocks);
            $countPhotoBlocks = count($photoBlocks);
        } else {
            $this->assign('photoBlocks', null);
            $countPhotoBlocks = 0;
        }

        OW::getDocument()->addScriptDeclarationBeforeIncludes(UTIL_JsGenerator::composeJsString(
            ';window.customParams = {$params};',
            ['params' => [
                'photoTitle' => OW::getLanguage()->text('photo', 'user_photo_albums_widget'),
                'videoTitle' => OW::getLanguage()->text('cvideoupload', 'video_widget_title'),
                'ownerMode' => $ownerMode,
                'countPhotoBlocks' => $countPhotoBlocks
            ]]
        ));
    }

    private function getUserInfo()
    {
        $permissions = $this->getPemissions();
        $user = array();

        $user['id'] = $this->userId;

        $onlineUser = BOL_UserService::getInstance()->findOnlineUserById($this->userId);
        $user['isOnline'] = $onlineUser !== null;

        $avatar = BOL_AvatarService::getInstance()->getAvatarUrl($this->userId, 3, null, false, !$permissions["viewAvatar"]);
        
        $user['avatar'] = $avatar ? $avatar : BOL_AvatarService::getInstance()->getDefaultAvatarUrl(2);

        $roles = BOL_AuthorizationService::getInstance()->getRoleListOfUsers(array($this->userId));

        $user['role'] = !empty($roles[$this->userId]) ? $roles[$this->userId] : null;

        $user['displayName'] = BOL_UserService::getInstance()->getDisplayName($this->userId);

        return $user;
    }

    public function getPemissions()
    {
        static $permissions = null;
        
        if ( !empty($permissions) )
        {
            return $permissions;
        }

        $permissions = array(
            'changeAvatar' => false,
            'uploadPhotos' => false,
            'selfMode' => false
        );

        $selfMode = $this->userId == OW::getUser()->getId();
        
        $permissions['selfMode'] = $selfMode;
        $permissions['changeSettings'] = $selfMode;
        $permissions['changeAvatar'] = $selfMode;
        $permissions['uploadPhotos'] = $selfMode;
        $permissions['viewAvatar'] = ($this->avatarDto && $this->avatarDto->status == "active") 
                || $selfMode || OW::getUser()->isAuthorized("base");
        
        $permissions['approveAvatar'] = OW::getUser()->isAuthorized("base");
        
        $permissions['view'] = $selfMode || OW::getUser()->isAuthorized("photo");
        
        if ( !$permissions['view'] )
        {
            $event = new OW_Event('privacy_check_permission', array(
                'action' => "photo_view_album",
                'ownerId' => $this->userId, 
                'viewerId' => OW::getUser()->getId()
            ));

            try 
            {
                OW::getEventManager()->trigger($event);
                $permissions['view'] = true;
            }
            catch ( RedirectException $e )
            {
                // Pass
            }
        }
        
        return $permissions;
    }

    public function getPhotos()
    {
        $source = BOL_PreferenceService::getInstance()->getPreferenceValue("pcgallery_source", $this->userId);
        $album = BOL_PreferenceService::getInstance()->getPreferenceValue("pcgallery_album", $this->userId);
        
        if ( $source == "album" )
        {
            $photos = PCGALLERY_CLASS_PhotoBridge::getInstance()->getAlbumPhotos($this->userId, $album, array(0, self::PHOTO_LIMIT));
        }
        else
        {
            $photos = PCGALLERY_CLASS_PhotoBridge::getInstance()->getPhotos($this->userId, array(0, self::PHOTO_LIMIT));
        }

        if ( count($photos) < self::PHOTO_COUNT )
        {
            return array();
        }

        return $photos;
    }
    
    public function initEmptyGallery()
    {
        $source = BOL_PreferenceService::getInstance()->getPreferenceValue("pcgallery_source", $this->userId);
        
        if ( $source == "all" )
        {
            $album = PCGALLERY_CLASS_PhotoBridge::getInstance()->getAlbum($this->userId);
            $albumId = $album["id"];
        }
        else
        {
            $albumId = BOL_PreferenceService::getInstance()->getPreferenceValue("pcgallery_album", $this->userId);
        }
        
        $jsCall = OW::getEventManager()->call("photo.getAddPhotoURL", array(
            "albumId" => $albumId
        ));
        
        $js = UTIL_JsGenerator::newInstance();
        $js->addScript('$(document).on("click", "#pcgallery-add-photo-btn", window[{$fncId}]);', array(
            "fncId" => $jsCall
        ));
        
        OW::getDocument()->addOnloadScript($js);
    }
    
    public function initFullGallery()
    {
        OW::getEventManager()->call("photo.init_floatbox");
    }

    public function initJs( $permissions )
    {
        
        if ( $permissions["changeAvatar"] )
        {
            $label = OW::getLanguage()->text('base', 'avatar_change');

            $script =
            '$("[data-outlet=avatar-change]", "#' . $this->uniqId . '").click(function() {
                document.avatarFloatBox = OW.ajaxFloatBox(
                    "BASE_CMP_AvatarChange",
                    { params : { step : 1 } },
                    { width : 749, title: ' . json_encode($label) . '}
                );
            });

            OW.bind("base.avatar_cropped", function(data){
                if ( data.bigUrl != undefined ) {
                    $("[data-outlet=avatar]", "#' . $this->uniqId . '").css({ "background-image" : "url(" + data.bigUrl + ")" });
                }
            });
            ';

            OW::getDocument()->addOnloadScript($script);
        }
        
        if ( $permissions["approveAvatar"] && ($this->avatarDto && $this->avatarDto->status != "active") )
        {
            $script = ' window.avartar_arrove_request = false;
                $("[data-outlet=approve-avatar]", "#' . $this->uniqId . '").click(function(){

                    if ( window.avartar_arrove_request == true )
                    {
                        return;
                    }

                    window.avartar_arrove_request = true;

                    $.ajax({
                        "type": "POST",
                        "url": '.json_encode(OW::getRouter()->urlFor('BASE_CTRL_Avatar', 'ajaxResponder')).',
                        "data": {
                            \'ajaxFunc\' : \'ajaxAvatarApprove\',
                            \'avatarId\' : '.((int)$this->avatarDto->id).'
                        },
                        "success": function(data){
                            if ( data.result == true )
                            {
                                if ( data.message )
                                {
                                    OW.info(data.message);
                                }
                                else
                                {
                                    OW.info('.json_encode(OW::getLanguage()->text('base', 'avatar_has_been_approved')).');
                                }

                                $("[data-outlet=approve-overlay]", "#' . $this->uniqId . '").remove();
                                $("[data-outlet=approve-avatar-w]", "#' . $this->uniqId . '").remove();
                            }
                            else
                            {
                                if ( data.error )
                                {
                                    OW.info(data.error);
                                }
                            }
                        },
                        "complete": function(){
                            window.avartar_arrove_request = false;
                        },
                        "dataType": "json"
                    });
                }); ';

            OW::getDocument()->addOnloadScript($script);
        }
    }
    
    public function onBeforeRender()
    {
        parent::onBeforeRender();

        $permissions = $this->getPemissions();
        
        PCGALLERY_CLASS_PhotoBridge::getInstance()->initFloatbox();

        $staticUrl = OW::getPluginManager()->getPlugin('pcgallery')->getStaticUrl();
        OW::getDocument()->addStyleSheet($staticUrl . 'style.css');
        OW::getDocument()->addScript($staticUrl . 'script.js');

        $this->assign("avatarApproval", $this->avatarDto && $this->avatarDto->status != "active");
        $this->initJs($permissions);

        $toolbar = new CUSTOMPROFILEVIEW_CMP_ProfileActionToolbar($this->userId);
        $this->addComponent('actionToolbar', $toolbar);

        $this->assign('uniqId', $this->uniqId);
        $this->assign('user', $this->getUserInfo());
        $this->assign('permissions', $permissions);

        $photos = $permissions["view"] ? $this->getPhotos() : array();
        $this->assign('empty', empty($photos));
        
        if ( empty($photos) )
        {
            $this->initEmptyGallery();
        }
        else
        {
            $this->initFullGallery();
        } 
        
        $this->assign('photos', $photos);

        $source = BOL_PreferenceService::getInstance()->getPreferenceValue("pcgallery_source", $this->userId);
        
        $settings = array(
            "changeInterval" => self::PHOTO_CHANGE_INTERVAL,
            "userId" => $this->userId,
            "listType" => $source == "all" ? "userPhotos" : "albumPhotos"
        );

        $js = UTIL_JsGenerator::newInstance();
        $js->callFunction(array('PCGALLERY', 'init'), array(
            $this->uniqId,
            $settings,
            $photos
        ));

        OW::getDocument()->addOnloadScript($js);
        
        OW::getLanguage()->addKeyForJs("pcgallery", "setting_fb_title");

        $adminMode = OW::getUser()->isAdmin() || OW::getUser()->isAuthorized('base');
        $questions = BASE_CMP_UserViewWidget::getUserViewQuestions($this->userId, $adminMode);

        $basicQuestions = $questions['questions'][self::SECTION_BASIC];
        $newQuestions = [];

        foreach ($basicQuestions as $key => $basicQuestion) {
            if (in_array($basicQuestion['name'], [self::QUESTION_AGE, self::QUESTION_MUSIC, self::QUESTION_INTEREST, self::QUESTION_LOCATION, self::QUESTION_RELIGION, self::QUESTION_HEIGHT, self::QUESTION_JOIN_DATE, self::QUESTION_EMAIL, self::QUESTION_USERNAME, self::QUESTION_DISPLAY_NAME, self::QUESTION_ABOUT_ME])) {
                unset($basicQuestions[$key]);
            } else {
                $newQuestions[] = $basicQuestion;
            }
        }

        $this->assign('questions', $newQuestions);

        $this->assign('questionsData', $questions['data'][$this->userId]);
        $this->assign('labels', $questions['labels']);

        $shortInfo = $questions['data'][$this->userId][self::QUESTION_AGE] . ', '  . $questions['data'][$this->userId][self::QUESTION_RELIGION] . ', ' . $questions['data'][$this->userId][self::QUESTION_HEIGHT] . ', ' . strip_tags($questions['data'][$this->userId][self::QUESTION_LOCATION]);
        $this->assign('shortInfo', $shortInfo);

        $shortInfointerest = $questions['data'][$this->userId][self::QUESTION_INTEREST];
        $this->assign('shortInfointerest', $shortInfointerest);
     
        $shortInfomusic = $questions['data'][$this->userId][self::QUESTION_MUSIC];
        $this->assign('shortInfomusic', $shortInfomusic);

        $shortInfoptype = $questions['data'][$this->userId][self::QUESTION_PTYPE];
        $this->assign('shortInfoptype', $shortInfoptype);
    }
}