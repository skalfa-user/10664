<?php

/**
 * Copyright (c) 2014, Skalfa LLC
 * All rights reserved.
 * 
 * ATTENTION: This commercial software is intended for exclusive use with SkaDate Dating Software (http://www.skadate.com) and is licensed under SkaDate Exclusive License by Skalfa LLC.
 * 
 * Full text of this license can be found at http://www.skadate.com/sel.pdf
 */
class SKADATE_CTRL_Ajax extends BASE_CTRL_Join
{
    public function promoImageUpload( $params )
    {
        if ( !OW::getUser()->isAuthenticated() || !OW::getUser()->isAuthorized('admin') )
        {
            throw new AuthorizationException();
        }
        
        if ( !OW::getRequest()->isAjax() || empty($_POST['command']) )
        {
            throw new Redirect404Exception();
        }
        
        $data = array();
        switch( $_POST['command'] ) 
        {
            case 'upload':

                $uploaddir = OW::getPluginManager()->getPlugin('skadate')->getUserFilesDir();

                if( !empty($_FILES[0]['name']) )
                {
                    $file = $_FILES[0];

                    if( !UTIL_File::validateImage($file['name']) )
                    {
                        echo json_encode(array('result' => false, 'message' => OW::getLanguage()->text('base', 'not_valid_image')));
                        exit;
                    }

                    $errorCode = $file['error'];

                    if ( $errorCode > 0 )
                    {
                        $error = BOL_FileService::getInstance()->getUploadErrorMessage($errorCode);
                        echo json_encode(array('result' => false, 'message' => $error));
                        exit;
                    }

                    if( !UTIL_File::checkDir($uploaddir) )
                    {
                        echo json_encode(array('result' => false, 'message' => OW::getLanguage()->text('base', 'not_valid_image')));
                        exit;
                    }
                    $path = SKADATE_BOL_Service::getInstance()->getPromoImagePath();
                    
                    if ( file_exists($path) )
                    {
                        unlink($path);
                    }

                    if( move_uploaded_file( $file['tmp_name'], $path ) )
                    {
                        $data = array('result' => true, 'url' => SKADATE_BOL_Service::getInstance()->getPromoImageUrl().'?a='. rand() * 1000000, 'message' => OW::getLanguage()->text('skadate', 'promo_image_upload_success'));

                        SKADATE_BOL_Service::getInstance()->setPromoImageUploaded();
                    }
                    else
                    {
                        $data = array('result' => false, 'message' => OW::getLanguage()->text('skadate', 'promo_image_upload_error'));
                    }
                }
                
                break;
                
            case 'delete':
                
                $path = SKADATE_BOL_Service::getInstance()->getPromoImagePath();
                @unlink($path);
                
                 SKADATE_BOL_Service::getInstance()->setPromoImageUploaded(false);
                
                $data = array('result' => true, 'message' => OW::getLanguage()->text('skadate', 'promo_image_deleted'));
                
                break;
        }
        
        echo json_encode($data);
        exit;
    }
}