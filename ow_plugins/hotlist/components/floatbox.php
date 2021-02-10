<?php

/**
 * Copyright (c) 2017, Skalfa LLC
 * All rights reserved.
 * 
 * ATTENTION: This commercial software is intended for exclusive use with SkaDate Dating Software (http://www.skadate.com) and is licensed under SkaDate Exclusive License by Skalfa LLC.
 * 
 * Full text of this license can be found at http://www.skadate.com/sel.pdf
 */
/**
 * @author Zarif Safiullin <zaph.saph@gmail.com>
 * @package ow_plugins.hotlist.components
 * @since 1.0
 */
class HOTLIST_CMP_Floatbox extends OW_Component
{
    public function __construct()
    {
        parent::__construct();

        if ( !OW::getUser()->isAuthenticated() )
        {
            throw new Redirect403Exception();
        }

        $service = HOTLIST_BOL_Service::getInstance();

        if ($service->findUserById(OW::getUser()->getId()))
        {
            $this->assign('userInList', true);
            $this->assign('text_notification', OW::getLanguage()->text('hotlist', 'text_remove_from_list'));

            $removeFromListForm = new RemoveFromHotListForm();
            $this->addForm($removeFromListForm);
        }
        else
        {
            $this->assign('userInList', false);

            if (OW::getPluginManager()->isPluginActive('usercredits'))
            {
                $creditService = USERCREDITS_BOL_CreditsService::getInstance();
                $action = $creditService->findAction('hotlist', 'add_to_list');
                $actionPrice = $creditService->findActionPriceForUser($action->id, OW::getUser()->getId());
                $amount = $actionPrice->amount;
            }
            else
            {
                $userCreditsAction = new HOTLIST_CLASS_Credits();
                $amount = $userCreditsAction->getActionCost();
            }

            $status = BOL_AuthorizationService::getInstance()->getActionStatus('hotlist', 'add_to_list');

            if (isset($status['authorizedBy']) && $status['authorizedBy'] == 'base')
            {
                $this->assign('floatbox_text', OW::getLanguage()->text('hotlist', 'floatbox_text_simple'));
            }
            else
            {
                $this->assign('floatbox_text', OW::getLanguage()->text('hotlist', 'floatbox_text', array('amount'=>abs($amount))));
            }

            $addToListForm = new AddToHotListForm();

            $this->addForm($addToListForm);
        }
    }

    public static function process( $data )
    {
        $resp = array();
        $lang = OW::getLanguage();
        $service = HOTLIST_BOL_Service::getInstance();

        if ( !OW::getUser()->isAuthenticated() )
        {
            $resp['error'] = $lang->text('base', 'base_sign_in_cap_label');
            echo json_encode($resp);
            exit;
        }

        if ($service->findUserById(OW::getUser()->getId()))
        {
            if ( $data['remove_from_list'] )
            {
                $service->deleteUser(OW::getUser()->getId());

//                        //Newsfeed
//                        OW::getEventManager()->trigger(new OW_Event('feed.delete_item', array(
//                            'entityType' => 'add_to_hotlist',
//                            'entityId' => OW::getUser()->getId()
//                        )));

                $resp['message'] = OW::getLanguage()->text('hotlist', 'user_removed');
                $resp['removed'] = 1;
                echo json_encode($resp);
                exit;
            }

        }
        else
        {
            if ( $data['add_to_list'] )
            {
                if (!OW::getUser()->isAuthorized('hotlist', 'add_to_list'))
                {
                    $status = BOL_AuthorizationService::getInstance()->getActionStatus('hotlist', 'add_to_list');
                    $resp['error'] = $status['msg'];
                    echo json_encode($resp);
                    exit;
                }

                BOL_AuthorizationService::getInstance()->trackAction('hotlist', 'add_to_list');
                $service->addUser(OW::getUser()->getId());

                //            //Newsfeed
                //            $event = new OW_Event('feed.action', array(
                //                'pluginKey' => 'hotlist',
                //                'entityType' => 'add_to_hotlist',
                //                'entityId' => OW::getUser()->getId(),
                //                'userId' => OW::getUser()->getId()
                //            ), array(
                //                'string' => OW::getLanguage()->text('hotlist', 'user_entered_hot_list', array('displayName'=>BOL_UserService::getInstance()->getDisplayName(OW::getUser()->getId()))),
                //                'view' => array('iconClass' => 'ow_ic_heart'),
                //                'toolbar' => array(array(
                //                    'href' => OW::getRouter()->urlForRoute('hotlist-add-to-list'),
                //                    'label' =>  OW::getLanguage()->text('hotlist', 'are_you_hot_too')
                //                ))
                //            ));
                //            OW::getEventManager()->trigger($event);

                $resp['message'] = OW::getLanguage()->text('hotlist', 'user_added');
                $resp['added'] = 1;
                echo json_encode($resp);
                exit;
            }
        }
    }

}

