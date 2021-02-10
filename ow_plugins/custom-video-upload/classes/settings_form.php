<?php

/**
 * Copyright (c) 2020, Skalfa LLC
 * All rights reserved.
 *
 * ATTENTION: This commercial software is intended for use with Oxwall Free Community Software http://www.oxwall.com/
 * and is licensed under Oxwall Store Commercial License.
 *
 * Full text of this license can be found at http://developers.oxwall.com/store/oscl
 */

class CVIDEOUPLOAD_CLASS_SettingsForm extends Form
{
    const ELEMENT_WATERMARK_FILE = 'watermark';

    /**
     * Service
     *
     * @var CVIDEOUPLOAD_BOL_Service
     */
    protected $service;

    /**
     * Constructor
     */
    public function __construct( $name )
    {
        parent::__construct( $name );

        $this->setEnctype(FORM::ENCTYPE_MULTYPART_FORMDATA);

        $this->service = CVIDEOUPLOAD_BOL_Service::getInstance();

        // ffmpeg path input
        $ffmpegPath = new TextField('ffmpegPath');
        $ffmpegPath->setValue($this->service->getConfigValue('ffmpegPath'));
        $ffmpegPath->setLabel($this->service->getLanguageText('admin_settings_ffmpeg_path_label'));
        $ffmpegPath->setRequired(true);
        $this->addElement($ffmpegPath);


        // file size input
        $fileSize = new TextField('fileSize');
        $fileSize->setValue($this->service->getUploadFileSizeInMegabytes());
        $fileSize->setLabel($this->service->getLanguageText('admin_settings_file_size_label'));
        $fileSize->setDescription($this->service->getLanguageText('admin_settings_file_size_desc'));
        $fileSize->setRequired(true);

        $validator = new FloatValidator(CVIDEOUPLOAD_BOL_Service::MIN_FILE_SIZE, $this->service->getMaxUploadFileSizeInMegabytes());
        $validator->setErrorMessage($this->service->getLanguageText('file_size_validation_error', [
            'minSize' => CVIDEOUPLOAD_BOL_Service::MIN_FILE_SIZE,
            'maxSize' => $this->service->getMaxUploadFileSizeInMegabytes()
        ]));
        $fileSize->addValidator($validator);

        $this->addElement($fileSize);


        // max duration input
        $maxDuration = new TextField('maxDuration');
        $maxDuration->setValue($this->service->getConfigValue('maxDuration'));
        $maxDuration->setLabel($this->service->getLanguageText('admin_settings_max_duration_label'));
        $maxDuration->setRequired(true);

        $validator = new FloatValidator(CVIDEOUPLOAD_BOL_Service::MIN_VIDEO_DURATION);
        $validator->setErrorMessage($this->service->getLanguageText('video_duration_validation_error', [
            'minDuration' => CVIDEOUPLOAD_BOL_Service::MIN_VIDEO_DURATION
        ]));
        $maxDuration->addValidator($validator);

        $this->addElement($maxDuration);

        $options = [];

        foreach( CVIDEOUPLOAD_BOL_Service::CONVERT_MIME_TYPES as $type )
        {
            $options[$type] = trim($type, '.');
        }
        unset($type);

        $typeOutput = new Selectbox('typeOutput');
        $typeOutput->setOptions($options);
        $typeOutput->setValue( $this->service->getConfigValue('typeOutput') );
        $typeOutput->setHasInvitation(false);
        $typeOutput->setLabel($this->service->getLanguageText('admin_settings_type_output_label'));
        $typeOutput->setDescription($this->service->getLanguageText('admin_settings_type_output_desc'));
        $typeOutput->setRequired(true);
        $this->addElement($typeOutput);


        $options = [];
        $typeInputValue = $this->service->getConfigValue('typeInput');

        if ( !empty($typeInputValue) )
        {
            $typeInputValue = unserialize($typeInputValue);
        }

        foreach( CVIDEOUPLOAD_BOL_Service::ALLOWED_MIME_TYPES as $input )
        {
            $options[$input] = $input;
        }
        unset($input);

        $typeOutput = new CheckboxGroup('typeInput');
        $typeOutput->setColumnCount(2);
        $typeOutput->setOptions($options);
        $typeOutput->setValue( $typeInputValue );
        $typeOutput->setLabel($this->service->getLanguageText('admin_settings_type_input_label'));
        $typeOutput->setDescription($this->service->getLanguageText('admin_settings_type_input_desc'));
        $typeOutput->setRequired(true);
        $this->addElement($typeOutput);

        $watermark = new CVIDEOUPLOAD_CLASS_ImageField(self::ELEMENT_WATERMARK_FILE);
        $watermark->setValue($this->service->getWatermarkUrl());
        $watermark->setLabel($this->service->getLanguageText('admin_settings_watermark_label'));
        $watermark->addAttribute('accept', 'image/*');
        $watermark->addValidator(new CVIDEOUPLOAD_CLASS_BannerFileValidator($watermark->getName()));
        $this->addElement($watermark);

        $enabled = new CheckboxField('watermarkEnabled');
        $enabled->setValue((boolean) $this->service->getConfigValue('watermarkEnabled'));
        $enabled->setLabel($this->service->getLanguageText('admin_settings_watermark_enabled_label'));
        $this->addElement($enabled);

        $submit = new Submit('submit');
        $submit->setValue($this->service->getLanguageText('admin_settings_submit_label'));
        $this->addElement($submit);
    }

