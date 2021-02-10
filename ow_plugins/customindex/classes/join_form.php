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

class CUSTOMINDEX_CLASS_JoinForm extends BASE_CLASS_UserQuestionForm
{
    const SESSION_JOIN_DATA = 'joinData';

    const SESSION_JOIN_STEP = 'joinStep';

    const SESSION_REAL_QUESTION_LIST = 'join.real_question_list';

    const SESSION_ALL_QUESTION_LIST = 'join.all_question_list';

    const SESSION_START_STAMP = 'join.session_start_stamp';

    protected $post = array();
    protected $stepCount = 1;
    protected $isLastStep = false;
    protected $displayAccountType = false;
    public  $questions = array();
    protected $sortedQuestionsList = array();
    protected $questionListBySection = array();
    protected $questionValuesList = array();
    protected $accountType = null;
    protected $isBot = false;
    protected $data = array();
    protected $toggleClass = '';

    public function __construct( $controller )
    {
        parent::__construct('joinForm');

        $this->setId('joinForm');

        $stamp = OW::getSession()->get(self::SESSION_START_STAMP);

        $this->clearSession();

        OW::getSession()->set(CUSTOMINDEX_CLASS_JoinForm::SESSION_JOIN_STEP, 1);

        if ( empty($stamp) )
        {
            OW::getSession()->set(self::SESSION_START_STAMP, time());
        }

        unset($stamp);

        $this->checkSession();

        $joinSubmitLabel = "";

        // get available account types from DB
        $accounts = $this->getAccountTypes();

        $joinData = OW::getSession()->get(self::SESSION_JOIN_DATA);

        if ( !isset($joinData) || !is_array($joinData) )
        {
            $joinData = array();
        }

        $accountsKeys = array_keys($accounts);
        $this->accountType = $accountsKeys[0];

        if ( isset($joinData['accountType']) )
        {
            $this->accountType = trim($joinData['accountType']);
        }

        $step = $this->getStep();

        if ( count($accounts) > 1 )
        {
            $this->stepCount = 2;

            $this->displayAccountType = false;
            $joinSubmitLabel = OW::getLanguage()->text('base', 'join_submit_button_continue');
        }

        $joinSubmit = new Submit('joinSubmit');
        $joinSubmit->addAttribute('class', 'ow_button ow_ic_submit');
        $joinSubmit->setValue($joinSubmitLabel);
        $this->addElement($joinSubmit);

        $this->init($accounts);

        $this->getQuestions();

        $section = null;

        $questionNameList = array();
        $this->sortedQuestionsList = array();

        foreach ( $this->questions as $sort => $question )
        {
            if ( (string) $question['base'] === '0' && $step === 2 || $step === 1 )
            {
                if ( $section !== $question['sectionName'] )
                {
                    $section = $question['sectionName'];
                }

                //$this->questionListBySection[$section][] = $this->questions[$sort];
                $questionNameList[] = $this->questions[$sort]['name'];
                $this->sortedQuestionsList[] = $this->questions[$sort];
            }
        }

        $this->questionValuesList = BOL_QuestionService::getInstance()->findQuestionsValuesByQuestionNameList($questionNameList);

        $this->addFakeQuestions();
        $this->addQuestions($this->sortedQuestionsList, $this->questionValuesList, $this->updateJoinData());

        $this->setQuestionsLabel();

        $this->addClassToBaseQuestions();

        $controller->assign('step', $step);
        $controller->assign('questionArray', $this->questionListBySection);
        $controller->assign('displayAccountType', $this->displayAccountType);
        $controller->assign('isLastStep', $this->isLastStep);
    }

    protected function init( array $accounts )
    {
        if ( $this->displayAccountType )
        {
            $joinAccountType = new Selectbox('accountType');
            $joinAccountType->setLabel(OW::getLanguage()->text('base', 'questions_question_account_type_label'));
            $joinAccountType->setRequired();
            $joinAccountType->setOptions($accounts);
            $joinAccountType->setValue($this->accountType);
            $joinAccountType->setHasInvitation(false);

            $this->addElement($joinAccountType);
        }
    }

