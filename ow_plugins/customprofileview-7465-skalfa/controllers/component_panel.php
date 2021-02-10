<?php

class CUSTOMPROFILEVIEW_CTRL_ComponentPanel extends BASE_CTRL_ComponentPanel
{
    /**
     *
     * @var BOL_ComponentAdminService
     */
    private $componentAdminService;
    /**
     *
     * @var BOL_ComponentEntityService
     */
    private $componentEntityService;

    public function __construct()
    {
        parent::__construct();
        $this->componentAdminService = BOL_ComponentAdminService::getInstance();
        $this->componentEntityService = BOL_ComponentEntityService::getInstance();

//        $controllersTemplate = OW::getPluginManager()->getPlugin('customprofileview')->getCtrlViewDir() . 'component_panel.html';
//        $this->setTemplate($controllersTemplate);
    }

    public function profile($paramList)
    {
        $userDto = BOL_UserService::getInstance()->findByUsername($paramList['username']);

//        if ( $userDto->id == OW::getUser()->getId() ) {
//            return parent::profile($paramList);
//        } else {
            $userService = BOL_UserService::getInstance();
            /* @var $userDao BOL_User */
            $userDto = $userService->findByUsername($paramList['username']);

            if ( $userDto === null )
            {
                throw new Redirect404Exception();
            }

//            if ( $userDto->id == OW::getUser()->getId() )
//            {
//                $this->myProfile($paramList);
//
//                return;
//            }

            if ( !OW::getUser()->isAuthorized('base', 'view_profile') )
            {
                $status = BOL_AuthorizationService::getInstance()->getActionStatus('base', 'view_profile');
                throw new AuthorizationException($status['msg']);
            }

            $eventParams = array(
                'action' => 'base_view_profile',
                'ownerId' => $userDto->id,
                'viewerId' => OW::getUser()->getId()
            );

            $event = new OW_Event('privacy_check_permission', $eventParams);

            try
            {
                OW::getEventManager()->getInstance()->trigger($event);
            }
            catch ( RedirectException $ex )
            {
                $exception = new RedirectException(OW::getRouter()->urlForRoute('base_user_privacy_no_permission', array('username' => $userDto->username)));

                throw $exception;
            }

            $displayName = BOL_UserService::getInstance()->getDisplayName($userDto->id);

            $this->setPageTitle(OW::getLanguage()->text('base', 'profile_view_title', array('username' => $displayName)));
            OW::getDocument()->setDescription(OW::getLanguage()->text('base', 'profile_view_description', array('username' => $displayName)));

            $event = new OW_Event('base.on_get_user_status', array('userId' => $userDto->id));
            OW::getEventManager()->trigger($event);
            $status = $event->getData();

//            $headingSuffix = "";
//
//            if ( !BOL_UserService::getInstance()->isApproved($userDto->id) )
//            {
//                $headingSuffix = ' <span class="ow_remark ow_small">(' . OW::getLanguage()->text("base", "pending_approval") . ')</span>';
//            }
//
//            if ( $status !== null )
//            {
//                $heading = OW::getLanguage()->text('base', 'user_page_heading_status', array('status' => $status, 'username' => $displayName));
//                $this->setPageHeading($heading . $headingSuffix);
//            }
//            else
//            {
//                $this->setPageHeading(OW::getLanguage()->text('base', 'profile_view_heading', array('username' => $displayName)) . $headingSuffix);
//            }
//
//            $this->setPageHeadingIconClass('ow_ic_user');

            $this->assign('isSuspended', $userService->isSuspended($userDto->id));
            $this->assign('isAdminViewer', OW::getUser()->isAuthorized('base'));

            $place = BOL_ComponentService::PLACE_PROFILE;

            $cmp = OW::getClassInstance("BASE_CMP_ProfileActionToolbar", $userDto->id);
            $this->addComponent('profileActionToolbar', $cmp);

            $template = 'drag_and_drop_entity_panel';

            $this->action($place, $userDto->id, false, array(), $template);

            $controllersTemplate = OW::getPluginManager()->getPlugin('customprofileview')->getCtrlViewDir() . 'widget_panel_profile.html';
            $this->setTemplate($controllersTemplate);

            $this->setDocumentKey('base_profile_page');

            $vars = BOL_SeoService::getInstance()->getUserMetaInfo($userDto);

//             set meta info
            $params = array(
                "sectionKey" => "base.users",
                "entityKey" => "userPage",
                "title" => "base+meta_title_user_page",
                "description" => "base+meta_desc_user_page",
                "keywords" => "base+meta_keywords_user_page",
                "vars" => $vars,
                "image" => BOL_AvatarService::getInstance()->getAvatarUrl($userDto->getId(), 2)
            );

            OW::getEventManager()->trigger(new OW_Event("base.provide_page_meta_info", $params));
//        }
    }

