<?php

class CUSTOMPROFILEVIEW_CMP_Questions extends OW_Component
{
    private $questionService;

    public function __construct()
    {
        parent::__construct();
        $this->questionService = BOL_QuestionService::getInstance();
    }

    public function onBeforeRender()
    {
        parent::onBeforeRender();

        $handler = OW::getRequestHandler()->getHandlerAttributes();

        if ($handler[OW_RequestHandler::ATTRS_KEY_CTRL] == 'BASE_CTRL_ComponentPanel' && $handler[OW_RequestHandler::ATTRS_KEY_ACTION] == 'profile' && OW::getUser()->isAuthenticated()) {
            $params = $handler[OW_RequestHandler::ATTRS_KEY_VARLIST];
            $userDto = BOL_UserService::getInstance()->findByUsername($params['username']);

//            if ($userDto->id != OW::getUser()->getId()) {
                $adminMode = OW::getUser()->isAdmin() || OW::getUser()->isAuthorized('base');
                $questions = BASE_CMP_UserViewWidget::getUserViewQuestions($userDto->id, $adminMode);

                $questionsBySections = $this->questionService->findAllQuestionsBySectionForAccountType('all');

                foreach ($questionsBySections as $section => $questionsBySection) {
                    if (in_array($section, [CUSTOMPROFILEVIEW_CMP_Gallery::SECTION_BASIC])) {
                        unset($questionsBySections[$section]);
                    }
                    if ($section == CUSTOMPROFILEVIEW_CMP_Gallery::SECTION_DETAILED) {
                        $detailedArray = [];
                        foreach ($questionsBySections[$section] as $detailedKey => $detailedQuestion) {
                            if (in_array($questionsBySections[$section][$detailedKey]['name'], [CUSTOMPROFILEVIEW_CMP_Gallery::QUESTION_ABOUT_ME])) {
                                unset($questionsBySections[$section][$detailedKey]);
                            } else {
                                $detailedArray[] = $detailedQuestion;
                            }
                        }

                        $questionsBySections[$section] = $detailedArray;
                    }
                }

//                $settings = BOL_ComponentEntityService::getInstance()->findSettingList('profile-BASE_CMP_AboutMeWidget', $userDto->id, array(
//                    'content'
//                ));
//
//                $aboutMeTitle = OW::getLanguage()->text('base', 'about_me_widget_default_title');
//                $aboutMeContent = empty($settings['content']) ? null : $settings['content'];
                $aboutMeTitle = $questions['labels'][CUSTOMPROFILEVIEW_CMP_Gallery::QUESTION_ABOUT_ME];
                $aboutMeContent = $questions['data'][$userDto->id][CUSTOMPROFILEVIEW_CMP_Gallery::QUESTION_ABOUT_ME];

                $this->assign('aboutMeTitle', $aboutMeTitle);
                $this->assign('aboutMeContent', $aboutMeContent);

                $ownerMode = false;
                if ($userDto->id == OW::getUser()->getId()) {
                    $ownerMode = true;
                    $this->addForm(new AboutMeForm('profile-BASE_CMP_AboutMeWidget', $aboutMeContent));
                }
                $this->assign('ownerMode', $ownerMode);
                $this->assign('noContent', $aboutMeContent === null);

                $this->assign('questionsBySections', $questionsBySections);
                $this->assign('questionsData', $questions['data'][$userDto->id]);
                $this->assign('labels', $questions['labels']);

                $this->assign('sectionDetailed', CUSTOMPROFILEVIEW_CMP_Gallery::SECTION_DETAILED);
                $this->assign('sectionPartnerPreference', CUSTOMPROFILEVIEW_CMP_Gallery::SECTION_PARTNER_PREFERENCE);
                $this->assign('questionPartnerDescription', CUSTOMPROFILEVIEW_CMP_Gallery::QUESTION_PARTNER_DESCRIPTION);
//            }
        }
    }
}