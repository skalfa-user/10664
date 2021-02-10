<?php

class CVIDEOUPLOAD_CMP_VideoContentPresenter extends BASE_ContentPresenter
{
    public function __construct( $content, $displayFormat )
    {
        parent::__construct( $content, $displayFormat );

        $this->setTemplate(OW::getPluginManager()->getPlugin(CVIDEOUPLOAD_BOL_Service::PLUGIN_KEY)
                ->getCmpViewDir() . 'video_content_presenter.html');

        OW::getDocument()->addStyleSheet(OW::getPluginManager()->getPlugin(CVIDEOUPLOAD_BOL_Service::PLUGIN_KEY)->getStaticCssUrl() . 'video_content_presenter.css');
    }

    protected function prepare()
    {
        parent::prepare();

        if ( isset($this->content['id']) &&
            intval($this->content['id']) > 0 &&
            !empty($this->content['fileName']) )
        {
            $js = UTIL_JsGenerator::newInstance();

            $videoPlayer = new CVIDEOUPLOAD_CMP_ViewVideo($this->content['fileName']);
            $render = $videoPlayer->render();

            $js->addScript('$("[data-action=play]", "#" + {$uniqId}).click(function(e) { '
                . 'e.preventDefault(); e.stopPropagation();'
                . '$(".ow_newsfeed_oembed_atch", "#" + {$uniqId}).addClass("video_playing"); '
                . '$(".ow_newsfeed_item_picture", "#" + {$uniqId}).html({$render});'
                . 'return false; });', array(
                "uniqId" => $this->uniqId,
                "render" => $render
            ));

            OW::getDocument()->addOnloadScript($js);
        }
    }
}