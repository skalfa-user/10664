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

/**
 * Class SKTEXTCR_BOL_Service
 *
 * @author (A. S. B.) (D. P.) <azazel9966@gmail.com>.
 */
class SKTEXTCR_BOL_Service
{
    use OW_Singleton;

    const FORM_NAME_BY_NAME = 'SearchByUserName';
    const FORM_NAME_FROM_TO = 'SearchFromTo';
    const FORM_NAME_BAD_WORD = 'SearchBadWord';

    const CONFIG_NAME_SEARCH_TYPE = 'searchType';
    const CONFIG_NAME_SEARCH_TYPE_RESULT = 'searchTypeResult';

    const CONFIG_TYPE_SEARCH_BY_USER_NAME = 'user_name';
    const CONFIG_TYPE_SEARCH_FROM_TO = 'from_to';
    const CONFIG_TYPE_SEARCH_BAD_WORD = 'bad_word';

    const INTERVAL = 30 * 24 * 60 * 60;


    /**
     * Plugin key
     */
    const PLUGIN_KEY = 'sktextcr';

    /**
     * @var OW_Config
     */
    private $config;

    /**
     * @var OW_Language
     */
    private $language;

    /**
     * Class constructor
     */
    private function __construct()
    {
        $this->config = OW::getConfig();
        $this->language = OW::getLanguage();
    }

    public function getLanguageText( $key, array $vars = null )
    {
        return $this->language->text(self::PLUGIN_KEY, $key, $vars);
    }

    public function setLanguageKeyForJs( $key )
    {
        $this->language->addKeyForJs(self::PLUGIN_KEY, $key);
    }

    public function getConfigValue($name)
    {
        return $this->config->getValue(self::PLUGIN_KEY, $name);
    }

    public function saveConfigValue($name, $value)
    {
        $this->config->saveConfig(self::PLUGIN_KEY, $name, $value );
    }

    public function clearData()
    {
        $this->saveConfigValue(self::CONFIG_NAME_SEARCH_TYPE, '');
        $this->saveConfigValue(self::CONFIG_NAME_SEARCH_TYPE_RESULT, '');
    }

    public function getDataSearch()
    {
        $dataSearch = new SKTEXTCR_BOL_DataSearch();

        $type = $this->getConfigValue(self::CONFIG_NAME_SEARCH_TYPE);
        $result = $this->getConfigValue(self::CONFIG_NAME_SEARCH_TYPE_RESULT);

        if ( !empty($result) )
        {
            $result = json_decode($result, true);
        }

        $dataSearch->type = !empty($type) ? $type : null;

        if ( !empty($type) )
        {
            switch ($type)
            {
                case self::CONFIG_TYPE_SEARCH_BY_USER_NAME:

                    $dataSearch->userId = $result['userId'];
                    $dataSearch->userName = $result['userName'];

                    break;

                case self::CONFIG_TYPE_SEARCH_FROM_TO:

                    $dataSearch->senderId = $result['senderId'];
                    $dataSearch->recipientId = $result['recipientId'];
                    $dataSearch->userNameFrom = $result['userNameFrom'];
                    $dataSearch->userNameTo = $result['userNameTo'];

                    break;

                case self::CONFIG_TYPE_SEARCH_BAD_WORD:

                    $dataSearch->badWord = $result['badWord'];

                    break;
            }
        }

        return $dataSearch;
    }

    public function initDataSearch( SKTEXTCR_BOL_DataSearch $data )
    {
        if ( !empty($data) )
        {
            switch ($data->type)
            {
                case self::CONFIG_TYPE_SEARCH_BY_USER_NAME:

                    $user = $this->searchUserByUserName($data->userName);
                    $userId = 0;

                    if ( !empty($user) )
                    {
                        $userId = $user->getId();
                    }

                    $this->saveConfigValue(self::CONFIG_NAME_SEARCH_TYPE_RESULT, json_encode(['userId' => $userId, 'userName' => $data->userName]));
                    $this->saveConfigValue(self::CONFIG_NAME_SEARCH_TYPE, self::CONFIG_TYPE_SEARCH_BY_USER_NAME);

                    break;

                case self::CONFIG_TYPE_SEARCH_FROM_TO:

                    $userSender = $this->searchUserByUserName($data->userNameFrom);
                    $userRecipient = $this->searchUserByUserName($data->userNameTo);

                    $senderId = 0;
                    $recipientId = 0;

                    if ( !empty($userSender) && !empty($userRecipient) )
                    {
                        $senderId = $userSender->getId();
                        $recipientId = $userRecipient->getId();
                    }

                    $this->saveConfigValue(self::CONFIG_NAME_SEARCH_TYPE_RESULT, json_encode([
                        'senderId' => $senderId,
                        'recipientId' => $recipientId,
                        'userNameFrom' => $data->userNameFrom,
                        'userNameTo' => $data->userNameTo
                    ]));

                    $this->saveConfigValue(self::CONFIG_NAME_SEARCH_TYPE, self::CONFIG_TYPE_SEARCH_FROM_TO);

                    break;

                case self::CONFIG_TYPE_SEARCH_BAD_WORD:

                    $this->saveConfigValue(self::CONFIG_NAME_SEARCH_TYPE_RESULT, json_encode(['badWord' => trim($data->badWord)]));
                    $this->saveConfigValue(self::CONFIG_NAME_SEARCH_TYPE, self::CONFIG_TYPE_SEARCH_BAD_WORD);

                    break;
            }
        }

        return;
    }

