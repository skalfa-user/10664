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
class HINT_CMP_GroupHintPreview extends HINT_CMP_HintPreviewBase
{
    public function getCoverPreview()
    {
        $staticUrl = OW::getPluginManager()->getPlugin("hint")->getStaticUrl() . "preview/";

        return array(
            'url' => $staticUrl . "group_cover.jpg",
            'height' => 122,
            'imageCss' => "width: 100%; height: auto; top: -7px"
        );
    }
    
    protected function getGroupInfo()
    {
        $group = array();

        $group["title"] = "Snowboarding";
        $group["url"] = "javascript://";
        
        $staticUrl = OW::getPluginManager()->getPlugin("hint")->getStaticUrl() . "preview/";
        $group['avatar'] =  $staticUrl . 'group_avatar.png';

        return $group;
    }

    public function onBeforeRender()
    {
        parent::onBeforeRender();
        
        $this->assign('group', $this->getGroupInfo());
    }
}