    public function checkSession()
    {
        $stamp = BOL_QuestionService::getInstance()->getQuestionsEditStamp();
        $sessionStamp = OW::getSession()->get(self::SESSION_START_STAMP);

        if ( !empty($sessionStamp) && $stamp > $sessionStamp )
        {
            OW::getSession()->delete(self::SESSION_ALL_QUESTION_LIST);
            OW::getSession()->delete(self::SESSION_JOIN_DATA);
            OW::getSession()->delete(self::SESSION_JOIN_STEP);
            OW::getSession()->delete(self::SESSION_REAL_QUESTION_LIST);
            OW::getSession()->delete(self::SESSION_START_STAMP);

            if ( OW::getRequest()->isPost() )
            {
                UTIL_Url::redirect(OW::getRouter()->urlForRoute('base_join'));
            }
        }
    }

    public function setQuestionsLabel()
    {
        foreach ( $this->sortedQuestionsList as $question )
        {
            if ( !empty($question['realName']) )
            {
                $event = new OW_Event('base.questions_field_add_label_join', $question, true);

                OW::getEventManager()->trigger($event);

                $data = $event->getData();

                if( !empty($data['label']) )
                {
                    $this->getElement($question['name'])->setLabel($data['label']);
                }
                else
                {
                    $this->getElement($question['name'])->setLabel(OW::getLanguage()->text('base', 'questions_question_' . $question['realName'] . '_label'));
                }

            }
        }
    }

    public function addClassToBaseQuestions()
    {
        foreach ( $this->sortedQuestionsList as $question )
        {
            if ( !empty($question['realName']) )
            {
                if ( $question['realName'] == 'username' )
                {
                    $this->getElement($question['name'])->addAttribute("class", "ow_username_validator");
                }

                if ( $question['realName'] == 'email' )
                {
                    $this->getElement($question['name'])->addAttribute("class", "ow_email_validator");
                }
            }
        }
    }

    protected function toggleQuestionClass()
    {
        $class = 'ow_alt1';
        switch ( $this->toggleClass )
        {
            case null:
            case 'ow_alt2':
                break;
            case 'ow_alt1':
                $class = 'ow_alt2';
        }

        $this->toggleClass = $class;

        return $class;
    }

    protected function randQuestionClass()
    {
        $rand = rand(0, 1);

        if ( !$rand )
        {
            $class = 'ow_alt1';
        }
        else
        {
            $class = 'ow_alt2';
        }

        return $class;
    }

