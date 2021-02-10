<?php

class CUSTOMPROFILEVIEW_CLASS_EventHandler {
    private static $classInstance;

    public static function getInstance()
    {
        if (self::$classInstance === null) {
            self::$classInstance = new self();
        }

        return self::$classInstance;
    }

    protected function __construct()
    {
    }

    public function init() {
        $eventManager = OW::getEventManager();

        $eventManager->bind(OW_EventManager::ON_AFTER_ROUTE, [$this, 'onAfterRoute']);
        $eventManager->bind('class.get_instance.PCGALLERY_CMP_Gallery', [$this, 'getGalleryInstance']);

        if (OW::getPluginManager()->isPluginActive('customfields')) {
            $eventManager->bind('class.get_instance.CUSTOMFIELDS_CTRL_ComponentPanel', array($this, 'onGetComponentPanelControllerInstance'));
        }
        $eventManager->bind('class.get_instance.BASE_CTRL_ComponentPanel', array($this, 'onGetComponentPanelControllerInstance'));

        $eventManager->bind(OW_EventManager::ON_FINALIZE, array($this, 'onFinalize'));

        $eventManager->bind(CUSTOMPROFILEVIEW_CMP_ProfileActionToolbar::EVENT_NAME, array($this, 'addProfileActionToolbar'));
    }

    public function onAfterRoute(OW_Event $event)
    {
        $handler = OW::getRequestHandler()->getHandlerAttributes();

        if ($handler[OW_RequestHandler::ATTRS_KEY_CTRL] == 'BASE_CTRL_ComponentPanel' && $handler[OW_RequestHandler::ATTRS_KEY_ACTION] == 'profile' && OW::getUser()->isAuthenticated()) {
            $params = $handler[OW_RequestHandler::ATTRS_KEY_VARLIST];
            $userDto = BOL_UserService::getInstance()->findByUsername($params['username']);

//            if ($userDto->id != OW::getUser()->getId() ) {
                $document = OW::getDocument();
                $document->getMasterPage()->setTemplate(
                    OW::getPluginManager()->getPlugin('customprofileview')->getViewDir() . 'master_pages' . DS . 'general.html'
                );
//            }

            if ($userDto->id = OW::getUser()->getId() ) {
                OW::getDocument()->addScriptDeclaration(
                    UTIL_JsGenerator::composeJsString(
                        ';$("#custom-btn-video").on("click", function(e)
                        {
                            document.location.href = {$url};
                        });',
                        [
                            'url' => OW::getRouter()->urlForRoute('cvideoupload.video-upload')
                        ]
                    )
                );

                OW::getDocument()->addScriptDeclaration(
                    UTIL_JsGenerator::composeJsString(
                        ';$("#custom-btn-photo").on("click", function(e)
                        {
                            document.location.href = {$url};
                        });',
                        [
                            'url' => OW::getRouter()->urlForRoute('photo_user_albums', ['user' => $params['username']])
                        ]
                    )
                );
            }

        }
    }

    public function getGalleryInstance(OW_Event $event) {
        $params = $event->getParams();
        $userId = $params['arguments'][0];
//        if ($userId != OW::getUser()->getId()) {
            OW::getDocument()->addStyleSheet(OW::getPluginManager()->getPlugin('customprofileview')->getStaticCssUrl() . 'customprofileview.css');
            $event->setData(
                new CUSTOMPROFILEVIEW_CMP_Gallery($params['arguments'][0])
            );
//        }

        return $event->getData();
    }

    public function onGetComponentPanelControllerInstance(OW_Event $event)
    {
        $event->setData(new CUSTOMPROFILEVIEW_CTRL_ComponentPanel());
    }

    public function onFinalize(OW_Event $event) {

    }

    public function addProfileActionToolbar( OW_Event $event )
    {
        $params = $event->getParams();

        if ( !OW::getUser()->isAuthenticated() )
        {
            return;
        }

        if ($params['userId'] == OW::getUser()->getId()) {
            $event->add(array(
                BASE_CMP_ProfileActionToolbar::DATA_KEY_ITEM_KEY => "edit_profile.action",
                BASE_CMP_ProfileActionToolbar::DATA_KEY_LINK_ID => uniqid('edit_profile-'),
                BASE_CMP_ProfileActionToolbar::DATA_KEY_LABEL => OW::getLanguage()->text('skmobileapp', 'edit_profile'),
                BASE_CMP_ProfileActionToolbar::DATA_KEY_LINK_ORDER => 1,
                BASE_CMP_ProfileActionToolbar::DATA_KEY_LINK_GROUP_KEY => '',
                BASE_CMP_ProfileActionToolbar::DATA_KEY_LINK_ATTRIBUTES => array(),
                BASE_CMP_ProfileActionToolbar::DATA_KEY_LINK_HREF => OW::getRouter()->urlForRoute('base_edit')
            ));
        }

        if (OW::getUser()->isAdmin() && $params['userId'] != OW::getUser()->getId()) {
            $event->add(array(
                BASE_CMP_ProfileActionToolbar::DATA_KEY_ITEM_KEY => "edit_profile.action",
                BASE_CMP_ProfileActionToolbar::DATA_KEY_LINK_ID => uniqid('edit_profile-'),
                BASE_CMP_ProfileActionToolbar::DATA_KEY_LABEL => OW::getLanguage()->text('skmobileapp', 'edit_profile'),
                BASE_CMP_ProfileActionToolbar::DATA_KEY_LINK_ORDER => -1,
                BASE_CMP_ProfileActionToolbar::DATA_KEY_LINK_GROUP_KEY => '',
                BASE_CMP_ProfileActionToolbar::DATA_KEY_LINK_ATTRIBUTES => array(),
                BASE_CMP_ProfileActionToolbar::DATA_KEY_LINK_HREF => OW::getRouter()->urlForRoute('base_edit_user_datails', ['userId' => $params['userId']])
            ));
        }
    }
}
