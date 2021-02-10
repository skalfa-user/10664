<?php

/**
 * Copyright (c) 2014, Skalfa LLC
 * All rights reserved.
 * 
 * ATTENTION: This commercial software is intended for exclusive use with SkaDate Dating Software (http://www.skadate.com) and is licensed under SkaDate Exclusive License by Skalfa LLC.
 * 
 * Full text of this license can be found at http://www.skadate.com/sel.pdf
 */
class SKADATE_MCTRL_Join extends SKADATE_CTRL_Join
{

    public function __construct()
    {
        parent::__construct();
    }

    public function index( $params )
    {
        parent::index($params);

        $this->setTemplate(OW::getPluginManager()->getPlugin('skadate')->getMobileCtrlViewDir() . 'join_index.html');
        
        $this->assign('displayAccountType', false);
        
        $urlParams = $_GET;
        
        if ( is_array($params) && !empty($params) )
        {
            $urlParams = array_merge($_GET, $params);
        }
        
        /* @var $form JoinForm */
        $form = $this->joinForm;
        
        if( !empty($form) )
        {
            $this->joinForm->setAction(OW::getRouter()->urlFor('SKADATE_MCTRL_Join', 'joinFormSubmit', $urlParams));
            
            BASE_MCLASS_JoinFormUtlis::setLabels($form, $form->getSortedQuestionsList());
            BASE_MCLASS_JoinFormUtlis::setInvitations($form, $form->getSortedQuestionsList());
            BASE_MCLASS_JoinFormUtlis::setColumnCount($form);

            $displayPhotoUpload = OW::getConfig()->getValue('base', 'join_display_photo_upload');

            $this->assign('requiredPhotoUpload', ($displayPhotoUpload == BOL_UserService::CONFIG_JOIN_DISPLAY_AND_SET_REQUIRED_PHOTO_UPLOAD));
            $this->assign('presentationToClass', $this->presentationToCssClass());

            $element = $this->joinForm->getElement('userPhoto');

            $this->assign('photoUploadId', 'userPhoto');

            if ( $element )
            {
                $this->assign('photoUploadId', $element->getId());
            }

            BASE_MCLASS_JoinFormUtlis::addOnloadJs($form->getName());
        }
    }
    public function joinFormSubmit( $params )
    {
        parent::joinFormSubmit($params);
        $this->setTemplate(OW::getPluginManager()->getPlugin('skadate')->getMobileCtrlViewDir() . 'join_index.html');
        $this->assign('displayAccountType', false);
    }


    protected function presentationToCssClass()
    {
        return BASE_MCLASS_JoinFormUtlis::presentationToCssClass();
    }
    
    public function ajaxResponder()
    {
        parent::ajaxResponder();
    }
    
    protected function createAvatar( $userId )
    {
        $avatarService = BOL_AvatarService::getInstance();

        $path = $_FILES['userPhoto']['tmp_name'];

        if ( !file_exists($path) )
        {
            return false;
        }

        if ( !UTIL_File::validateImage($_FILES['userPhoto']['name']) )
        {
            return false;
        }

        $event = new OW_Event('base.before_avatar_change', array(
            'userId' => $userId,
            'avatarId' => null,
            'upload' => true,
            'crop' => false,
            'isModerable' => false
        ));
        OW::getEventManager()->trigger($event);

        $avatarSet = $avatarService->setUserAvatar($userId, $path, array('isModerable' => false, 'trackAction' => false ));

        if ( $avatarSet )
        {
            $avatar = $avatarService->findByUserId($userId);
            
            if ( $avatar )
            {
                $event = new OW_Event('base.after_avatar_change', array(
                    'userId' => $userId,
                    'avatarId' => $avatar->id,
                    'upload' => true,
                    'crop' => false
                ));
                OW::getEventManager()->trigger($event);
            }
        }

        return $avatarSet;
    }
}
