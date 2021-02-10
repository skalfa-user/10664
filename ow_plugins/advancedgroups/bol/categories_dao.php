<?php

class ADVANCEDGROUPS_BOL_CategoriesDao extends OW_BaseDao
{
    const CACHE_LIFE_TIME = 86400;

    /**
     * Singleton instance.
     *
     * @var ADVANCEDGROUPS_BOL_CategoriesDao
     */
    private static $classInstance;

    /**
     * Returns an instance of class (singleton pattern implementation).
     *
     * @return ADVANCEDGROUPS_BOL_CategoriesDao
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
     * Constructor.
     */
    protected function __construct()
    {
        parent::__construct();
    }

    /**
     * @see OW_BaseDao::getDtoClassName()
     *
     */
    public function getDtoClassName()
    {
        return 'ADVANCEDGROUPS_BOL_Categories';
    }

    /**
     * @see OW_BaseDao::getTableName()
     *
     */
    public function getTableName()
    {
        return OW_DB_PREFIX . 'advancedgroups_categories';
    }

    public function getAllCategories() {
    	
    }
    
    public function getCategoryId($category) {
        $example = new OW_Example();
        $example->andFieldEqual('name', $category);
        $catObject = $this->findObjectByExample($example);

        if (count($catObject) > 0)
            return $catObject->id;
        else
            return false;
    }

    public function isDuplicate($category) {
        $example = new OW_Example();
        $example->andFieldEqual('name', $category);

        if (count($this->findObjectByExample($example)) > 0)
            return true;
        else
            return false;
    }   
    
	public function getMaxOrder()
    {
        $query = "
			SELECT MAX( `order` )
			FROM `{$this->getTableName()}`";

        return $this->dbo->queryForColumn($query);
    }
    
    public function findAllOrdered(){
    	$example = new OW_Example();
        $example->setOrder('`order` ASC');
        
        return $this->findListByExample($example);
    }
    
    public function order($catId, $index) {
    	$sql = 'UPDATE `'. $this->getTableName() .'` SET `order`='.$index.' WHERE id='. $catId;
    	return $this->dbo->query($sql);
    }
}