class RemoveFromHotListForm extends Form
{
    public function __construct()
    {
        parent::__construct('removeFromHotListForm');

        $this->setAjax(true);
        $this->setAction(OW::getRouter()->urlFor('HOTLIST_CTRL_Index', 'ajax'));

        $this->setId('removeFromHotListForm');

        $remove_from_list = new HiddenField('remove_from_list');
        $remove_from_list->setValue(1);
        $this->addElement($remove_from_list);

        $submit = new Submit('remove');
        $submit->addAttribute('class', 'ow_ic_delete');
        $submit->setValue(OW::getLanguage()->text('hotlist', 'label_remove_btn_label'));

        $this->addElement($submit);

        $js = 'owForms["'.$this->getName().'"].bind("success", function(data){
            if ( data.error != undefined ){
                OW.error(data.error);
            }
            if ( data.message != undefined ){
                OW.info(data.message);
            }

            if ( data.removed != undefined && data.removed == 1)
            {
                //$("#add_to_list").html("'.OW::getLanguage()->text('hotlist', 'are_you_hot_too').'");
                OW.loadComponent("HOTLIST_CMP_Index", {},
                    {
                      onReady: function( html ){
                        if ($(".ow_box_empty.dashboard-HOTLIST_CMP_IndexWidget").length) {
                            $(".ow_box_empty.dashboard-HOTLIST_CMP_IndexWidget").empty().html(html);
                        }
                        
                        if ($(".ow_box.dashboard-HOTLIST_CMP_IndexWidget").length) {
                            $(".ow_box.dashboard-HOTLIST_CMP_IndexWidget").empty().html(html);
                        }
                      }
                    });
            }

            hotListFloatBox.close()
        });';

        OW::getDocument()->addOnloadScript($js);
    }
}

class AddToHotListForm extends Form
{
    public function __construct()
    {
        parent::__construct('addToHotListForm');

        $this->setAjax(true);
        $this->setAction(OW::getRouter()->urlFor('HOTLIST_CTRL_Index', 'ajax'));

        $this->setId('addToHotListForm');

        $add_to_list = new HiddenField('add_to_list');
        $add_to_list->setValue(1);
        $this->addElement($add_to_list);

        $submit = new Submit('add');
        $submit->addAttribute('class', 'ow_ic_add');

        $status = BOL_AuthorizationService::getInstance()->getActionStatus('hotlist', 'add_to_list');
        if (isset($status['authorizedBy']) && $status['authorizedBy'] == 'base')
        {
            $submit->setValue(OW::getLanguage()->text('hotlist', 'yes_btn_label'));
        }
        else
        {
            $submit->setValue(OW::getLanguage()->text('hotlist', 'label_add_btn_label'));
        }


        $this->addElement($submit);

        $js = 'owForms["'.$this->getName().'"].bind("success", function(data){
            if ( data.error != undefined ){
                OW.error(data.error);
            }
            if ( data.message != undefined ){
                OW.info(data.message);
            }

            if ( data.added != undefined && data.added == 1)
            {
                //$("#add_to_list").html("'.OW::getLanguage()->text('hotlist', 'remove_from_hot_list').'");

                OW.loadComponent("HOTLIST_CMP_Index", {},
                    {
                      onReady: function( html ){
                        if ($(".ow_box_empty.dashboard-HOTLIST_CMP_IndexWidget").length) {
                            $(".ow_box_empty.dashboard-HOTLIST_CMP_IndexWidget").empty().html(html);
                        }
                        
                        if ($(".ow_box.dashboard-HOTLIST_CMP_IndexWidget").length) {
                            $(".ow_box.dashboard-HOTLIST_CMP_IndexWidget").empty().html(html);
                        }
                      }
                    });
            }

            hotListFloatBox.close()
        });';

        OW::getDocument()->addOnloadScript($js);
    }
}