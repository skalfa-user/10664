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
 * @package hint.controllers
 */
class HINT_CTRL_Hint extends OW_ActionController
{
    public function rsp()
    {
        if ( !OW::getRequest()->isAjax() )
        {
            throw new Redirect404Exception();
        }

        if ( empty($_GET['url']) )
        {
            exit;
        }

        $url = trim($_GET['url']);

        $parser = HINT_CLASS_ParseManager::getInstance()->getParser($url);

        if ( $parser === null )
        {
            exit;
        }

        $params = $parser->parse($url);

        $hint = $parser->renderHint($params);
        $topCorner = empty($hint['topCorner']) ? null : $hint['topCorner'];
        $bottomCorner = empty($hint['bottomCorner']) ? null : $hint['bottomCorner'];
        $rightCorner = empty($hint['rightCorner']) ? null : $hint['rightCorner'];

        $out = array(
            'markup' => $this->getMarkup($hint['body'], $topCorner, $bottomCorner, $rightCorner)
        );

        echo json_encode($out);
        exit;
    }

    private function getMarkup( $html, $topCorner, $bottomCorner, $rightCorner )
    {
        /* @var $document OW_AjaxDocument */
        $document = OW::getDocument();

        $responce = array();

        $responce['content'] = array(
            'body' => $html,
            'topCorner' => $topCorner,
            'bottomCorner' => $bottomCorner,
            'rightCorner' => $rightCorner
        );

        $beforeIncludesScript = $document->getScriptBeforeIncludes();
        if ( !empty($beforeIncludesScript) )
        {
            $responce['beforeIncludes'] = $beforeIncludesScript;
        }
        
        foreach ( $document->getScripts() as $script )
        {
            $responce['scriptFiles'][] = $script;
        }

        $onloadScript = $document->getOnloadScript();
        if ( !empty($onloadScript) )
        {
            $responce['onloadScript'] = $onloadScript;
        }

        $styleDeclarations = $document->getStyleDeclarations();
        if ( !empty($styleDeclarations) )
        {
            $responce['styleDeclarations'] = $styleDeclarations;
        }
        
        $styleSheets = $document->getStyleSheets();
        if ( !empty($styleSheets) )
        {
            $responce['styleSheets'] = $styleSheets;
        }

        return $responce;
    }

    public function test()
    {
        $hint = new HINT_CMP_UserHint(1);
        $this->addComponent('hint', $hint);
    }

    public function query()
    {
        if ( !OW::getRequest()->isAjax() )
        {
            throw new Redirect404Exception();
        }

        $command = $_GET["command"];
        $params = empty($_GET["params"]) ? array() : json_decode($_GET["params"], true);

        $event = new OW_Event(HINT_BOL_Service::EVENT_QUERY, array(
            "command" => $command,
            "params" => $params
        ));
        OW::getEventManager()->trigger($event);

        $data = $event->getData();

        echo json_encode(empty($data) ? array() : $data);
        exit;
    }
}