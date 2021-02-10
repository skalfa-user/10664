<?php

/**
 * Copyright (c) 2017, Skalfa LLC
 * All rights reserved.
 *
 * ATTENTION: This commercial software is intended for use with Oxwall Free Community Software http://www.oxwall.com/
 * and is licensed under Oxwall Store Commercial License.
 *
 * Full text of this license can be found at http://developers.oxwall.com/store/oscl
 */

class CUSTOMINDEX_CLASS_BannerForm extends Form
{
    const FORM_NAME = 'banner-manager';

    const ELEMENT_BANNER_ID = 'banner-id';
    const ELEMENT_BANNER_FILE = 'banner-file';
    const ELEMENT_BANNER_CONTENT = 'banner-content';
    const ELEMENT_SUBMIT = 'banner-submit';

    public function __construct(CUSTOMINDEX_BOL_Banner $banner = null)
    {
        parent::__construct(self::FORM_NAME);

        $this->setEnctype(FORM::ENCTYPE_MULTYPART_FORMDATA);
        $this->setAction(OW::getRouter()->urlForRoute(CUSTOMINDEX_BOL_Service::PLUGIN_KEY . '.admin-banner'));

        $language = OW::getLanguage();

        $bannerFile = new FileField(self::ELEMENT_BANNER_FILE);
        $bannerFile->addAttribute('accept', 'image/*');
        $bannerFile->addValidator(new BannerFileValidator($bannerFile->getName()));
        $bannerFile->setLabel($language->text(CUSTOMINDEX_BOL_Service::PLUGIN_KEY, 'banner_file_label'));
        // $bannerFile->setRequired(true);
        $this->addElement($bannerFile);

        $content = new TextField(self::ELEMENT_BANNER_CONTENT);
        $content->setRequired(true);
        $content->setLabel($language->text(CUSTOMINDEX_BOL_Service::PLUGIN_KEY, 'banner_content_label'));
        $content->setDescription($language->text(CUSTOMINDEX_BOL_Service::PLUGIN_KEY, 'banner_content_description'));

        if ($banner !== null)
        {
            $this->setAction(OW::getRouter()->urlForRoute(CUSTOMINDEX_BOL_Service::PLUGIN_KEY . '.admin-banner-id-save', ['id' => $banner->id]));
            $content->setValue($banner->html);
        }

        $this->addElement($content);

        $submit = new Submit(self::ELEMENT_SUBMIT);
        $submit->setValue($language->text(CUSTOMINDEX_BOL_Service::PLUGIN_KEY, 'banner_submit_label'));
        $this->addElement($submit);
    }

    public function process()
    {
        if (OW::getRequest()->isPost() && !$this->isValid($_POST))
        {
            OW::getFeedback()->error(OW::getLanguage()->text(CUSTOMINDEX_BOL_Service::PLUGIN_KEY, 'banner_error_message'));

            return false;
        }

        if (OW::getRequest()->isPost() && $this->isValid($_POST)) 
        {
            $service = CUSTOMINDEX_BOL_Service::getInstance();

            $entity = $service->createBannerEntity(
                $_FILES[self::ELEMENT_BANNER_FILE]['name'],
                $this->getElement(self::ELEMENT_BANNER_CONTENT)->getValue()
            );

            $result = move_uploaded_file(
                $_FILES[self::ELEMENT_BANNER_FILE]['tmp_name'],
                OW::getPluginManager()->getPlugin(CUSTOMINDEX_BOL_Service::PLUGIN_KEY)->getUserFilesDir() . $entity->name
            );

            if (!$result)
            {
                $service->deleteBannerEntity($entity);
            }

            return $result;
        }

        return false;
    }

}

class BannerFileValidator  extends OW_Validator
{
    protected $fileName;

    public function __construct($fileName)
    {
        $this->fileName = $fileName;
    }

    function isValid($value)
    {
        return isset($_FILES[$this->fileName]) 
            && $_FILES[$this->fileName]['error'] === UPLOAD_ERR_OK 
            && is_uploaded_file($_FILES[$this->fileName]['tmp_name'])
            && getimagesize($_FILES[$this->fileName]['tmp_name']);
    }
}
