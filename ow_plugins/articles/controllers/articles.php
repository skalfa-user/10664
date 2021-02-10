<?php

class ARTICLES_CTRL_Articles extends OW_ActionController
{
    /**
     *
     * @var ARTICLES_BOL_Service
     */
    private $service;

    public function __construct()
    {
        $this->service = ARTICLES_BOL_Service::getInstance();
    }

    public function init()
    {
        $this->assign('isAdmin', OW::getUser()->isAdmin());

        OW::getDocument()->addScript(OW::getPluginManager()->getPlugin('articles')->getStaticJsUrl() . 'articles.js');
        OW::getDocument()->addStyleSheet(OW::getPluginManager()->getPlugin('articles')->getStaticCssUrl() . 'articles.css');

        OW::getDocument()->addScriptDeclarationBeforeIncludes(UTIL_JsGenerator::composeJsString(
            ';window.articlesParams = {$params};',
            ['params' => [
                'updateFeaturedUrl' => OW::getRouter()->urlForRoute('articles-update-featured')
            ]]
        ));
    }

    public function index()
    {

    }

    public function viewlist()
    {

    }

    public function view($params) {
        $id = $params['id'];
        $article = ARTICLES_BOL_ArticleDao::getInstance()->findById($id);

        if ( null === $article )
        {
            throw new Redirect404Exception();
        }

        $this->assign('article',$article);
    }

    public function updateFeatured() {
        if (!OW::getUser()->isAuthorized('admin')) {
            throw new AuthorizationException();
        }

        $id = $_POST['id'];
        $featured = $_POST['featured'];
        ARTICLES_BOL_ArticleDao::getInstance()->updateFeaturedById($id, $featured);

        exit(json_encode(['success' => true]));

    }

}
