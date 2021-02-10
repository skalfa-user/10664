<?php

class ARTICLES_BOL_ArticleDao extends OW_BaseDao
{

    /**
     * Singleton instance.
     *
     * @var ARTICLES_BOL_ArticleDao
     */
    private static $classInstance;

    /**
     * Returns an instance of class (singleton pattern implementation).
     *
     * @return ARTICLES_BOL_ArticleDao
     */
    public static function getInstance()
    {
        if ( self::$classInstance === null )
        {
            self::$classInstance = new self();
        }

        return self::$classInstance;
    }

    /**
     * @see OW_BaseDao::getDtoClassName()
     *
     */
    public function getDtoClassName()
    {
        return 'ARTICLES_BOL_Article';
    }

    /**
     * @see OW_BaseDao::getTableName()
     *
     */
    public function getTableName()
    {
        return OW_DB_PREFIX . 'articles_article';
    }

    public function getArticles()
    {
        $sql = 'SELECT 
                * FROM ' . $this->getTableName()
                . ' ORDER BY id DESC';

        return $this->dbo->queryForObjectList($sql, $this->getDtoClassName());
    }

    public function getFeaturedArticles()
    {
        $sql = 'SELECT 
                * FROM ' . $this->getTableName() . '
                WHERE featured = 1
                ORDER BY id DESC
                LIMIT 5';

        return $this->dbo->queryForObjectList($sql, $this->getDtoClassName());
    }

    public function updateFeaturedById($id, $featured)
    {
        $sql = "UPDATE " . $this->getTableName() . "
                SET featured = '$featured'
                WHERE id = $id";

        $this->dbo->query($sql);
    }

    public function deleteArticleById($id)
    {
        $sql = "DELETE FROM " . $this->getTableName() . "
                WHERE id = $id";

        $this->dbo->query($sql);
    }

    public function editArticleById(ARTICLES_BOL_Article $data)
    {
        $sql = 'UPDATE `' . $this->getTableName() . '`
            SET `title` = :title,
            `subtitle` = :subtitle,
            `description` = :description,
            `image` = :image
            WHERE `id` = :id';

        $this->dbo->query($sql, array('id' => $data->id, 'title' => $data->title, 'subtitle' => $data->subtitle, 'description' => $data->description, 'image' => $data->image));
    }

}