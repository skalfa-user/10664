<?php

class CVIDEOUPLOAD_CLASS_UploadVideoForm extends Form
{
    /**
     * @var CVIDEOUPLOAD_BOL_Service
     */
    private $service;

    protected $userId;

    /**
     * CVIDEOUPLOAD_CLASS_UploadVideoForm constructor.
     *
     * @param string $formName
     * @param $userId
     */
    public function __construct( $formName, $userId )
    {
        parent::__construct($formName);

        $this->setId($formName);
        $this->userId = intval($userId);
        $this->service = CVIDEOUPLOAD_BOL_Service::getInstance();
        $userIdList = [];
        $videoForPreview = null;

        $video = $this->service->findUserVideo($this->userId, false);

        if ( !empty($video) && $video->getStatus() == CVIDEOUPLOAD_BOL_Service::VIDEO_STATUS_NOT_CONFIRMED )
        {
            // TODO Подумать
//            $this->service->deleteVideosByUserId($userId);
//            $video = null;
        }

        if ( !empty($video) )
        {
            $videoForPreview = $this->service->getVideoForPreview($video);
        }

        // TODO добавить список юзеров

        if ( !empty($videoForPreview) && $video->getPrivacy() == CVIDEOUPLOAD_BOL_Service::VIDEO_PRIVACY_CERTAIN_USERS )
        {
            // TODO список юзеров

            $userIdList = $this->service->getFriendListByUserId($userId);
        }

        $videoUploadInput = new VideoField('custom_video_upload_input', $userId, $videoForPreview);
        $videoUploadInput->setRequired(true);
        $this->addElement($videoUploadInput);

        $privacyVal = [
            CVIDEOUPLOAD_BOL_Service::VIDEO_PRIVACY_EVERYBODY => $this->service->getLanguageText('privacy_everybody_label'),
            CVIDEOUPLOAD_BOL_Service::VIDEO_PRIVACY_ONLY_OWNER => $this->service->getLanguageText('privacy_only_owner_label'),
            CVIDEOUPLOAD_BOL_Service::VIDEO_PRIVACY_CERTAIN_USERS => $this->service->getLanguageText('privacy_certain_users_label')
        ];

        $privacy = new Selectbox('privacy');
        $privacy->setOptions($privacyVal);
        $privacy->setHasInvitation(false);
        $privacy->addValidator(new PrivacySelectBoxValidator($privacyVal));
        // TODO или default или значение
        $privacy->setValue( !empty($video) ? $video->getPrivacy() : CVIDEOUPLOAD_BOL_Service::VIDEO_PRIVACY_EVERYBODY );
        $this->addElement($privacy);

        $searchByUserNameField = new SearchByUserNameField('custom_user_search_input', $userId, $userIdList);
        $this->addElement($searchByUserNameField);

        $submit = new Submit('save');
        $submit->setValue($this->service->getLanguageText('btn_save'));
        $this->addElement($submit);
    }

