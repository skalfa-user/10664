<?php


class ARTICLES_BOL_Service
{

    private static $classInstance;

    /**
     * Returns class instance
     *
     * @return ARTICLES_BOL_Service
     */
    public static function getInstance()
    {
        if ( null === self::$classInstance )
        {
            self::$classInstance = new self();
        }

        return self::$classInstance;
    }

    /**
     *
     * @var ARTICLES_BOL_ArticleDao
     */
    private $articleDao;

    /**
     * Class constructor
     *
     */
    protected function __construct()
    {
        $this->articleDao = ARTICLES_BOL_ArticleDao::getInstance();
    }

    public function addArticle($article) {

        $this->articleDao->save($article);
    }

    public function getArticles() {
        return $this->articleDao->getArticles();
    }

    public function getFeaturedArticles() {
        return $this->articleDao->getFeaturedArticles();
    }

    public function deleteArticleById($id)
    {
        $this->articleDao->deleteArticleById($id);
    }

    public function editArticleById($data)
    {
        $this->articleDao->editArticleById($data);
    }

}