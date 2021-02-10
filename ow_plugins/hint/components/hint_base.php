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
class HINT_CMP_HintBase extends OW_Component
{
    const COVER_WIDTH = 350;
    const COVER_HEIGHT = 350;
    const CORNER_OFFSET = 7;
    
    protected $uniqId;
    protected $hasButtons = false;
    protected $entityType, $entityId;


    public function __construct( $entityType, $entityId ) 
    {
        parent::__construct();
        
        $this->uniqId = uniqid("hint-");
        
        $this->entityType = $entityType;
        $this->entityId = $entityId;
        
        $this->cover = $this->getCover();
    }
    
    public function getCover()
    {
        return null;
    }
    
    protected function prepareCover( $coverUrl, $settings )
    {
        $canvasHeight = $settings['canvas']['height'];
        $canvasWidth = $settings['canvas']['width'];
        $imageHeight = $settings['dimensions']['height'];
        $imageWidth = $settings['dimensions']['width'];

        $itemCanvasHeight = $canvasHeight * self::COVER_WIDTH / $canvasWidth;

        $tmp = ( $canvasWidth * $imageHeight ) / $imageWidth;
        $css = $settings['css'];
        
        if ( $css["width"] == "100%" )
        {
            $css["width"] = (self::COVER_WIDTH + self::CORNER_OFFSET) . "px";
        }
        
        $topOffset = 0;
        
        if ( $tmp >= $canvasHeight )
        {
            $itemHeight = $this->scale($imageWidth, $imageHeight, self::COVER_WIDTH);
            $coverHeight = $this->scale($settings['dimensions']['width'], $settings['dimensions']['height'], $canvasWidth);
            $k = $coverHeight / $itemHeight;

            $topOffset = ($settings['position']['top'] / $k );
        }
        else
        {
            $itemWidth = $this->scale($imageHeight, $imageWidth, $itemCanvasHeight);
            $coverWidth = $this->scale($imageHeight, $imageWidth, $canvasHeight);

            $k = $coverWidth / $itemWidth;
            
            $css['left'] = ($settings['position']['left'] / $k) . 'px';
        }
        
        if ( $css["height"] == "100%" )
        {
            $css["height"] = $itemCanvasHeight . "px";
        }
        
        if ( abs($topOffset) <= self::CORNER_OFFSET )
        {
            $itemCanvasHeight = $itemCanvasHeight - ( self::CORNER_OFFSET - $topOffset );
            $topOffset = -self::CORNER_OFFSET;
        }
        
        if ( $topOffset )
        {
            $css['top'] = $topOffset . 'px';
        }

        $cssStr = '';
        foreach ( $css as $k => $v )
        {
            $cssStr .= $k . ': ' . $v  . '; ';
        }

        return array(
            'url' => $coverUrl,
            'height' => $itemCanvasHeight,
            'imageCss' => $cssStr
        );
    }


    protected function scale( $x, $y, $toX )
    {
        return $y * $toX / $x;
    }
    
    public function renderTopCover()
    {
        if ( !$this->cover )
        {
            return '<div class="uhint-top-corner"></div>';
        }

        return '<div class="uhint-top-corner-cover uhint-corner-cover" style="height: ' . $this->cover['height'] . 'px;"><img class="uhint-corner-cover-img" src="' . $this->cover['url'] . '" style="' . $this->cover['imageCss'] . '" /></div>';
    }
    
    public function renderRightCover()
    {
        if ( !$this->cover )
        {
            return '<div class="uhint-right-corner"></div>';
        }

        return '<div class="uhint-right-corner-cover uhint-corner-cover" style="height: ' . $this->cover['height'] . 'px;"><img class="uhint-corner-cover-img" src="' . $this->cover['url'] . '" style="' . $this->cover['imageCss'] . '" /></div>';
    }
    
    public function renderBottomCover()
    {
        if ( $this->hasButtons )
        {
            return '<div class="uhint-bottom-corner"></div>';
        }
        
        return '<div class="uhint-bottom-corner ow_bg_color"></div>';
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

        $btns = HINT_BOL_Service::getInstance()->getButtonList($this->entityType, $this->entityId);
        $out = array();
        foreach ( $btns as $btn )
        {
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

        $this->hasButtons = count($out) > 0;
        
        return $out;
    }
    
    public function getInfo()
    {
        return array(
            HINT_BOL_Service::INFO_LINE0 => HINT_BOL_Service::getInstance()->getInfoLine($this->entityType, $this->entityId, HINT_BOL_Service::INFO_LINE0),
            HINT_BOL_Service::INFO_LINE1 => HINT_BOL_Service::getInstance()->getInfoLine($this->entityType, $this->entityId, HINT_BOL_Service::INFO_LINE1),
            HINT_BOL_Service::INFO_LINE2 => HINT_BOL_Service::getInstance()->getInfoLine($this->entityType, $this->entityId, HINT_BOL_Service::INFO_LINE2)
        );
    }
    
    public function getOptions( $info, $buttons )
    {
        $options = array(
            "hasLines" => false,
            "has0line" => false
        );
        
        foreach ( $info as $lineKey => $line )
        {
            if ( $lineKey == HINT_BOL_Service::INFO_LINE0 && !empty($line) )
            {
                $options["has0line"] = true;
                
                continue;
            }
            
            if ( !empty($line) )
            {
                $options["hasLines"] = true;
            }
        }
        
        return $options;
    }
    
    public function onBeforeRender()
    {
        parent::onBeforeRender();

        $event = new OW_Event(HINT_BOL_Service::EVENT_HINT_RENDER, array(
            "entityType" => $this->entityType,
            "entityId" => $this->entityId
        ));
        OW::getEventManager()->trigger($event);
        
        $info = $this->getInfo();
        $buttons = $this->getButtonList();
        
        $this->assign('cover', $this->cover);
        $this->assign("buttons", $buttons);
        $this->assign("info", $info);

        $this->assign("options", $this->getOptions($info, $buttons));
        
        $this->assign('uniqId', $this->uniqId);
    }
}
