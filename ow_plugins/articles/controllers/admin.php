<?php

class ARTICLES_CTRL_Admin extends ADMIN_CTRL_Abstract
{
    private $serviceInstance;

    public function __construct()
    {
        parent::__construct();

        $this->serviceInstance = ARTICLES_BOL_Service::getInstance();
    }

    public function init()
    {
        OW::getDocument()->addScript(OW::getPluginManager()->getPlugin('articles')->getStaticJsUrl() . 'articles.js');
        OW::getDocument()->addStyleSheet(OW::getPluginManager()->getPlugin('articles')->getStaticCssUrl() . 'articles.css');

        OW::getDocument()->addScriptDeclarationBeforeIncludes(UTIL_JsGenerator::composeJsString(
            ';window.articlesParams = {$params};',
            ['params' => [
                'updateFeaturedUrl' => OW::getRouter()->urlForRoute('articles-update-featured'),
                'deleteArticleUrl' => OW::getRouter()->urlForRoute('articles.admin-delete')
            ]]
        ));
    }

    public function index()
    {
        OW::getDocument()->setHeading(OW::getLanguage()->text("articles", "create_article"));

        $form = new ARTICLES_CLASS_AddForm();

        if ($form->addProcess()) {
            OW::getFeedback()->info(OW::getLanguage()->text('articles', 'success_add'));
            $this->redirect();
        }

        $this->addForm($form);

        $articles = $this->serviceInstance->getArticles();
        $this->assign('articles', $articles);

    }

    public function view($params)
    {
        $id = $params['id'];
        $article = ARTICLES_BOL_ArticleDao::getInstance()->findById($id);

        if (null === $article )
        {
            throw new Redirect404Exception();
        }

        $this->assign('article',$article);
    }

    public function delete() {
        $id = $_POST['id'];

        $existArticle = ARTICLES_BOL_ArticleDao::getInstance()->findById($id);
        $success = [];

        if ($existArticle) {
            ARTICLES_BOL_Service::getInstance()->deleteArticleById($id);
            unlink($existArticle->getImageNamePath());

            OW::getFeedback()->info(OW::getLanguage()->text('articles', 'success_delete'));
            $success = ['success' => true];
        }

        exit(json_encode($success));
    }

    public function edit($params) {
        OW::getDocument()->setHeading(OW::getLanguage()->text("articles", "edit_article"));

        $id = $params['id'];
        $article = ARTICLES_BOL_ArticleDao::getInstance()->findById($id);

        if (null === $article) {
            throw new Redirect404Exception();
        }

        $form = new ARTICLES_CLASS_EditForm($article);

        if ($form->editProcess()) {
            OW::getFeedback()->info(OW::getLanguage()->text('articles', 'success_edit'));
            $this->redirect(OW::getRouter()->urlForRoute('articles.admin_index'));
        }

        $this->addForm($form);
        $this->assign('article',$article);
    }

}
