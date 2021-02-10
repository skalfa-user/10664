<?php

/**
 * Copyright (c) 2016, Skalfa LLC
 * All rights reserved.
 * 
 * ATTENTION: This commercial software is intended for exclusive use with SkaDate Dating Software (http://www.skadate.com)
 * and is licensed under SkaDate Exclusive License by Skalfa LLC.
 * 
 * Full text of this license can be found at http://www.skadate.com/sel.pdf
 */

class OCSGUESTS_CTRL_List extends OW_ActionController
{
    public function index( array $params )
    {
        if ( !$userId = OW::getUser()->getId() )
        {
            throw new AuthenticateException();
        }

        $page = (!empty($_GET['page']) && intval($_GET['page']) > 0 ) ? $_GET['page'] : 1;
        $lang = OW::getLanguage();

        $perPage = (int) OW::getConfig()->getValue('base', OW::getPluginManager()->isPluginActive('skadate') ? 'users_on_page' : 'users_count_on_page');
        $guests = OCSGUESTS_BOL_Service::getInstance()->findGuestsForUser($userId, $page, $perPage);

        $guestList = array();

        if ( $guests )
        {
        	foreach ( $guests as $guest )
        	{
        		$guestList[$guest->guestId] = array('last_visit' => $lang->text('ocsguests', 'visited') . ' ' . '<span class="ow_remark">' . $guest->visitTimestamp . '</span>');
        	}

	        $itemCount = OCSGUESTS_BOL_Service::getInstance()->countGuestsForUser($userId);

            if ( OW::getPluginManager()->isPluginActive('skadate') )
            {
                $cmp = OW::getClassInstance('BASE_CMP_Users', $guestList, array(), $itemCount);
            }
            else
            {
                $guestsUsers = BOL_UserService::getInstance()->findUserListByIdList(array_keys($guestList));
                $cmp = new OCSGUESTS_CMP_Users($guestsUsers, $itemCount, $perPage, true, $guestList);
            }

	        $this->addComponent('guests', $cmp);
        }
        else 
        {
        	$this->assign('guests', null);
        }
        
        $this->setPageHeading($lang->text('ocsguests', 'viewed_profile'));
        $this->setPageTitle($lang->text('ocsguests', 'viewed_profile'));

        OW::getNavigation()->activateMenuItem(OW_Navigation::MAIN, 'base', 'dashboard');
    }
}