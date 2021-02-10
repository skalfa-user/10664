<?php

/**
 * User list
 */
class CUSTOMINDEX_CMP_AvatarUserList extends BASE_CMP_AvatarUserList
{
    public function getAvatarInfo( $idList )
    {
        // get avatars data
        $avatarsData = BOL_AvatarService::getInstance()->getDataForUserAvatars($idList);

        // get users data
        $usersData = BOL_QuestionService::getInstance()->getQuestionData($idList, ['birthdate', 'googlemap_location']);

        // collect data
        foreach ($avatarsData as $id => $item)
        {
            $avatarsData[$id]['age'] = '';
            if (!empty($usersData[$id]['birthdate']))
            {
                $date = UTIL_DateTime::parseDate($usersData[$id]['birthdate'], UTIL_DateTime::MYSQL_DATETIME_DATE_FORMAT);
                $avatarsData[$id]['age'] = UTIL_DateTime::getAge($date['year'], $date['month'], $date['day']);
            }

            $avatarsData[$id]['location'] = !empty($usersData[$id]['googlemap_location']['address'])
                ? $usersData[$id]['googlemap_location']['address']
                :'';
        }

        return $avatarsData;
    }
}
