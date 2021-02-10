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

class CVIDEOUPLOAD_CTRL_Ajax extends OW_ActionController
{
    /**
     * Response success
     */
    const RESPONSE_SUCCESS = 'success';

    /**
     * Response error
     */
    const RESPONSE_ERROR = 'error';

    /**
     * Service
     *
     * @var CVIDEOUPLOAD_BOL_Service
     */
    protected $service;

    /**
     * Class constructor
     */
    public function __construct()
    {
        parent::__construct();

        $this->service = CVIDEOUPLOAD_BOL_Service::getInstance();
    }

    /**
     * Uploads
     */
    public function upload()
    {
        // validate video
        if ( !empty($_FILES['video']['tmp_name'])
            && !$_FILES['video']['error']
            && in_array(mime_content_type($_FILES['video']['tmp_name']), $this->service->getAllowedFileTypes()))
        {
            // check file size
            if ( $_FILES['video']['size'] > $this->service->getUploadFileSizeInBytes() )
            {
                echo json_encode([
                    'status' => self::RESPONSE_ERROR,
                    'message' => $this->service->getLanguageText('upload_failed_size_error_desc', [
                        'maxSize' => $this->service->getUploadFileSizeInMegabytes()
                    ]),
                    'data' => []
                ]);

                exit;
            }

            $userId = !empty($_GET['userId']) && OW::getUser()->getId() == intval($_GET['userId'])
                ? intval($_GET['userId'])
                : 0;

            if ( empty($userId) )
            {
                echo json_encode([
                    'status' => self::RESPONSE_ERROR,
                    'message' => $this->service->getLanguageText('upload_failed_user_error_desc'),
                    'data' => []
                ]);

                exit;
            }

            $isAdmin = OW::getUser()->isAdmin();

            $video = $this->service->uploadVideoFile($_FILES['video'], $userId, $isAdmin);

            if ( $video )
            {
                // save file
                echo json_encode([
                    'status' => self::RESPONSE_SUCCESS,
                    'message' => 'ok',
                    'data' => get_object_vars($this->service->getVideoForPreview($video))
                ]);

                exit;
            }
        }

        echo json_encode([
            'status' => self::RESPONSE_ERROR,
            'message' => $this->service->getLanguageText('upload_failed_error_desc'),
            'data' => []
        ]);

        exit;
    }

    public function autocomplete()
    {
        if ( strlen($searchVal = trim($_POST['searchVal'])) === 0 )
        {
            exit(json_encode(['success' => false, 'content' => 'User name is empty']));
        }

        $ignore = (!empty($_POST['addedUser'])) ? $_POST['addedUser'] : [];

        $userIdList =  $this->service->getSearchResult($searchVal, $ignore);

        $cmp = new CVIDEOUPLOAD_CMP_UserList($userIdList, true);

        exit(json_encode(['success' => true, 'content' => $cmp->render()]));
    }
}