    private function action( $place, $userId, $customizeMode, $customizeRouts, $componentTemplate, $responderController = null )
    {
        $userCustomizeAllowed = (bool) $this->componentAdminService->findPlace($place)->editableByUser;

        if ( !$userCustomizeAllowed && $customizeMode )
        {
            $this->redirect($customizeRouts['normal']);
        }

        $schemeList = $this->componentAdminService->findSchemeList();

        $state = $this->componentAdminService->findCache($place);
        if ( empty($state) )
        {
            $state = array();
            $state['defaultComponents'] = $this->componentAdminService->findPlaceComponentList($place);
            $state['defaultPositions'] = $this->componentAdminService->findAllPositionList($place);
            $state['defaultSettings'] = $this->componentAdminService->findAllSettingList();
            $state['defaultScheme'] = (array) $this->componentAdminService->findSchemeByPlace($place);

            $this->componentAdminService->saveCache($place, $state);
        }

        $defaultComponents = $state['defaultComponents'];

        $defaultPositions = $state['defaultPositions'];
        $defaultSettings = $state['defaultSettings'];
        $defaultScheme = $state['defaultScheme'];

        if ( $userCustomizeAllowed )
        {
            $userCache = $this->componentEntityService->findEntityCache($place, $userId);

            if ( empty($userCache) )
            {
                $userCache = array();
                $userCache['userComponents'] = $this->componentEntityService->findPlaceComponentList($place, $userId);
                $userCache['userSettings'] = $this->componentEntityService->findAllSettingList($userId);
                $userCache['userPositions'] = $this->componentEntityService->findAllPositionList($place, $userId);

                $this->componentEntityService->saveEntityCache($place, $userId, $userCache);
            }

            $userComponents = $userCache['userComponents'];
            $userSettings = $userCache['userSettings'];
            $userPositions = $userCache['userPositions'];
        }
        else
        {
            $userComponents = array();
            $userSettings = array();
            $userPositions = array();
        }

        if ( empty($defaultScheme) && !empty($schemeList) )
        {
            $defaultScheme = reset($schemeList);
        }

        $componentPanel = OW::getClassInstance('CUSTOMPROFILEVIEW_CMP_DragAndDropEntityPanel', $place, $userId, $defaultComponents, $customizeMode, $componentTemplate, $responderController);
        $componentPanel->setAdditionalSettingList(array(
            'entityId' => $userId,
            'entity' => 'user'
        ));

        if ( !empty($customizeRouts) )
        {
            $componentPanel->allowCustomize($userCustomizeAllowed);
            $componentPanel->customizeControlCunfigure($customizeRouts['customize'], $customizeRouts['normal']);
        }

        $componentPanel->setSchemeList($schemeList);
        $componentPanel->setPositionList($defaultPositions);
        $componentPanel->setSettingList($defaultSettings);
        $componentPanel->setScheme($defaultScheme);

        /*
         * This feature was disabled for users
         * if ( !empty($userScheme) )
          {
          $componentPanel->setEntityScheme($userScheme);
          } */

        if ( !empty($userComponents) )
        {
            $componentPanel->setEntityComponentList($userComponents);
        }

        if ( !empty($userPositions) )
        {
            $componentPanel->setEntityPositionList($userPositions);
        }

        if ( !empty($userSettings) )
        {
            $componentPanel->setEntitySettingList($userSettings);
        }

        $this->assign('componentPanel', $componentPanel->render());
    }
}