    /**
     * @param $userName
     *
     * @return BOL_User | null
     */
    public function searchUserByUserName( $userName )
    {
        $obj = $this->findUserByUserName($userName);

        if ( empty($obj) )
        {
            $obj = $this->findUserByName($userName);
        }

        return $obj;
    }

    /**
     * @param $userName
     *
     * @return null | BOL_User
     */
    public function findUserByUserName( $userName )
    {
        $bolUser = BOL_UserDao::getInstance();

        $queryParts = $bolUser->getUserQueryFilter('u', 'id', [
            'method' => 'BOL_UserDao::findList'
        ]);

        $query = "SELECT `u`.*
    		FROM `" . $bolUser->getTableName() . "` as `u`
    		{$queryParts["join"]}

            WHERE {$queryParts["where"]} AND `u`.`username` LIKE '%" . $userName . "%'
    		LIMIT :first, :count ";

        return OW::getDbo()->queryForObject($query, $bolUser->getDtoClassName(), ['first' => 0, 'count' => 1]);
    }

    /**
     * @param $userName
     *
     * @return null | BOL_User
     */
    public function findUserByName( $userName )
    {
        $bolUser = BOL_UserDao::getInstance();

        $queryParts = $bolUser->getUserQueryFilter('u', 'id', [
            'method' => 'BOL_UserDao::findList'
        ]);

        $query = "SELECT `u`.*
    		FROM `" . $bolUser->getTableName() . "` as `u`
    		{$queryParts["join"]}
    		
            INNER JOIN `" . BOL_QuestionDataDao::getInstance()->getTableName() . "` `qd` ON 
            ( `u`.`id` = `qd`.`userId` AND `qd`.`questionName` = 'realname' AND  LCASE(`qd`.`textValue`) LIKE '%" . OW::getDbo()->escapeString($userName) . "%' ) 
                                
            WHERE {$queryParts["where"]}
    		LIMIT :first, :count ";

        return OW::getDbo()->queryForObject($query, $bolUser->getDtoClassName(), ['first' => 0, 'count' => 1]);
    }

    public function countMessageList( $params = [] )
    {
        $end = time();
        $start = time() - self::INTERVAL;

        $join = '';
        $where =  " `mailbox_message`.`isSystem` = 0 AND `mailbox_message`.`timeStamp` BETWEEN '" . $start . "' AND '" . $end . "'";

        $type = !empty($params['type']) ? $params['type'] : null;

        switch ( $type )
        {
            case self::CONFIG_TYPE_SEARCH_BY_USER_NAME:

                $userId = intval($params['userId']);
                $params['userId'] = $userId;

                $where .= ' AND ( `senderId` = :userId OR `recipientId` = :userId ) ';

                break;

            case self::CONFIG_TYPE_SEARCH_FROM_TO:

                $senderId = intval($params['senderId']);
                $recipientId = intval($params['recipientId']);
                $params['senderId'] = $senderId;
                $params['recipientId'] = $recipientId;

                $where .= ' AND ( ( `senderId` = :senderId AND `recipientId` = :recipientId ) OR ( `senderId` = :recipientId AND `recipientId` = :senderId ) ) ';

                break;

            case self::CONFIG_TYPE_SEARCH_BAD_WORD:

                $where .= " AND `text` LIKE '%" . OW::getDbo()->escapeString($params['badWord']) . "%' ";

                break;

            default:


        }

        $sql = "SELECT COUNT(*) FROM `" . MAILBOX_BOL_MessageDao::getInstance()->getTableName() . "` as `mailbox_message` 
                " . $join . "
                WHERE " . $where . " ";

        return OW::getDbo()->queryForColumn($sql, $params);
    }

    public function findMessageList( $first, $count, $params = [] )
    {
        $end = time();
        $start = time() - self::INTERVAL;

        $join = '';
        $where =  " `mailbox_message`.`timeStamp` BETWEEN '" . $start . "' AND '" . $end . "'";
        $limit = ' LIMIT :first, :count ';

        $option = ['first' => $first, 'count' => $count];

        $type = !empty($params['type']) ? $params['type'] : null;

        switch ( $type )
        {
            case self::CONFIG_TYPE_SEARCH_BY_USER_NAME:

                $userId = intval($params['userId']);
                $option['userId'] = $userId;

                $where .= ' AND ( `senderId` = :userId OR `recipientId` = :userId ) ';

                break;

            case self::CONFIG_TYPE_SEARCH_FROM_TO:

                $senderId = intval($params['senderId']);
                $recipientId = intval($params['recipientId']);
                $option['senderId'] = $senderId;
                $option['recipientId'] = $recipientId;

                $where .= ' AND ( ( `senderId` = :senderId AND `recipientId` = :recipientId ) OR ( `senderId` = :recipientId AND `recipientId` = :senderId ) ) ';

                break;

            case self::CONFIG_TYPE_SEARCH_BAD_WORD:

                $where .= " AND `text` LIKE '%" . OW::getDbo()->escapeString($params['badWord']) . "%' ";

                break;

            default:

        }

        $sql = "SELECT `mailbox_message`.* FROM `" . MAILBOX_BOL_MessageDao::getInstance()->getTableName() . "` as `mailbox_message` 
                " . $join . "
                WHERE " . $where . " ORDER BY `mailbox_message`.`timeStamp` DESC " . $limit;


        return OW::getDbo()->queryForList($sql, $option);
    }
}
