<?php

final class ADVANCEDGROUPS_BOL_CategoriesService
{
    /**
     * @var array
     */
    private $configs = array();
    /**
     * @var ADVANCEDGROUPS_BOL_CategoriesDao
     */
    private $categoryDao;

    private static $classInstance;

    /**
     * Returns an instance of class (singleton pattern implementation).
     *
     * @return ADVANCEDGROUPS_BOL_CategoriesService
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
    private function __construct()
    {
        $this->categoryDao = ADVANCEDGROUPS_BOL_CategoriesDao::getInstance();
    }

    
    public function saveCategories( ADVANCEDGROUPS_BOL_Categories $category )
    {
        $this->categoryDao->save($category);
    }
    
	public function getCategoriesList() {
        $categories = $this->categoryDao->findAllOrdered();
        $lang = OW::getLanguage();
        
        $result = array();
        foreach ( $categories as $category )
        {	
        	$result[$category->id]['id'] = $category->id;
        	$result[$category->id]['title'] = $lang->text('advancedgroups', 'category_title_'.$category->id);
        	$result[$category->id]['description'] = $lang->text('advancedgroups', 'category_description_'.$category->id);
        }
        
        return $result;
    }

    public function addCategory($title, $description) {
    	$description = $description === "" ? "..." : $description;
        $category = new ADVANCEDGROUPS_BOL_Categories();
        $category->order = (int) $this->categoryDao->getMaxOrder() + 1;
        $this->categoryDao->save($category);
        
        if ( $category->id )
        {
            $langService = BOL_LanguageService::getInstance();
            foreach ($langService->getLanguages() as $currentLang) {
	            $langService->addOrUpdateValue($currentLang->getId(), 'advancedgroups', 'category_title_' . $category->id, $title);
	            $langService->addOrUpdateValue($currentLang->getId(), 'advancedgroups', 'category_description_' . $category->id, $description);
	            $langService->generateCache($currentLang->getId());
            }    
            return $category->id;
        }
        
        return false;
    }

    public function deleteCategory($id) {
      // @TODO: should delete group
//    	$groupService  = ADVANCEDGROUPS_BOL_PhotogroupService::getInstance();
//    	foreach ($groupService->findPhotogroupsByCategoryId($id) as $group) {
//    		$groupService->deletePhotogroup($group->id);
//    	}
        $this->categoryDao->deleteById($id);
    }

    public function isDuplicate($category) {
        return $this->categoryDao->isDuplicate($category);
    }

    public function getCategoryId($category) {
        return $this->categoryDao->getCategoryId($category);
    }

    
}