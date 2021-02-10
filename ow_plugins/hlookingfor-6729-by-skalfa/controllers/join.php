<?php

class HLOOKINGFOR_CTRL_Join extends SKADATE_CTRL_Join
{
    public function __construct()
    {
        parent::__construct();
    }

    public function index($params)
    {
        parent::index($params);
        // if customfields plugin is active
        if (OW::getPluginManager()->isPluginActive('customfields')) {
            // replace default view with customfields view
            $this->setTemplate(OW::getPluginManager()->getPlugin('customfields')->getCtrlViewDir() . 'join_index.html');
        }
    }

    protected function joinUser( $joinData, $accountType, $params )
    {
        if ( isset($joinData['sex']) )
        {
            $sex = intval($joinData['sex']);

            $accountTypes = BOL_QuestionService::getInstance()->findAllAccountTypes();

            $matchSexValue = 0;
            foreach ( $accountTypes as $value )
            {
                $matchSexValue = HLOOKINGFOR_BOL_Service::getInstance()->getGenderByAccounType($value->name);

                if ( $matchSexValue != $sex )
                {
                    break;
                }
            }

            if ( $matchSexValue )
            {
                $joinData['match_sex'] = $matchSexValue;
            }
        }

        parent::joinUser($joinData, $accountType, $params);
    }
}
