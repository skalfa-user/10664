<?php

/**
 * This software is intended for use with Oxwall Free Community Software http://www.oxwall.org/ and is
 * licensed under The BSD license.

 * ---
 * Copyright (c) 2011, Oxwall Foundation
 * All rights reserved.

 * Redistribution and use in source and binary forms, with or without modification, are permitted provided that the
 * following conditions are met:
 *
 *  - Redistributions of source code must retain the above copyright notice, this list of conditions and
 *  the following disclaimer.
 *
 *  - Redistributions in binary form must reproduce the above copyright notice, this list of conditions and
 *  the following disclaimer in the documentation and/or other materials provided with the distribution.
 *
 *  - Neither the name of the Oxwall Foundation nor the names of its contributors may be used to endorse or promote products
 *  derived from this software without specific prior written permission.

 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES,
 * INCLUDING, BUT NOT LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR
 * PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT HOLDER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT,
 * INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO,
 * PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED
 * AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE)
 * ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 */

/**
 * @author Zarif Safiullin <zaph.saph@gmail.com>
 * @package ow_plugins.ask.controllers
 * @since 1.0
 */
class ADVANCEDGROUPS_CTRL_Admin extends ADMIN_CTRL_Abstract
{

    public function __construct()
    {
        parent::__construct();
        
    }
    
    public function init() {
    	
    	OW::getDocument()->addScript(OW::getPluginManager()->getPlugin('base')->getStaticJsUrl() . 'jquery-ui.min.js');	
    	
    	$contentMenu = $this->getContentMenu();
		$this->addComponent('menu', $contentMenu );
    }
    
	public function config()
    {
        
    }
	
	public function categories() {
        $adminForm = new Form('categoriesForm');

        $language = OW::getLanguage();

        $element = new TextField('categoryName');
        $element->setRequired();
        $element->setInvitation("Category");
        $element->setHasInvitation(true);
        $adminForm->addElement($element);
        
        $element =  new Textarea('categoryDesc');
        $element->setInvitation("Write a short description...");
        $element->setHasInvitation(true);
        $adminForm->addElement($element);
        
        $element = new Submit('add');
        $element->setValue("Add new category");
        $adminForm->addElement($element);

        if (OW::getRequest()->isPost()) {
            if ($adminForm->isValid($_POST)) {
                $values = $adminForm->getValues();
                $name = $values['categoryName'];
                $desc = $values['categoryDesc'];
                if (ADVANCEDGROUPS_BOL_CategoriesService::getInstance()->addCategory($name, $desc))
                    OW::getFeedback()->info("Added successfully");
                else
                    OW::getFeedback()->error("Added unsuccessfully");

                $this->redirect();
            }
        }

        $this->addForm($adminForm);

        $allCategories = array();
        $deleteUrls = array();
        $editrls = array();

        $categories = ADVANCEDGROUPS_BOL_CategoriesService::getInstance()->getCategoriesList();

        foreach ($categories as $category) {
            $allCategories[$category['id']]['id'] = $category['id'];
            $allCategories[$category['id']]['title'] = $category['title'];
            $allCategories[$category['id']]['description'] = $category['description'];
            $deleteUrls[$category['id']] = OW::getRouter()->urlFor(__CLASS__, 'delete', array('id' => $category['id']));
        }

        $this->assign('allCategories', $allCategories);
        $this->assign('deleteUrls', $deleteUrls);
        $this->assign('editrls', $editrls);
        
        $this->setPageHeading("Categories Management");
        
        $orderUrl = OW::getRouter()->urlFor(__CLASS__, 'catorder');
		OW::getDocument()->addScriptDeclaration('	    
		    var fixHelperModified = function(e, tr) {
			    var $originals = tr.children();
			    var $helper = tr.clone();
			    $helper.children().each(function(index) {
			        $(this).width($originals.eq(index).width())
			    });
			    return $helper;
			},
			    updateIndex = function(e, ui) {
				    var items = $("#sort tr");
				
				    var linkIDs = [items.size()];
				    var index = 0;
				
				    items.each(
				        function(intIndex) {
				        	if($(this).data("id")){
				        		linkIDs[index] = $(this).data("id");
				            	index++;
							}	
				        });
				    $.get("'.$orderUrl.'?ids=" + linkIDs.join(","));
			    };
			
			$("#sort tbody").sortable({
			    helper: fixHelperModified,
			    stop: updateIndex
			}).disableSelection();
    	');
    }
    
	public function delete($params) {
        if (isset($params['id'])) {
            ADVANCEDGROUPS_BOL_CategoriesService::getInstance()->deleteCategory((int) $params['id']);
        }

        $this->redirect(OW::getRouter()->urlForRoute('groups-admin-categories'));
    }
    
    public function catorder(){
    	$ids = explode(',', $_GET['ids']);
    	$index = 1;
    	foreach ($ids as $id) {
    		ADVANCEDGROUPS_BOL_CategoriesDao::getInstance()->order($id, $index);
    		$index ++;
    	} 
    	exit();
    }


  public function uninstall()
  {
    if ( isset($_POST['action']) && $_POST['action'] == 'delete_content' )
    {
      ADVANCEDGROUPS_CLASS_Api::getInstance()->databaseUninstall();

      BOL_PluginService::getInstance()->uninstall('advancedgroups');

      OW::getFeedback()->info(OW::getLanguage()->text('admin', 'manage_plugins_uninstall_success_message', array( 'plugin' => 'Advanced Groups' )));

      $this->redirect(OW::getRouter()->urlFor('ADMIN_CTRL_Plugins', 'index'));
    }

    $this->setPageHeading('Uninstall Advanced Groups plugin');
    $this->setPageHeadingIconClass('ow_ic_delete');


  }
	private function getContentMenu()
	{
		$menuItems = array();

		$listNames['admin-advancedgroups-categories'] = array('label'=>'Category','url' => OW::getRouter()->urlForRoute('groups-admin-categories'));
		//$listNames['admin-advancedgroups-admin-config'] = array('label'=>'Config', 'url' => OW::getRouter()->urlForRoute('advancedgroups.admin-config'));
		
		foreach ( $listNames as $listKey => $listArr )
		{
			$menuItem = new BASE_MenuItem();
			$menuItem->setKey($listKey);
			$menuItem->setUrl($listArr['url']);

			$menuItem->setLabel($listArr['label']);
			$menuItems[] = $menuItem;
		}

		return new BASE_CMP_ContentMenu($menuItems);
	}

} 