    protected function addFakeQuestions()
    {
        $step = $this->getStep();
        $realQuestionList = array();
        $valueList = $this->questionValuesList;
        $this->questionValuesList = array();
        $this->sortedQuestionsList = array();
        $this->questionListBySection = array();
        $section = '';

        $oldQuestionList = OW::getSession()->get(self::SESSION_REAL_QUESTION_LIST);
        $allQuestionList = OW::getSession()->get(self::SESSION_ALL_QUESTION_LIST);

        if ( !empty($oldQuestionList) && !empty($allQuestionList) )
        {
            $realQuestionList = $oldQuestionList;
            $this->sortedQuestionsList = $allQuestionList;

            foreach ( $this->sortedQuestionsList as $key => $question )
            {
                $this->questionListBySection[$question['sectionName']][] = $question;

                if ( $question['fake'] == true )
                {
                    $this->addDisplayNoneClass(preg_replace('/\s+(ow_alt1|ow_alt2)/', '', $question['trClass']));
                }
                else
                {
                    $this->addEmptyClass(preg_replace('/\s+(ow_alt1|ow_alt2)/', '', $question['trClass']));
                }

                if ( !empty($valueList[$question['realName']]) )
                {
                    $this->questionValuesList[$question['name']] = $valueList[$question['realName']];
                }
            }
        }
        else
        {
            foreach ( $this->questions as $sort => $question )
            {
                if ( (string) $question['base'] === '0' && $step === 2 || $step === 1 )
                {
                    if ( $section !== $question['sectionName'] )
                    {
                        $section = $question['sectionName'];
                    }

                    $event = new OW_Event('base.questions_field_add_fake_questions', $question, true);

                    OW::getEventManager()->trigger($event);

                    $addFakes = $event->getData();

                    if ( !$addFakes || in_array( $this->questions[$sort]['presentation'], array('password', 'range') ) )
                    {
                        $this->questions[$sort]['fake'] = false;
                        $this->questions[$sort]['realName'] = $question['name'];

                        $this->questions[$sort]['trClass'] = $this->toggleQuestionClass();

                        if ( $this->questions[$sort]['presentation'] == 'password' )
                        {
                            $this->toggleQuestionClass();
                        }

                        $this->sortedQuestionsList[$question['name']] = $this->questions[$sort];
                        $this->questionListBySection[$section][] = $this->questions[$sort];

                        if ( !empty($valueList[$question['name']]) )
                        {
                            $this->questionValuesList[$question['name']] = $valueList[$question['name']];
                        }

                        continue;
                    }

                    $fakesCount = rand(2, 5);
                    $fakesCount = $fakesCount + 1;
                    $randId = rand(0, $fakesCount);

                    for ( $i = 0; $i <= $fakesCount; $i++ )
                    {
                        $randName = uniqid(UTIL_String::getRandomString(rand(5, 13), 2));
                        $question['trClass'] = uniqid('ow_'. UTIL_String::getRandomString(rand(5, 10), 2));

                        if ( $i == $randId )
                        {
                            $realQuestionList[$randName] = $this->questions[$sort]['name'];
                            $question['fake'] = false;
                            $question['required'] = $this->questions[$sort]['required'];

                            $this->addEmptyClass($question['trClass']);

                            $question['trClass'] = $question['trClass'] . " " . $this->toggleQuestionClass();

                        }
                        else
                        {
                            $question['required'] = 0;
                            $question['fake'] = true;

                            $this->addDisplayNoneClass($question['trClass']);

                            $question['trClass'] = $question['trClass'] . " " . $this->randQuestionClass();
                        }

                        $question['realName'] = $this->questions[$sort]['name'];

                        $question['name'] = $randName;

                        $this->sortedQuestionsList[$randName] = $question;

                        if ( !empty($valueList[$this->questions[$sort]['name']]) )
                        {
                            $this->questionValuesList[$randName] = $valueList[$this->questions[$sort]['name']];
                        }

                        $this->questionListBySection[$section][] = $question;
                    }
                }
            }
        }

        if ( OW::getRequest()->isPost() )
        {
            $this->post = $_POST;

            if ( empty($oldQuestionList) )
            {
                $oldQuestionList = array();
            }

            if ( empty($allQuestionList) )
            {
                $allQuestionList = array();
            }

            if ( $oldQuestionList && $allQuestionList )
            {
                foreach ( $oldQuestionList as $key => $value )
                {
                    $newKey = array_search($value, $realQuestionList);

                    if ( $newKey !== false && isset($_POST[$key]) && isset($realQuestionList[$newKey]) )
                    {
                        $this->post[$newKey] = $_POST[$key];
                    }
                }

                foreach ( $allQuestionList as $question )
                {
                    if ( !empty($question['fake']) && !empty($_POST[$question['name']]) )
                    {
                        $this->isBot = true;
                    }
                }
            }
        }

        if ( $this->isBot )
        {
            $event = new OW_Event('base.bot_detected', array('isBot' => true));
            OW::getEventManager()->trigger($event);
        }

        OW::getSession()->set(self::SESSION_REAL_QUESTION_LIST, $realQuestionList);
        OW::getSession()->set(self::SESSION_ALL_QUESTION_LIST, $this->sortedQuestionsList);
    }

