<?php
class ADVANCEDGROUPS_BOL_Categories extends OW_Entity
{
    public $order = 0;
	
	public function getHref($isFullUrl = true){
		$root = $isFullUrl ? OW_URL_HOME . OW::getRouter()->uriForRoute('advancedgroups.main_menu_route') : "";
    	return $root . "?cat=". $this->id;
    }
    
    public function getTitle() {
    	return OW::getLanguage()->text('advancedgroups', 'category_title_'.$this->id);
    }
    
	public function getDescription() {
    	return OW::getLanguage()->text('advancedgroups', 'category_description_'.$this->id);
    }

}

