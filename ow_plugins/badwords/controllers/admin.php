<?php

/**
 * Copyright (c) 2014, Skalfa LLC
 * All rights reserved.
 *
 * ATTENTION: This commercial software is intended for use with Oxwall Free Community Software http://www.oxwall.org/ and is licensed under SkaDate Exclusive License by Skalfa LLC.
 *
 * Full text of this license can be found at http://www.skadate.com/sel.pdf
 */

/**
 * @author Kairat Bakytow
 * @package ow_plugins.badwords.controllers
 * @since 1.1
 */
class BADWORDS_CTRL_Admin extends ADMIN_CTRL_Abstract
{
    private $service;
    
    public function __construct()
    {
        parent::__construct();
        
        $this->service = BADWORDS_BOL_Service::getInstance();
    }
    
    public function index( $params = null )
    {
        $document = OW::getDocument();
        $plugin = OW::getPluginManager()->getPlugin('badwords');
        
        $document->addStyleSheet($plugin->getStaticCssUrl() . 'colorpicker.css');
        $document->addStyleSheet($plugin->getStaticCssUrl() . 'layout.css');
        
        $document->addScript($plugin->getStaticJsUrl() . 'colorpicker.js');
        $document->addScript($plugin->getStaticJsUrl() . 'badwords-admin.js');
        $document->addOnloadScript(';badwordsAdmin.getInstance();', 1500);
        
        $badwordsForm = new BADWORDS_BadWordsForm();
        $this->addForm($badwordsForm);
        
        $censorForm = new CensorForm();
        $this->addForm($censorForm);
        
        if ( OW::getRequest()->isPost() )
        {
            if ( $badwordsForm->isValid($_POST) )
            {
                $editBadwords = $badwordsForm->getElement('editBadwords');

                if ( $editBadwords->getValue() )
                {
                    $this->service->deleteBadwordsByIdList(explode(',', $editBadwords->getValue()));
                }

                if ( $this->service->saveBadwods(preg_split('/\n\r?/', $badwordsForm->getElement('badwords')->getValue())) )
                {
                    OW::getFeedback()->info('Bad word(s) added');
                }
            }
            
            if ( !empty($_POST['command']) && !empty($_POST['word']) )
            {
                if ( $this->service->deleteBadwordsByIdList($_POST['word']) )
                {
                    OW::getFeedback()->info('Bad word(s) deleted');
                }
            }
            
            $badwordsForm->reset();
        }
        
        $page = !empty($_GET['page']) && (int)$_GET['page'] ? abs((int)$_GET['page']) : 1;
        $this->assign('badwords', $this->service->findBadwords($page, 30));
        
        $pages = (int)ceil($this->service->countBadwords() / 30);
        $paging = new BASE_CMP_Paging($page, $pages, 20);
        $this->addComponent('paging', $paging);
    }
    
    public function rsp( $params = NULL )
    {
        $censorForm = new CensorForm();
        
        if ( OW::getRequest()->isAjax() && OW::getRequest()->isPost() && $censorForm->isValid($_POST) )
        {
            OW::getConfig()->saveConfig('badwords', 'censorText', $censorForm->getElement('censor')->getValue());
            OW::getConfig()->saveConfig('badwords', 'censorColor', $censorForm->getElement('censorColor')->getValue());
                
            exit(json_encode(array('message' => 'Settings successfully saved')));
        }
    }
}

class BADWORDS_BadWordsForm extends Form
{
    public function __construct()
    {
        parent::__construct('badwordsForm');
        
        $hidden = new HiddenField('editBadwords');
        $this->addElement($hidden);
        
        $languages = OW::getLanguage();
        
        $textarea = new Textarea('badwords');
        $textarea->setRequired();
        $textarea->setHasInvitation(TRUE);
        $textarea->setInvitation($languages->text('base', 'form_element_common_invitation_text'));
        $this->addElement($textarea);
        
        $submit = new Submit('save');
        $submit->setValue('Save');
        $this->addElement($submit);
    }
}

class CensorForm extends Form
{
    public function __construct()
    {
        parent::__construct( 'consorForm' );
        
        $this->setAjax(TRUE);
        $this->setAction(OW::getRouter()->urlForRoute('badwords.admin-rsp'));
        $this->setAjaxResetOnSuccess(FALSE);
        $this->bindJsFunction(Form::BIND_SUCCESS, 'function(data){if(data.message){OW.info(data.message);}}');
        
        $censor = new TextField('censor');
        $censor->setRequired(TRUE);
        $censor->setValue(OW::getConfig()->getValue('badwords', 'censorText'));
        $this->addElement($censor);
        
        $color = new COLOR_Field('censorColor');
        $color->setValue(OW::getConfig()->getValue('badwords', 'censorColor'));
        $this->addElement($color);
        
        $submit = new Submit('save');
        $submit->setValue('Save');
        $this->addElement($submit);
    }
}

class COLOR_Field extends TextField
{
    public function renderInput( $params = null )
    {
        parent::renderInput($params);
        
        $this->addAttribute('class', 'colorSelector');
        
        return UTIL_HtmlTag::generateTag('div', $this->attributes, true, '<div></div>');
    }
    
    public function getElementJs()
    {
        return 'var formElement = new BadwordsColorField(' . json_encode($this->getId()) . ', ' . json_encode($this->getName()) . ',' . json_encode($this->getValue()) .');';
    }
}