    protected function updateJoinData()
    {
        $joinData = OW::getSession()->get(self::SESSION_JOIN_DATA);

        if ( empty($joinData) )
        {
            return;
        }

        $this->data = $joinData;

        $list = OW::getSession()->get(self::SESSION_REAL_QUESTION_LIST);

        if ( !empty($list) )
        {
            foreach ( $list as $fakeName => $realName )
            {
                if ( !empty($joinData[$realName]) )
                {
                    unset($this->data[$realName]);
                    $this->data[$fakeName] = $joinData[$realName];
                }
            }
        }

        return $this->data;
    }

    public function getRealValues()
    {
        $list = $this->sortedQuestionsList;

        $values = $this->getValues();
        $result = array();

        if ( !empty($list) )
        {
            foreach ( $values as $fakeName => $value )
            {
                if ( !empty($list[$fakeName]) && isset($list[$fakeName]['fake']) && $list[$fakeName]['fake'] == false )
                {
                    $result[$list[$fakeName]['realName']] = $value;
                }

                if ( $fakeName == 'accountType' )
                {
                    $result[$fakeName] = $value;
                }
            }

            if ( !empty($result['sex']) )
            {

                $gender2accountType = SKADATE_BOL_AccountTypeToGenderService::getInstance()->findAll();

                if ( !empty($gender2accountType) )
                {
                    /* @var $dto SKADATE_BOL_AccountTypeToGender */
                    foreach ( $gender2accountType as $dto )
                    {
                        if ( $dto->genderValue == $result['sex'] )
                        {
                            $result['accountType'] = $dto->accountType;
                        }
                    }
                }
            }
        }

        return $result;
    }

    public function getStep()
    {
        return 1;
    }

    public function getQuestions()
    {
        $this->questions = BOL_QuestionService::getInstance()->findBaseSignUpQuestions();

        $questionDtoList = BOL_QuestionService::getInstance()->findQuestionByNameList(array('sex', 'match_sex'));

        if ( !empty($questionDtoList['sex']) )
        {
            $sex = get_object_vars($questionDtoList['sex']);
            array_push($this->questions, $sex);
        }

        if ( !empty($questionDtoList['match_sex']) )
        {
            $matchSex = get_object_vars($questionDtoList['match_sex']);
            array_push($this->questions, $matchSex);
        }
    }

    protected function addLastStepQuestions( $controller )
    {
        $displayPhoto = false;

        $displayPhotoUpload = OW::getConfig()->getValue('base', 'join_display_photo_upload');
        $avatarValidator = OW::getClassInstance("BASE_CLASS_AvatarFieldValidator", false);

        switch ( $displayPhotoUpload )
        {
            case BOL_UserService::CONFIG_JOIN_DISPLAY_AND_SET_REQUIRED_PHOTO_UPLOAD :
                $avatarValidator = OW::getClassInstance("BASE_CLASS_AvatarFieldValidator", true);

            case BOL_UserService::CONFIG_JOIN_DISPLAY_PHOTO_UPLOAD :
                $userPhoto = OW::getClassInstance("BASE_CLASS_JoinUploadPhotoField", 'userPhoto');
                $userPhoto->setLabel(OW::getLanguage()->text('base', 'questions_question_user_photo_label'));
                $userPhoto->addValidator($avatarValidator);
                $this->addElement($userPhoto);

                $displayPhoto = true;
        }

        $displayTermsOfUse = false;

        if ( OW::getConfig()->getValue('base', 'join_display_terms_of_use') )
        {
            $termOfUse = new CheckboxField('termOfUse');
            $termOfUse->setLabel(OW::getLanguage()->text('base', 'questions_question_user_terms_of_use_label'));
            $termOfUse->setRequired();

            $this->addElement($termOfUse);

            $displayTermsOfUse = true;
        }

        $this->setEnctype('multipart/form-data');

        $event = new OW_Event('join.get_captcha_field');
        OW::getEventManager()->trigger($event);
        $captchaField = $event->getData();

        $displayCaptcha = false;

        $enableCaptcha = OW::getConfig()->getValue('base', 'enable_captcha');

        if ( $enableCaptcha && !empty($captchaField) && $captchaField instanceof FormElement )
        {
            $captchaField->setName('captchaField');
            $this->addElement($captchaField);
            $displayCaptcha = true;
        }

        $controller->assign('display_captcha', $displayCaptcha);
        $controller->assign('display_photo', $displayPhoto);
        $controller->assign('display_terms_of_use', $displayTermsOfUse);

        if ( OW::getRequest()->isPost() )
        {
            if ( !empty($captchaField) && $captchaField instanceof FormElement )
            {
                $captchaField->setValue(null);
            }

            if ( isset($userPhoto) && isset($_FILES[$userPhoto->getName()]['name']) )
            {
                $_POST[$userPhoto->getName()] = $_FILES[$userPhoto->getName()]['name'];
            }
        }
    }

