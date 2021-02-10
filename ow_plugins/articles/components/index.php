<?php

class ARTICLES_CMP_Index extends OW_Component
{
    public function __construct()
    {
        parent::__construct();

        $service = ARTICLES_BOL_Service::getInstance();
        $articles = $service->getFeaturedArticles();

        $this->assign('articles', $articles);
        $this->assign('isAdmin', OW::getUser()->isAdmin());
        $this->assign('createLink', OW::getRouter()->urlForRoute('articles.admin_index'));
        $this->assign('viewAll', OW::getRouter()->urlForRoute('articles-viewlist'));
    }
}