    public function process()
    {
        $values = $this->getValues();

        $video = $this->service->findVideoByName($values['custom_video_upload_input']);

        if ( empty($video) )
        {
            $video = $this->service->findUserVideo($this->userId, false);
        }

        if ( !empty($video) && $video->userId == $this->userId )
        {
            /* @var CVIDEOUPLOAD_BOL_Video $video */

            $status = $video->status;

            if ( in_array($values['privacy'], [CVIDEOUPLOAD_BOL_Service::VIDEO_PRIVACY_EVERYBODY,
                CVIDEOUPLOAD_BOL_Service::VIDEO_PRIVACY_ONLY_OWNER,
                CVIDEOUPLOAD_BOL_Service::VIDEO_PRIVACY_CERTAIN_USERS]) )
            {
                $video->setPrivacy($values['privacy']);
            }

            if ( $video->status == CVIDEOUPLOAD_BOL_Service::VIDEO_STATUS_NOT_CONFIRMED )
            {
                $video->setStatus(CVIDEOUPLOAD_BOL_Service::VIDEO_STATUS_NOT_PROCESSED);
            }

            $video->setTimestamp();

            if ( !$this->service->isRequireApproval() )
            {
                $video->setAuthorization(CVIDEOUPLOAD_BOL_Service::VIDEO_AUTHORIZATION_APPROVED);
            }
            else
            {
                $video->setAuthorization(CVIDEOUPLOAD_BOL_Service::VIDEO_AUTHORIZATION_APPROVAL);
            }

            if ( $this->service->saveVideo($video) )
            {
                $this->service->deleteAllUserFriends($video->getUserId());

                if ( $values['privacy'] == CVIDEOUPLOAD_BOL_Service::VIDEO_PRIVACY_CERTAIN_USERS )
                {
                    $this->service->saveFriendByUserId($video->getUserId(), $values['custom_user_search_input']);
                }

                // TODO Считаем что видео только создано
                if ( $status == CVIDEOUPLOAD_BOL_Service::VIDEO_STATUS_NOT_CONFIRMED )
                {
                    $this->service->trackActionAdd($video->getId());
                }

                if ( $video->getAuthorization() == CVIDEOUPLOAD_BOL_Service::VIDEO_AUTHORIZATION_APPROVAL )
                {
                    OW::getEventManager()->trigger(new OW_Event(CVIDEOUPLOAD_BOL_Service::EVENT_VIDEO_EDIT, ['id' => $video->getId()]));
                }
            }

            $_POST = $_FILES = [];
            $this->reset();

            return $video;
        }

        return null;
    }
}

class PrivacySelectBoxValidator extends OW_Validator
{
    private $options;

    public function __construct( array $options )
    {
        $this->options = $options;
        $this->errorMessage = CVIDEOUPLOAD_BOL_Service::getInstance()->getLanguageText('error_message_not_in_privacy');
    }

