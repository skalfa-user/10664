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
 * Bookmarks Admin controller
 *
 * @author Kairat Bakitow <kainisoft@gmail.com>
 * @package ow.ow_plugins.bookmarks.controllers
 * @since 1.0
 */
class BOOKMARKS_CTRL_Admin extends ADMIN_CTRL_Abstract
{
    public function __construct()
    {
        parent::__construct();
        
        $item = new BASE_MenuItem();
        $item->setLabel(OW::getLanguage()->text('bookmarks', 'general_menu_item_label'));
        $item->setUrl(OW::getRouter()->urlForRoute('bookmarks.admin'));
        $item->setIconClass('ow_ic_gear_wheel');

        $this->addComponent('menu', new BASE_CMP_ContentMenu(array($item)));
    }

    public function index( array $params = array() )
    {
        OW::getDocument()->setHeading(OW::getLanguage()->text('bookmarks', 'general_heading_title'));
        
        $form = new BookmarksSettingsForm();
        
        if ( OW::getRequest()->isAjax() && $form->isValid($_POST) )
        {
            OW::getConfig()->saveConfig('bookmarks', 'notify_interval', $form->getElement('notify_interval')->getValue());
            
            exit();
        }
        
        $this->addForm($form);
    }
}

class BookmarksSettingsForm extends Form
{
    public function __construct()
    {
        parent::__construct('bookmarks_settings');
        
        $language = OW::getLanguage();
        
        $this->setAjax();
        $this->setAjaxResetOnSuccess(FALSE);
        $this->setAction(OW::getRouter()->urlForRoute('bookmarks.admin'));
        $this->bindJsFunction('success', 'function()
        {
            OW.info("' . $language->text('bookmarks', 'settings_saved') . '");
        }');
        
        $notifyIntervalConfigVal = OW::getConfig()->getValue('bookmarks', 'notify_interval');
        $notifyIntervalVal = array(
            0 => $language->text('bookmarks', 'remainderinterval_dont_send'),
            10 => $language->text('bookmarks', 'remainderinterval_10'),
            20 => $language->text('bookmarks', 'remainderinterval_20'),
            30 => $language->text('bookmarks', 'remainderinterval_30')
        );
        
        $notifyInterval = new Selectbox('notify_interval');
        $notifyInterval->addValidator(new BookmarkSelectboxValidator($notifyIntervalVal));
        $notifyInterval->setOptions($notifyIntervalVal);
        $notifyInterval->setValue($notifyIntervalConfigVal);
        $notifyInterval->setLabel($language->text('bookmarks', 'notify_interval_label'));
        $notifyInterval->setDescription($language->text('bookmarks', 'notify_interval_desc'));
        $notifyInterval->setHasInvitation(FALSE);
        $this->addElement($notifyInterval);
        
        $submit = new Submit('save');
        $submit->setValue($language->text('bookmarks', 'submit_label'));
        $this->addElement($submit);
    }
}

class BookmarkSelectboxValidator extends OW_Validator
{
    private $options;
    
    public function __construct( array $options )
    {
        $this->options = $options;
        $this->errorMessage = OW::getLanguage()->text('bookmarks', 'error_message_not_in_range');
    }
    
    public function getJsValidator()
    {
        return '
        {
            validate: function( value )
            {
                if ( ' . json_encode(array_keys($this->options)) . '.indexOf(+value) === -1 )
                {
                    throw ' . json_encode($this->getError()) . ';
                }
            },

            getErrorMessage: function()
            {
                return ' . json_encode($this->getError()) . ';
            }
        }';
    }

    public function isValid( $value )
    {
        return array_key_exists($value, $this->options) !== FALSE;
    }
}
