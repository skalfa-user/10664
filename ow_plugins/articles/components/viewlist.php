<?php

class ARTICLES_CMP_Viewlist extends OW_Component
{
    public function __construct()
    {
        parent::__construct();
        $service = ARTICLES_BOL_Service::getInstance();
        $articles = $service->getArticles();

        $this->assign('articles', $articles);
        $this->assign('isAdmin', OW::getUser()->isAdmin());
        $this->assign('createLink', OW::getRouter()->urlForRoute('articles.admin_index'));

        OW::getDocument()->addScript(OW::getPluginManager()->getPlugin('articles')->getStaticJsUrl() . 'articles.js');
        OW::getDocument()->addScriptDeclarationBeforeIncludes(UTIL_JsGenerator::composeJsString(
            ';window.articlesParams = {$params};',
            ['params' => [
                'updateFeaturedUrl' => OW::getRouter()->urlForRoute('articles-update-featured')
            ]]
        ));
    }
}
