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
 *
 * @author Podiachev Evgenii <joker.OW2@gmail.com>
 * @package ow.ow_plugins.hotlist.mobile.components
 * @since 1.7.6
 */
class HOTLIST_MCMP_AddToHotlist extends OW_MobileComponent
{
    public function __construct()
    {
        $hotlistUser = HOTLIST_BOL_Service::getInstance()->findUserById(OW::getUser()->getId());
        
        $userInList = false;
        
        if ( $hotlistUser )
        {
            $userInList = true;
        }
        
        $this->assign("userInList", $userInList);
        
        $this->initButtons($hotlistUser);
    }
    
    protected function initButtons() 
    {
        $responderUrl = OW::getRouter()->uriForRoute('hotlist-add-remove-responder');
        
        $script = new UTIL_JsGenerator();
        
        $script->addScript("
                ; var prepareResult = function(data) { 
                      if ( !data )
                      {
                        return;
                      }

                      if ( (data.added == 1 ||  data.removed == 1) && window.mobileUserList )
                      {
                          window.mobileUserList.allowLoadData = true;
                          window.mobileUserList.process = false;
                          window.mobileUserList.renderedItems = [];

                          $(window.mobileUserList.node + \" .owm_content_list_item\").remove();

                          window.mobileUserList.loadData();

                          if ( data.message )
                          {
                            OWM.info(data.message);
                          }
                          
                          var addButton = $('.hotlist_add_button').parents('div.hotlist_buttons');
                          var removeButton = $('.hotlist_remove_button').parents('div.hotlist_buttons');
                          
                          if ( data.added == 1 )
                          {
                            addButton.hide();
                            removeButton.show();
                          }
                          
                          if ( data.removed == 1 )
                          {
                            addButton.show();
                            removeButton.hide();
                          }
                      }
                      else if ( data.error )
                      {
                          OWM.ajaxFloatBox('HOTLIST_MCMP_Notification', [data.error], {});
                      } 
                }
            ");
        
        $script->addScript("; $('.hotlist_add_button input').on('click', function() { 
            $.ajax({
                url: {\$responderUrl},
                type: 'POST',
                data: {'add_to_list': 'true'},
                dataType: 'json'
            }).done(function( data ) {
                prepareResult(data);

            });
        } );", array('responderUrl' => $responderUrl));
        
        $script->addScript("; $('.hotlist_remove_button input').on('click', function() { 
            $.ajax({
                url: {\$responderUrl},
                type: 'POST',
                data: {'remove_from_list': 'true'},
                dataType: 'json'
            }).done(function( data ) {
                prepareResult(data);
                
            }  ); } );  ", array('responderUrl' => $responderUrl));
        
        OW::getDocument()->addOnloadScript($script->generateJs());
    }
}