    public function process()
    {
        $this->service->saveConfigValue('fileSize', $this->service->convertMegabytesToBytes($this->getElement('fileSize')->getValue()));
        $this->service->saveConfigValue('ffmpegPath', $this->getElement('ffmpegPath')->getValue());
        $this->service->saveConfigValue('maxDuration', intval($this->getElement('maxDuration')->getValue()));
        $this->service->saveConfigValue('typeOutput', $this->getElement('typeOutput')->getValue());
        $this->service->saveConfigValue('typeInput', serialize($this->getElement('typeInput')->getValue()));
        $this->service->saveConfigValue('watermarkEnabled', (boolean) $this->getElement('watermarkEnabled')->getValue());

        // save watermark
        if ( !empty($_FILES[self::ELEMENT_WATERMARK_FILE]['name']) )
        {
            // remove watermark
            if ( !empty($this->service->getConfigValue('watermark')) )
            {
                unlink($this->service->getWatermarkImageUserFilesDir($this->service->getConfigValue('watermark')));

                $this->service->saveConfigValue('watermark', '');
            }

            // generate watermark name
            $name = $this->service->generateWatermarkName($_FILES[self::ELEMENT_WATERMARK_FILE]['name']);

            // move watermark user files
            $result = move_uploaded_file(
                $_FILES[self::ELEMENT_WATERMARK_FILE]['tmp_name'], $this->service->getWatermarkImageUserFilesDir($name)
            );

            if ( $result )
            {
                $this->service->saveConfigValue('watermark', $name);
            }
        }
    }
}

class CVIDEOUPLOAD_CLASS_ImageField extends FormElement
{
    public function __construct( $name )
    {
        parent::__construct( $name );

        $this->addAttribute('type', 'file');
    }

    public function getValue()
    {
        return isset($_FILES[$this->getName()]) ? $_FILES[$this->getName()] : null;
    }

    public function renderInput( $params = null )
    {
        parent::renderInput($params);

        OW::getDocument()->addStyleSheet(OW::getPluginManager()->getPlugin(CVIDEOUPLOAD_BOL_Service::PLUGIN_KEY)->getStaticCssUrl() . 'image_field.css');

        $output = '';

        if ( !is_array($this->value) && $this->value !== null && ( trim($this->value) !== 'none' ) )
        {
            $this->value = 'url(' . $this->value . ')';
            $randId = 'if' . rand(10, 10000000);

            $script = "$('#" . $randId . "').click(function(){
                new OW_FloatBox({\$title:'" . OW::getLanguage()->text(CVIDEOUPLOAD_BOL_Service::PLUGIN_KEY, 'graphics_preview_cap_label') . "', \$contents:$('#image_view_" . $this->getName() . "'), width:'550px'});
            });";

            OW::getDocument()->addOnloadScript($script);

            $output .= '
                <div class="clearfix">
                    <a id="' . $randId . '" href="javascript://" class="control_image" style="background-image:' . $this->value . ';"></a>
                </div>
                
                <div style="display:none;">
                    <div class="preview_graphics" id="image_view_' . $this->getName() . '" style="background-image:' . $this->value . '"></div>
                </div>';
        }

        $output .= UTIL_HtmlTag::generateTag('input', $this->attributes);

        return $output;
    }
}

class CVIDEOUPLOAD_CLASS_BannerFileValidator  extends OW_Validator
{
    private $language;
    protected $fileName;

    public function __construct($fileName)
    {
        $this->fileName = $fileName;
        $this->language = OW::getLanguage();

        $this->setErrorMessage($this->language->text(CVIDEOUPLOAD_BOL_Service::PLUGIN_KEY, 'not_valid_image'));
    }

    public function isValid($value)
    {
        if ( empty($_FILES[$this->fileName]['name']) )
        {
            return true;
        }

        if ( isset($_FILES[$this->fileName]['name']) &&
            !UTIL_File::validateImage($_FILES[$this->fileName]['name']) &&
            !getimagesize($_FILES[$this->fileName]['tmp_name']) )
        {
            $this->setErrorMessage($this->language->text(CVIDEOUPLOAD_BOL_Service::PLUGIN_KEY, 'file_no_image'));

            return false;
        }

        $maxUploadSize = OW::getConfig()->getValue('base', 'attch_file_max_size_mb');

        $result = UTIL_File::checkUploadedFile($_FILES[$this->fileName], $maxUploadSize * 1024 * 1024);
        if ( $result['result'] == false )
        {
            if ( isset($result['message']) )
            {
                $this->setErrorMessage($result['message']);
            }
            return false;
        }

        return isset($_FILES[$this->fileName])
            && $_FILES[$this->fileName]['error'] === UPLOAD_ERR_OK
            && is_uploaded_file($_FILES[$this->fileName]['tmp_name'])
            && getimagesize($_FILES[$this->fileName]['tmp_name']);
    }
}
