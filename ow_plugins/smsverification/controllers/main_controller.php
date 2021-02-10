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

/**
 * Paymentwall order pages controller
 *
 * @author Pryadkin Sergey <GiperProger@gmail.com>
 * @package ow.ow_plugins.smsverification.controllers
 * @since 1.7.6
 */
class SMSVERIFICATION_CTRL_MainController extends OW_ActionController 
{
    private $lang;
    /**
     * @var SMSVERIFICATION_BOL_Service
     */
    private $service;

    public function __construct() 
    {
        $this->lang = OW::getLanguage();
        $this->service = SMSVERIFICATION_BOL_Service::getInstance();        
    }

    protected function setMasterPage()
    {
        OW::getEventManager()->bind('base.append_markup', array($this, 'catcher'));
        OW::getDocument()->getMasterPage()->setTemplate(OW::getThemeManager()->getMasterPageTemplate(OW_MasterPage::TEMPLATE_BLANK));
    }

    public function catcher(BASE_CLASS_EventCollector $collector)
    {
        $cmp = new SMSVERIFICATION_CMP_FloatBox();
        $collector->add($cmp->render());
    }
    
    public function inputNumber()
    {
        if(OW::getRequest()->isAjax())
        {
            exit("{}");
        }


        $this->setMasterPage();


        $form = new SMSVERIFICATION_CLASS_InputNumberForm();
        $this->addForm($form);
        
        if( OW::getRequest()->isPost() && $form->isValid($_POST))
        {         
            try
            {
                $form->sendMessage();
                $this->redirect(OW::getRouter()->urlForRoute('smsverification_send_code', array()));
            }
            catch(Exception $ex)
            {
                OW::getFeedback()->error( $ex->getMessage() );
                $this->redirect();
            }                                
        }
        
        $this->assign('countryName', $this->lang->text( 'smsverification', 'select_country' ));
        
        $countries = $this->service->getCountries();
        $length = ceil(count($countries) / 2);
        $this->assign('countries1', array_slice($countries, 0, $length));
        $this->assign('countries2', array_slice($countries, $length));

        
        $script = UTIL_JsGenerator::composeJsString('
        $("#txtnation-edit-country").click(function(){
            window.chooseCountryFloatbox = new OW_FloatBox({
                $title: {$title}, 
                $contents: $("#countries-floatbox").children(), 
                width: "650px"
            });
        });
        
        $("#btn-set-country").click(function(){
        
        if ( $("#countries-content [name=country_code]:checked").length === 0 )
                {
                    OW.error({$errorMsg});
                    
                    return false;
                }
        var $option = $("#countries-content input[name=country_code]:checked");
        var country = $option.data("name");
        var code = $option.data("code");
        $("#txtnation-current-country").text(country);
        $("#code-ceil").text(code);
        var input = owForms["inputNumber-form"].getElement("countryName");
        input.setValue(country);
        input.removeErrors();

        window.chooseCountryFloatbox.close();
        });    

        ', array(
            'title' => $this->lang->text('smsverification', 'choose_country'),
            'url' => OW::getRouter()->urlFor('SMSVERIFICATION_CTRL_MainController', 'ajaxResponder'),
            'errorMsg' => OW::getLanguage()->text( 'smsverification', 'select_country_error' )
        ));        
        
        OW::getDocument()->addOnloadScript($script);
        $this->setPageHeading(OW::getLanguage()->text('smsverification', 'user_verification'));
        $this->assign('page_description', OW::getLanguage()->text('smsverification', 'input_number_page_description'));
        
    }
    
    public function inputCode()
    {
        if(OW::getRequest()->isAjax())
        {
            exit("{}");
        }

        $this->setMasterPage();
        
        $form = new SMSVERIFICATION_CLASS_InputCodeForm();
        $this->addForm($form);
        $userId = OW::getUser()->getId();
        $userData = $this->service->getUserDataByUserId($userId);
        $this->assign('userCountry', $userData[0]->country);
        $this->assign('countryCode', $userData[0]->countryCode);
        $this->assign('telNumber', $userData[0]->number);        
        
        if ( OW::getRequest()->isPost() && $form->isValid($_POST) )
        {
            if(isset($_POST['changeNumber_btn']))
            {
                $this->service->resetUserData($userId);
                $this->redirect(OW::getRouter()->uriForRoute('smsverification_prepare_data', array()));
            }
            $values = $form->getValues();

            $checkCode = $this->service->checkCode($userId, $values['userCode']);
            
            if($checkCode)
            {
                $this->service->setUserAsAutorized($userId);
                OW::getFeedback()->info(OW::getLanguage()->text('smsverification', 'success_verification'));
                $this->redirect(OW_URL_HOME);
            }
            else
            {
                OW::getFeedback()->error($this->lang->text( 'smsverification', 'incorrect_code' ));
                $this->redirect();
            }
            
        }
        $this->setPageHeading(OW::getLanguage()->text('smsverification', 'user_verification'));
                
    }
   
}
