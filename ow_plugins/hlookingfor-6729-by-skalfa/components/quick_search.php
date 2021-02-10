<?php

class HLOOKINGFOR_CMP_QuickSearch extends USEARCH_CMP_QuickSearch
{
    protected $searchService;
    protected $questionService;

    public function __construct()
    {
        parent::__construct();

        $this->questionService = BOL_QuestionService::getInstance();
        $this->searchService = USEARCH_BOL_Service::getInstance();

        $form = OW::getClassInstance('USEARCH_CLASS_QuickSearchForm', $this);
        /* @var USEARCH_CLASS_QuickSearchForm $form */

        $this->setTemplate(OW::getPluginManager()->getPlugin('usearch')->getCmpViewDir() . 'quick_search.html');

        $questionNameList = $this->searchService->getQuickSerchQuestionNames();
        $questionValueList = $this->questionService->findQuestionsValuesByQuestionNameList($questionNameList);

        $elements = $form->getElements();

        if ( isset($elements['sex']) )
        {
            $form->deleteElement('sex');

            $sex = new HiddenField('sex');

            if ( !empty($questionValueList['sex']['values'][0]) )
            {
                $val = $questionValueList['sex']['values'][0];

                /* @var BOL_QuestionValue $val */

                $sex->setValue($val->value);
            }

            $form->addElement($sex);
        }

        $this->addForm($form);

        $this->assign('form', $form);
        $this->assign('advancedUrl', OW::getRouter()->urlForRoute('users-search'));
        $this->assign('questions', USEARCH_BOL_Service::getInstance()->getQuickSerchQuestionNames());

        $this->assign('displayGender', false);

    }
}
