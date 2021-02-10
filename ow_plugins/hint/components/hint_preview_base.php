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
class HINT_CMP_HintPreviewBase extends HINT_CMP_HintBase
{
    protected $params = array();

    public function __construct( $entityType, array $params = array() )
    {
        $this->params = $params;
        
        parent::__construct($entityType, 0);
    }
    
    public function getCoverPreview()
    {
        return null;
    }
    
    public function getCover()
    {
        if ( empty($this->params["features"]["cover"]) )
        {
            return null;
        }
        
        return $this->getCoverPreview();
    }
    
    public function getButtonList()
    {
        $defaults = array(
            "label" => "---",
            "attrs" => array(
                "href" => "javascript://"
            ),
            "html" => null
        );

        $btns = HINT_BOL_Service::getInstance()->getPreviewButtonList($this->entityType);
        $out = array();
        foreach ( $btns as $btn )
        {
            if ( !in_array($btn['key'], $this->params["actions"]) )
            {
                continue;
            }

            $btn = array_merge($defaults, $btn);
            $attrs = array();
            foreach ( array_merge($defaults["attrs"], $btn["attrs"]) as $k => $v )
            {
                $attrs[] = $k . '="' . $v . '"';
            }

            $out[] = array_merge($btn, array(
                "attrs" => implode(" ", $attrs)
            ));
        }

        return $out;
    }
    
    public function getInfo()
    {
        $out = array();
        
        $line0question = empty($this->params["info"][HINT_BOL_Service::INFO_LINE0]["question"]) ? null : $this->params["info"][HINT_BOL_Service::INFO_LINE0]["question"];
        $line0key = empty($this->params["info"][HINT_BOL_Service::INFO_LINE0]["key"]) ? null : $this->params["info"][HINT_BOL_Service::INFO_LINE0]["key"];
        $out[HINT_BOL_Service::INFO_LINE0] = HINT_BOL_Service::getInstance()->getInfoLinePreview($this->entityType, $line0key, $line0question, HINT_BOL_Service::INFO_LINE0);
        
        $line1question = empty($this->params["info"][HINT_BOL_Service::INFO_LINE1]["question"]) ? null : $this->params["info"][HINT_BOL_Service::INFO_LINE1]["question"];
        $line1key = empty($this->params["info"][HINT_BOL_Service::INFO_LINE1]["key"]) ? null : $this->params["info"][HINT_BOL_Service::INFO_LINE1]["key"];
        $out[HINT_BOL_Service::INFO_LINE1] = HINT_BOL_Service::getInstance()->getInfoLinePreview($this->entityType, $line1key, $line1question, HINT_BOL_Service::INFO_LINE1);
        
        $line2question = empty($this->params["info"][HINT_BOL_Service::INFO_LINE2]["question"]) ? null : $this->params["info"][HINT_BOL_Service::INFO_LINE2]["question"];
        $line2key = empty($this->params["info"][HINT_BOL_Service::INFO_LINE2]["key"]) ? null : $this->params["info"][HINT_BOL_Service::INFO_LINE2]["key"];
        $out[HINT_BOL_Service::INFO_LINE2] = HINT_BOL_Service::getInstance()->getInfoLinePreview($this->entityType, $line2key, $line2question, HINT_BOL_Service::INFO_LINE2);
        
        return $out;
    }
    
    public function onBeforeRender()
    {
        $info = $this->getInfo();
        $buttons = $this->getButtonList();
        
        $this->assign('cover', $this->cover);
        $this->assign("buttons", $buttons);
        $this->assign("renderedCover", $this->renderTopCover());
        $this->assign("info", $info);
        $this->assign('uniqId', $this->uniqId);
        
        $this->assign("options", $this->getOptions($info, $buttons));
        
        $rspUrl = OW::getRequest()
                ->buildUrlQueryString(OW::getRouter()->urlFor("HINT_CTRL_Admin", "saveOrder"), array(
                    "entityType" => $this->entityType
                ));
        
        $this->assign("rspUrl", json_encode($rspUrl));
    }
}