    public function getJsValidator()
    {
        return '
        {
            validate: function( value )
            {
                if ( ' . json_encode(array_keys($this->options)) . '.indexOf(value) === -1 )
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

class VideoField extends Textarea
{
    /**
     * Upload component width
     */
    const UPLOAD_COMPONENT_WIDTH = 800;

    /**
     * Service
     *
     * @var CVIDEOUPLOAD_BOL_Service
     */
    protected $service;

    /**
     * user Id
     *
     * @var integer
     */
    protected $userId;

    /**
     * @var CVIDEOUPLOAD_BOL_Preview
     */
    protected $videoPreview;

    /**
     * Constructor.
     *
     * @param string $name
     * @param integer $userId
     * @param CVIDEOUPLOAD_BOL_Preview $videoPreview
     */
    public function __construct( $name, $userId, CVIDEOUPLOAD_BOL_Preview $videoPreview = null )
    {
        parent::__construct($name);

        $this->service = CVIDEOUPLOAD_BOL_Service::getInstance();
        $this->userId = $userId;
        $this->videoPreview = $videoPreview;

        if ( !empty($this->videoPreview) && !empty($this->videoPreview->getFileName()) )
        {
            $this->setValue($this->videoPreview->getFileName());
        }
    }

    /**
     * @see FormElement::renderInput()
     *
     * @param array $params
     * @return string
     */
    public function renderInput( $params = null )
    {
        $allowedMimeTypes = json_encode(array_unique($this->service->getAllowedFileTypes()));

        $maxSize = $this->service->getUploadFileSizeInBytes();
        $uploadUrl = OW::getRouter()->uriForRoute('cvideoupload.ajax-video-upload');
        $translations = json_encode([
            'upload_wrong_mime_type' => $this->service->getLanguageText('wrong_mime_type', [
                'types' => implode(', ', $this->service->getAllowedFileTypes())
            ]),
            'upload_wrong_file_size' => $this->service->getLanguageText('wrong_file_size', [
                'maxFileSize' => $this->service->getUploadFileSizeInMegabytes()
            ]),
            'upload_wrong_files_count' =>$this->service->getLanguageText('wrong_files_count'),
            'now_uploading' => $this->service->getLanguageText('now_uploading'),
            'uploaded_successfully' => $this->service->getLanguageText('uploaded_successfully'),
            'upload_failed' => $this->service->getLanguageText('upload_failed')
        ]);

        $document = OW::getDocument();
        $plugin = OW::getPluginManager()->getPlugin(CVIDEOUPLOAD_BOL_Service::PLUGIN_KEY);

        // init static js files
        $document->addScript($plugin->getStaticJsUrl() . 'jquery.ui.widget.js');
        $document->addScript($plugin->getStaticJsUrl() . 'jquery.iframe-transport.js');
        $document->addScript($plugin->getStaticJsUrl() . 'jquery.fileupload.js');
        $document->addScript($plugin->getStaticJsUrl() . 'video_upload.js');

        // init css
        $document->addStyleSheet($plugin->getStaticCssUrl() . 'file_upload.css');

        $viewTitle = json_encode($this->service->getLanguageText('view_video'));
        $viewError = json_encode($this->service->getLanguageText('video_view_error'));

        $windowWidth = self::UPLOAD_COMPONENT_WIDTH;

        // init on load js
        $onLoadJs = <<<JS
            var fileUpload = new VideoUpload({
                allowedMimeTypes: {$allowedMimeTypes},
                maxFileSize: {$maxSize},
                userId: {$this->userId},
                progressWrapperId: 'video-upload-progress-wrapper',
                progressValWrapperId: 'video-upload-progress-val',
                progressValLabelWrapperId: 'video-upload-progress-val-label',
                progressLabelWrapperId: 'video-upload-progress-label',
                cancelButtonId: 'video-upload-cancel',
                okButtonId: 'video-upload-ok',
                fileUploadId: 'video_upload_file',
                dropZone: 'video-upload-wrapper',
                url: '{$uploadUrl}',
                translations: {$translations}
            });

            fileUpload.init();
JS;
        $onLoadJs .= <<<JS
            // delete video handler
            $('#video-delete').click(function(e){
                e.preventDefault();

                // clear input value
                $('#{$this->getId()}').val('');

                // hide preview area
                $('#video-preview-wrapper').addClass('ow_hidden');
            });

            // preview video handler
            $('#video-upload-cover').click(function(e){
                e.preventDefault();

                if ($(this).data('status') != 'processed') {
                    OW.error({$viewError});

                    return;
                }

                OW.ajaxFloatBox('CVIDEOUPLOAD_CMP_ViewVideo', [$(this).data('name')], {
                    title: $viewTitle,
                    width: $windowWidth
                });
            });

            //-- init observers --//

            // update field
            OW.bind('videoUploadSuccess', function(data) {
                $('#video-upload-cover')
                    .data('status', data.status)
                    .data('name', data.fileName)
                    .attr('src', data.coverImage);

                $('#video-status').html(data.statusLabel);
                $('#video-preview-wrapper').removeClass('ow_hidden');

                // update input value
                $('#{$this->getId()}').val(data.fileName);
            });
JS;

        $document->addOnloadScript($onLoadJs);

        //-- init question presentation --//


        $input = new VideoUploadFieldRender();

        // init view vars
        $input->assign('allowedMimeTypes', implode(', ', array_unique($this->service->getAllowedFileTypes(true))));
        $input->assign('maxFileSize', $this->service->getUploadFileSizeInMegabytes());
        $input->assign('videoPreview', $this->videoPreview);
        $input->assign('fieldName', $this->getName());
        $input->assign('fieldId', $this->getId());
        $input->assign('fieldValue', $this->getValue());

        return $input->render();
    }
}

class VideoUploadFieldRender extends OW_Component
{
    public function __construct()
    {
        $plugin = OW::getPluginManager()->getPlugin(CVIDEOUPLOAD_BOL_Service::PLUGIN_KEY);

        $this->setTemplate($plugin->getCmpViewDir()  .'video_upload_field.html');
    }
}

class SearchByUserNameField extends Textarea
{
    /**
     * Service
     *
     * @var CVIDEOUPLOAD_BOL_Service
     */
    protected $service;

    /**
     * user Id
     *
     * @var integer
     */
    protected $userId;

    /**
     * @var array
     */
    protected $userIdList;

    /**
     * @var CVIDEOUPLOAD_CMP_UserList| null
     */
    protected $previewUserList = null;

    /**
     * Constructor.
     *
     * @param string $name
     * @param integer $userId
     * @param array $userIdList
     */
    public function __construct( $name, $userId, $userIdList = [] )
    {
        parent::__construct($name);

        $this->service = CVIDEOUPLOAD_BOL_Service::getInstance();
        $this->userId = $userId;
        $this->userIdList = (array) $userIdList;

        if ( !empty($this->userIdList) )
        {
            $this->previewUserList = new CVIDEOUPLOAD_CMP_UserList($this->userIdList);
            $this->setValue($this->userIdList);
        }
    }

    public function setValue($value)
    {
        if ( is_array($value) )
        {
            parent::setValue(implode(',', $value));
        }
        else
        {
            parent::setValue($value);
        }
    }

    /**
     * @see FormElement::renderInput()
     *
     * @param array $params
     * @return string
     */
    public function renderInput( $params = null )
    {
        $uploadUrl = OW::getRouter()->uriForRoute('cvideoupload.ajax-video-autocomplete');
        $translations = json_encode([
            'added_user_label' =>$this->service->getLanguageText('added_user_label'),
            'remove_user_button_label' => $this->service->getLanguageText('remove_user_button_label')
        ]);

        $document = OW::getDocument();
        $plugin = OW::getPluginManager()->getPlugin(CVIDEOUPLOAD_BOL_Service::PLUGIN_KEY);

        // init static js files
        $document->addScript($plugin->getStaticJsUrl() . 'search_users.js');

        // init css
        $document->addStyleSheet($plugin->getStaticCssUrl() . 'search_users.css');

        $inputUserNameSearch = 'input-user-name-search';
        $userIdListSearchResult = 'user-list-search-result';
        $loadingContent = 'loading-content';
        $scrollLiveSearchResult = 'scroll-live-search-result';

        // init on load js
        $onLoadJs = <<<JS
            var customSearchUserList = new CustomSearchUserList({
                userId: {$this->userId},
                userItemAdded: 'user_item_added',
                removeUserLabel: 'remove_user_label',
                ajaxResponder: '{$uploadUrl}',
                translations: {$translations},
                inputUserNameSearch: '{$inputUserNameSearch}',
                userListSearchResult: '{$userIdListSearchResult}',
                loadingContent: '{$loadingContent}',
                scrollLiveSearchResult: '{$scrollLiveSearchResult}',
                userSearchInput: '{$this->getName()}'
            });

            customSearchUserList.init();
JS;

        $document->addOnloadScript($onLoadJs);
        //-- init question presentation --//

        $input = new SearchByUserNameFieldRender();

        // init view vars
        $input->assign('previewUserList', (!empty($this->previewUserList)) ? $this->previewUserList->render() : null);
        $input->assign('fieldName', $this->getName());
        $input->assign('fieldId', $this->getId());
        $input->assign('fieldValue', $this->getValue());
        $input->assign('inputUserNameSearch', $inputUserNameSearch);
        $input->assign('userListSearchResult', $userIdListSearchResult);
        $input->assign('loadingContent', $loadingContent);
        $input->assign('scrollLiveSearchResult', $scrollLiveSearchResult);

        return $input->render();
    }
}

class SearchByUserNameFieldRender extends OW_Component
{
    public function __construct()
    {
        $plugin = OW::getPluginManager()->getPlugin(CVIDEOUPLOAD_BOL_Service::PLUGIN_KEY);

        $this->setTemplate($plugin->getCmpViewDir()  .'user_search_field.html');
    }
}