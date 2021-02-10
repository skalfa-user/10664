<?php

/**
 * Copyright (c) 2014, Skalfa LLC
 * All rights reserved.
 *
 * ATTENTION: This commercial software is intended for exclusive use with SkaDate Dating Software (http://www.skadate.com) and is licensed under SkaDate Exclusive License by Skalfa LLC.
 *
 * Full text of this license can be found at http://www.skadate.com/sel.pdf
 */
class SKADATE_CMP_UploadPromoImage extends OW_Component
{
    public function __construct()
    {
        parent::__construct();
        
        OW::getDocument()->addScript(OW::getPluginManager()->getPlugin('skadate')->getStaticJsUrl().'ajax_file_upload.js');
        
        $js = " SKADATE_FileUploader.init($('.promo_image_upload'), {\$responderUrl}); ";
        
        OW::getDocument()->addOnloadScript(
            UTIL_JsGenerator::composeJsString($js, array("responderUrl" => OW::getRouter()->urlFor('SKADATE_CTRL_Ajax', 'promoImageUpload')))
        );
        
        $file = new FileField('image');
        $this->assign('image', $file->renderInput());
        
        $this->assign('src', false);
        
        if ( SKADATE_BOL_Service::getInstance()->isPromoImageUploaded() )
        {
            $this->assign('src', SKADATE_BOL_Service::getInstance()->getPromoImageUrl());
        }
    }
}

class UploadPromoImageForm extends Form 
{
    public function __construct()
    {
        parent::__construct('promo_image_upload');
        
        
        $this->addElement($file);
        
        $this->setAjax(true);
    }
}