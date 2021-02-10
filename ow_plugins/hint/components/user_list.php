<?php

/**
 * Copyright (c) 2012, Sergey Kambalin
 * All rights reserved.

 * ATTENTION: This commercial software is intended for use with Oxwall Free Community Software http://www.oxwall.org/
 * and is licensed under Oxwall Store Commercial License.
 * Full text of this license can be found at http://www.oxwall.org/store/oscl
 */

/**
 *
 * @author Sergey Kambalin <greyexpert@gmail.com>
 * @package hint.components
 */
class HINT_CMP_UserList extends OW_Component
{
    public function __construct( $avatarData, $idList = array(), $viewAllTitle = null, $displayCount = 6 ) 
    {
        parent::__construct();
        
        $uniqId = uniqid("hint-user-list-");
        $this->assign("uniqId", $uniqId);
        
        $viewAll = count($idList) > $displayCount;
        $this->assign("viewAll", $viewAll);
        
        $tplData = array_slice($avatarData, 0, $displayCount - ( $viewAll ? 1 : 0 ) );
        
        $this->assign("avatars", $tplData);
        
        $js = UTIL_JsGenerator::newInstance();
        $js->jQueryEvent("#" . $uniqId . " .hint-view-all", "click", "OW.showUsers(e.data.idList, e.data.title); return false;", array("e"), array(
            "idList" => $idList,
            "title" => $viewAllTitle
        ));
        
        OW::getDocument()->addOnloadScript($js);
    }
}