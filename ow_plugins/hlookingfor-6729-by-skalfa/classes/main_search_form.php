<?php

class HLOOKINGFOR_CLASS_MainSearchForm extends USEARCH_CLASS_MainSearchForm
{
    protected function addGenderQuestions($controller, $accounts, $questionValueList, $questionData)
    {
        $controller->assign('displayGender', false);
        $controller->assign('displayAccountType', false);

        if ( count($accounts) > 1  )
        {
            $controller->assign('displayAccountType', true);

            if ( !OW::getUser()->isAuthenticated() )
            {
                $sex = new HiddenField('sex');

                if ( !empty($questionData['sex']) )
                {
                    $sex->setValue($questionData['sex']);
                }
                else
                {
                    if ( !empty($questionValueList['sex']['values'][0]) )
                    {
                        $val = $questionValueList['sex']['values'][0];

                        /* @var BOL_QuestionValue $val */

                        $sex->setValue($val->value);
                    }
                }

                $this->addElement($sex);
            }
            else
            {
                $sexData = BOL_QuestionService::getInstance()->getQuestionData(array(OW::getUser()->getId()), array('sex'));

                if ( !empty($sexData[OW::getUser()->getId()]['sex']) )
                {
                    $sex = new HiddenField('sex');
                    $sex->setValue($sexData[OW::getUser()->getId()]['sex']);
                    $this->addElement($sex);
                }
            }

            $matchSex = new Selectbox('match_sex');
            $matchSex->setLabel(BOL_QuestionService::getInstance()->getQuestionLang('match_sex'));
            $matchSex->setRequired();
            $matchSex->setHasInvitation(false);

            $this->setFieldOptions($matchSex, 'match_sex', $questionValueList['sex']);

            if ( !empty($questionData['match_sex']) )
            {
                $matchSex->setValue($questionData['match_sex']);
            }

            $this->addElement($matchSex);
        }
    }
}