    protected function addFieldValidator( $formField, $question )
    {
        $list = OW::getSession()->get(self::SESSION_ALL_QUESTION_LIST);

        $questionInfo = empty($list[$question['name']]) ? null : $list[$question['name']];

        if ( (string) $question['base'] === '1' )
        {
            if ( !empty($questionInfo['realName']) && $questionInfo['realName'] === 'email' && $questionInfo['fake'] == false )
            {
                $formField->addValidator(new BASE_CLASS_JoinEmailValidator());
            }

            if ( !empty($questionInfo['realName']) && $questionInfo['realName'] === 'username' && $questionInfo['fake'] == false )
            {
                $formField->addValidator(new BASE_CLASS_JoinUsernameValidator());
            }

            if ( $question['name'] === 'password' )
            {
                $passwordRepeat = BOL_QuestionService::getInstance()->getPresentationClass($question['presentation'], 'repeatPassword');
                $passwordRepeat->setLabel(OW::getLanguage()->text('base', 'questions_question_repeat_password_label'));
                $passwordRepeat->setRequired((string) $question['required'] === '1');
                $this->addElement($passwordRepeat);

                $formField->addValidator(new PasswordValidator());
            }
        }
    }

    protected function setFieldOptions( $formField, $questionName, array $questionValues )
    {
        $realQuestionList = OW::getSession()->get(self::SESSION_REAL_QUESTION_LIST);

        $name = $questionName;
        if ( !empty($realQuestionList[$questionName]) )
        {
            $name = $realQuestionList[$questionName];
        }

        parent::setFieldOptions($formField, $name, $questionValues);
    }

    public function isBot()
    {
        return $this->isBot;
    }

    public function isLastStep()
    {
        return $this->isLastStep;
    }

    public function getPost()
    {
        return $this->post;
    }

    public function getAccountType()
    {
        return $this->accountType;
    }

    public function addEmptyClass( $className )
    {
        OW::getDocument()->addStyleDeclaration("
            .{$className}
            {

            } ");
    }

    public function addDisplayNoneClass( $className )
    {
        OW::getDocument()->addStyleDeclaration("
            .{$className}
            {
                display:none !important;
            } ");
    }

    public function clearSession()
    {
        OW::getSession()->delete(self::SESSION_REAL_QUESTION_LIST);
        OW::getSession()->delete(self::SESSION_ALL_QUESTION_LIST);
    }

    public function getSortedQuestionsList()
    {
        return $this->sortedQuestionsList;
    }
}

if (!class_exists('PasswordValidator'))
{
    class PasswordValidator extends BASE_CLASS_PasswordValidator
    {

        /**
         * Constructor.
         *
         * @param array $params
         */
        public function __construct()
        {
            parent::__construct();
        }

        /**
         * @see Validator::getJsValidator()
         *
         * @return string
         */
        public function getJsValidator()
        {
            return "{
                validate : function( value )
                {
                    if( !window.join.validatePassword() )
                    {
                        throw window.join.errors['password']['error'];
                    }
                },
                getErrorMessage : function()
                {
                       if( window.join.errors['password']['error'] !== undefined ){ return window.join.errors['password']['error'] }
                       else{ return " . json_encode($this->getError()) . " }
                }
        }";
        }
    }
}
