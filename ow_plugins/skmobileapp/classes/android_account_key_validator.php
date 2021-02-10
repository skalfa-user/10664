<?php

class SKMOBILEAPP_CLASS_AndroidAccountKeyValidator extends OW_Validator
{
    protected $fileName;
    protected $required;
    protected $validateAndroidPrivateKeyId;

    public function __construct( $fileName, $validateAndroidPrivateKeyId, $required = false )
    {
        $this->fileName = $fileName;
        $this->required = $required;
        $this->validateAndroidPrivateKeyId = $validateAndroidPrivateKeyId;

        if ( $this->required )
        {
            $errorMessage = OW::getLanguage()->text('base', 'form_validator_required_error_message');
            $this->setErrorMessage($errorMessage);
        }
    }

    public function isValid( $value )
    {
        $configs = OW::getConfig()->getValues('skmobileapp');

        if ( empty($_FILES[$this->fileName]['name']) && !$this->required )
        {
            return true;
        }

        if ( $this->required && empty($_FILES[$this->fileName]['name']) )
        {
            if ( empty($configs['inapps_apm_android_client_email'])
                || empty($configs['inapps_apm_android_private_key']) )
            {
                return false;
            }

            return true;
        }

        if ( $_FILES[$this->fileName]['error'] != UPLOAD_ERR_OK ) {
            $message = BOL_FileService::getInstance()->getUploadErrorMessage($_FILES[$this->fileName]['error']);
            $this->setErrorMessage($message);

            return false;
        }

        $content = file_get_contents($_FILES[$this->fileName]['tmp_name']);

        $list = @json_decode($content, true);

        if ( empty($list['private_key']) || empty($list['client_email']) )
        {
            $this->errorMessage = OW::getLanguage()->text('skmobileapp', 'invalid_android_account_key');

            return false;
        }

        return true;
    }

    public function getJsValidator()
    {
        if ( !$this->required )
        {
            return "{
                validate : function( value ){}
            }";
        }

        return ' {
            validate : function( value ){
                    if ( ( !$("' . $this->validateAndroidPrivateKeyId . '").get(0) || !$("' . $this->validateAndroidPrivateKeyId . '").get(0) ) )
                    {
                        if( $.isArray(value) ){ if(value.length == 0  ) throw ' . json_encode($this->getError()) . '; return;}
                        else if( !value || $.trim(value).length == 0 ){ throw ' . json_encode($this->getError()) . '; }
                    }
                },
            getErrorMessage : function(){ return ' . json_encode($this->getError()) . ' }
        } ';
    }
}