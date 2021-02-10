<?php

class HLOOKINGFOR_BOL_Service
{
    /**
     * Plugin key
     */
    const PLUGIN_KEY = 'hlookingfor';

    const SUPPLIERS_ACCOUNT_TYPE = '808aa8ca354f51c5a3868dad5298cd72';
    const BRIDE_OR_GROOM_ACCOUNT_TYPE = '8cc28eaddb382d7c6a94aeea9ec029fb';

    const FIELD_ALLOW_SUPPLIERS_CONTACT_YOU = 'allow_suppliers_contact_you';
    const FIELD_YES = 2;
    const FIELD_NO = 4;

    const FILTERED_METHODS = [
        'BOL_UserDao::findUserIdListByQuestionValues'
    ];

    private static $instance;

    /**
     * @return HLOOKINGFOR_BOL_Service
     */
    public static function getInstance()
    {
        if ( static::$instance == null )
        {
            try
            {
                static::$instance = OW::getClassInstance(static::class);
            }
            catch ( ReflectionException $ex )
            {
                static::$instance = new static();
            }
        }

        return static::$instance;
    }

    private function __construct()
    {

    }

    public function getGenderByAccounType( $accountType )
    {
        $accountType2Gender = SKADATE_BOL_AccountTypeToGenderService::getInstance()->findAll();
        if ( !empty($accountType2Gender) )
        {
            foreach ( $accountType2Gender as $item )
            {
                if ( $item->accountType == $accountType )
                {
                    return $item->genderValue;
                }
            }
        }

        return null;
    }

    public function saveMatchSexBySex( $userId )
    {
        $service = BOL_QuestionService::getInstance();

        $questionData = $service->getQuestionData([$userId], ['sex']);

        if ( !empty($questionData[$userId]['sex']) )
        {
            $sex = intval($questionData[$userId]['sex']);
            $accountTypes = $service->findAllAccountTypes();

            $matchSexValue = 0;
            foreach ( $accountTypes as $value )
            {
                $matchSexValue = $this->getGenderByAccounType($value->name);

                if ( $matchSexValue != $sex )
                {
                    break;
                }
            }

            if ( !empty($matchSexValue) )
            {
                $service->saveQuestionsData(['match_sex' => $matchSexValue], $userId);
            }
        }
    }
}
