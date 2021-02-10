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
 * Groups
 *
 * @author Sergey Kambalin <greyexpert@gmail.com>
 * @package ow_plugins.groups.controllers
 * @since 1.0
 */
class ADVANCEDGROUPS_CTRL_Groups extends OW_ActionController
{
  /**
   *
   * @var ADVANCEDGROUPS_BOL_GroupService
   */
  private $service;

  public function __construct()
  {
    $this->service = ADVANCEDGROUPS_BOL_GroupService::getInstance();

    if (!OW::getRequest()->isAjax()) {
      $mainMenuItem = OW::getDocument()->getMasterPage()->getMenu(OW_Navigation::MAIN)->getElement('main_menu_list', 'groups');
      if ($mainMenuItem !== null) {
        $mainMenuItem->setActive(true);
      }
    }
    OW::getDocument()->addStyleSheet(OW::getPluginManager()->getPlugin('advancedgroups')->getStaticCssUrl(). 'font-awesome.css');
    OW::getDocument()->addStyleSheet(OW::getPluginManager()->getPlugin('advancedgroups')->getStaticCssUrl(). "style.css");
  }

  public function index()
  {
    $this->mostPopularList();
  }


  public function create()
  {

    if (!OW::getUser()->isAuthenticated()) {
      throw new AuthenticateException();
    }

    if (!$this->service->isCurrentUserCanCreate()) {
      $permissionStatus = BOL_AuthorizationService::getInstance()->getActionStatus('groups', 'create');

      throw new AuthorizationException($permissionStatus['msg']);
    }

    $language = OW::getLanguage();

    OW::getDocument()->setHeading($language->text('groups', 'create_heading'));
    OW::getDocument()->setHeadingIconClass('ow_ic_new');
    OW::getDocument()->setTitle($language->text('groups', 'create_page_title'));
    OW::getDocument()->setDescription($language->text('groups', 'create_page_description'));

    $form = new GROUPS_CreateGroupForm();

    if (OW::getRequest()->isPost() && $form->isValid($_POST)) {
      $groupDto = $form->process();

      if (empty($groupDto)) {
        $this->redirect();
      }

      $this->service->addUser($groupDto->id, OW::getUser()->getId());

      OW::getFeedback()->info($language->text('groups', 'create_success_msg'));
      $this->redirect($this->service->getGroupUrl($groupDto));
    }

    $this->addForm($form);
  }


  public function edit($params)
  {
    $groupId = (int)$params['groupId'];

    if (empty($groupId)) {
      throw new Redirect404Exception();
    }

    $groupDto = $this->service->findGroupById($groupId);

    if (!$this->service->isCurrentUserCanEdit($groupDto)) {
      throw new Redirect404Exception();
    }

    if ($groupId === null) {
      throw new Redirect404Exception();
    }

    $form = new GROUPS_EditGroupForm($groupDto);

    if (OW::getRequest()->isPost() && $form->isValid($_POST)) {
      if ($form->process()) {
        OW::getFeedback()->info(OW::getLanguage()->text('groups', 'edit_success_msg'));
      }
      $this->redirect();
    }

    $this->addForm($form);

    $this->assign('imageUrl', empty($groupDto->imageHash) ? false : $this->service->getGroupImageUrl($groupDto));

    $deleteUrl = OW::getRouter()->urlFor('GROUPS_CTRL_Groups', 'delete', array('groupId' => $groupDto->id));
    $viewUrl = $this->service->getGroupUrl($groupDto);
    $lang = OW::getLanguage()->text('groups', 'delete_confirm_msg');

    $js = UTIL_JsGenerator::newInstance();
    $js->newFunction('window.location.href=url', array('url'), 'redirect');
    $js->jQueryEvent('#groups-delete_btn', 'click', UTIL_JsGenerator::composeJsString(
      'if( confirm({$lang}) ) redirect({$url});', array('url' => $deleteUrl, 'lang' => $lang)));
    $js->jQueryEvent('#groups-back_btn', 'click', UTIL_JsGenerator::composeJsString(
      'redirect({$url});', array('url' => $viewUrl)));

    OW::getDocument()->addOnloadScript($js);
  }


  public function mostPopularList()
  {
    $language = OW::getLanguage();

    OW::getDocument()->setHeading($language->text('groups', 'group_list_heading'));
    OW::getDocument()->setHeadingIconClass('ow_ic_files');

    OW::getDocument()->setTitle($language->text('groups', 'popular_list_page_title'));
    OW::getDocument()->setDescription($language->text('groups', 'popular_list_page_description'));

    if (!$this->service->isCurrentUserCanViewList()) {
      $status = BOL_AuthorizationService::getInstance()->getActionStatus('groups', 'view');
      throw new AuthorizationException($status['msg']);
    }

    $page = (!empty($_GET['page']) && intval($_GET['page']) > 0) ? $_GET['page'] : 1;
    $perPage = 20;
    $first = ($page - 1) * $perPage;
    $count = $perPage;
    $optionalParmams =  isset($_GET['cat']) ? array('categoryId' => (int) $_GET['cat']) : array();

    $dtoList = $this->service->findGroupList(ADVANCEDGROUPS_BOL_GroupService::LIST_MOST_POPULAR, $first, $count, $optionalParmams);
    $listCount = $this->service->findGroupListCount(ADVANCEDGROUPS_BOL_GroupService::LIST_MOST_POPULAR, $optionalParmams);

    $paging = new BASE_CMP_Paging($page, ceil($listCount / $perPage), 5);

    $menu = $this->getGroupListMenu();
    $menu->getElement('popular')->setActive(true);
    $this->assign('listType', 'popular');

    $this->displayGroupList($dtoList, $paging, $menu);

    $params = array(
      "sectionKey" => "groups",
      "entityKey" => "mostPopular",
      "title" => "groups+meta_title_most_popular",
      "description" => "groups+meta_desc_most_popular",
      "keywords" => "groups+meta_keywords_most_popular"
    );

    OW::getEventManager()->trigger(new OW_Event("base.provide_page_meta_info", $params));
  }

  public function latestList()
  {
    $language = OW::getLanguage();

    OW::getDocument()->setHeading($language->text('groups', 'group_list_heading'));
    OW::getDocument()->setHeadingIconClass('ow_ic_files');

    OW::getDocument()->setTitle($language->text('groups', 'latest_list_page_title'));
    OW::getDocument()->setDescription($language->text('groups', 'latest_list_page_description'));

    if (!$this->service->isCurrentUserCanViewList()) {
      $status = BOL_AuthorizationService::getInstance()->getActionStatus('groups', 'view');
      throw new AuthorizationException($status['msg']);
    }

    $page = (!empty($_GET['page']) && intval($_GET['page']) > 0) ? $_GET['page'] : 1;
    $perPage = 20;
    $first = ($page - 1) * $perPage;
    $count = $perPage;
    $optionalParmams =  isset($_GET['cat']) ? array('categoryId' => (int) $_GET['cat']) : array();

    $dtoList = $this->service->findGroupList(ADVANCEDGROUPS_BOL_GroupService::LIST_LATEST, $first, $count, $optionalParmams);
    $listCount = $this->service->findGroupListCount(ADVANCEDGROUPS_BOL_GroupService::LIST_LATEST, $optionalParmams);

    $paging = new BASE_CMP_Paging($page, ceil($listCount / $perPage), 5);

    $menu = $this->getGroupListMenu();
    $menu->getElement('latest')->setActive(true);
    $this->assign('listType', 'latest');

    $this->displayGroupList($dtoList, $paging, $menu);

    $params = array(
      "sectionKey" => "groups",
      "entityKey" => "latest",
      "title" => "groups+meta_title_latest",
      "description" => "groups+meta_desc_latest",
      "keywords" => "groups+meta_keywords_latest"
    );

    OW::getEventManager()->trigger(new OW_Event("base.provide_page_meta_info", $params));
  }

  public function taglist(array $params = null) {
    if (!$this->service->isCurrentUserCanViewList()) {
      $status = BOL_AuthorizationService::getInstance()->getActionStatus('groups', 'view');
      throw new AuthorizationException($status['msg']);
    }

    $this->assign("showCreate", true);

    if ( !$this->service->isCurrentUserCanCreate() )
    {
      $authStatus = BOL_AuthorizationService::getInstance()->getActionStatus('groups', 'create');
      if ( $authStatus['status'] == BOL_AuthorizationService::STATUS_PROMOTED )
      {
        $this->assign("authMsg", json_encode($authStatus["msg"]));
      }
      else
      {
        $this->assign("showCreate", false);
      }
    }

    $menu = $this->getGroupListMenu();
    $menu->getElement('tagged')->setActive(true);
    $this->assign('listType', 'tagged');
    $this->addComponent('menu', $menu);

    $listUrl = OW::getRouter()->urlForRoute('groups_tag_list');

    OW::getDocument()->addScript(OW::getPluginManager()->getPlugin('advancedgroups')->getStaticJsUrl() . 'groups_tag_search.js');

    $objParams = array('listUrl' => $listUrl);

    $script = "$(document).ready(function(){
                     var itemSearch = new itemTagSearch(" . json_encode($objParams) . ");
                   });";

    OW::getDocument()->addOnloadScript($script);

    $tag = !empty($params['tag']) ? trim(htmlspecialchars(urldecode($params['tag']))) : '';

    if (strlen($tag)) {
      $this->assign('tag', $tag);

      $page = isset($_GET['page']) && (int) $_GET['page'] ? (int) $_GET['page'] : 1;

      $itemsPerPage = 10;

      $items = $this->service->findTaggedItemsList($tag, $page, $itemsPerPage);
      $records = $this->service->findTaggedItemsCount($tag);

      $pages = (int) ceil($records / $itemsPerPage);
      $paging = new BASE_CMP_Paging($page, $pages, 10);

      $out = array();

      foreach ( $items as $item )
      {
        /* @var $item GROUPS_BOL_Group */

        $userCount = ADVANCEDGROUPS_BOL_GroupService::getInstance()->findUserListCount($item->id);
        $title = strip_tags($item->title);

        $toolbar = array(
          array(
            'label' => OW::getLanguage()->text('groups', 'listing_users_label', array(
              'count' => $userCount
            ))
          )
        );

        $out[] = array(
          'id' => $item->id,
          'url' => OW::getRouter()->urlForRoute('groups-view', array('groupId' => $item->id)),
          'title' => $title,
          'imageTitle' => $title,
          'content' => UTIL_String::truncate(strip_tags($item->description), 300, '...'),
          'time' => UTIL_DateTime::formatDate($item->timeStamp),
          'imageSrc' => ADVANCEDGROUPS_BOL_GroupService::getInstance()->getGroupImageUrl($item, ADVANCEDGROUPS_BOL_GroupService::IMAGE_SIZE_BIG),
          'users' => $userCount,
          'toolbar' => $toolbar,
          'group' => $item
        );
      }

      $this->assign('paging', $paging->render());
      $this->assign('list', $out);
      $this->setPageTitle(OW::getLanguage()->text('advancedgroups', 'meta_description_item_tagged_as', array('tag' => $tag)));
      $this->setPageHeading(OW::getLanguage()->text('advancedgroups', 'meta_description_item_tagged_as', array('tag' => $tag)));
    } else {
      $tags = new BASE_CMP_EntityTagCloud(ADVANCEDGROUPS_BOL_GroupService::ENTITY_TYPE_GROUP);
      $tags->setRouteName('groups_view_tagged_list');
      $this->addComponent('tags', $tags);

      $tagsArr = BOL_TagService::getInstance()->findMostPopularTags(ADVANCEDGROUPS_BOL_GroupService::ENTITY_TYPE_GROUP, 20);

      foreach ($tagsArr as $t) {
        $labels[] = $t['label'];
      }
      $tagStr = $tagsArr ? implode(', ', $labels) : '';
      $this->setPageTitle(OW::getLanguage()->text('advancedgroups', 'meta_title_item_tagged'));
      $this->setPageHeading(OW::getLanguage()->text('advancedgroups', 'meta_description_item_tagged'));
    }

    OW::getDocument()->setHeadingIconClass('ow_ic_tag');
  }

  private function displayGroupList( $list, $paging, $menu = null )
  {
    $templatePath = OW::getPluginManager()->getPlugin('advancedgroups')->getCtrlViewDir() . 'groups_list.html';
    $this->setTemplate($templatePath);

    $out = array();

    foreach ( $list as $item )
    {
      /* @var $item GROUPS_BOL_Group */

      $userCount = ADVANCEDGROUPS_BOL_GroupService::getInstance()->findUserListCount($item->id);
      $title = strip_tags($item->title);

      $toolbar = array(
        array(
          'label' => OW::getLanguage()->text('groups', 'listing_users_label', array(
            'count' => $userCount
          ))
        )
      );

      $out[] = array(
        'id' => $item->id,
        'url' => OW::getRouter()->urlForRoute('groups-view', array('groupId' => $item->id)),
        'title' => $title,
        'imageTitle' => $title,
        'content' => UTIL_String::truncate(strip_tags($item->description), 300, '...'),
        'time' => UTIL_DateTime::formatDate($item->timeStamp),
        'imageSrc' => ADVANCEDGROUPS_BOL_GroupService::getInstance()->getGroupImageUrl($item, ADVANCEDGROUPS_BOL_GroupService::IMAGE_SIZE_BIG),
        'users' => $userCount,
        'toolbar' => $toolbar,
        'group' => $item
      );
    }

    $this->addComponent('paging', $paging);

    if ( !empty($menu) )
    {
      $this->addComponent('menu', $menu);
    }
    else
    {
      $this->assign('menu', '');
    }

    $this->assign("showCreate", true);

    if ( !$this->service->isCurrentUserCanCreate() )
    {
      $authStatus = BOL_AuthorizationService::getInstance()->getActionStatus('groups', 'create');
      if ( $authStatus['status'] == BOL_AuthorizationService::STATUS_PROMOTED )
      {
        $this->assign("authMsg", json_encode($authStatus["msg"]));
      }
      else
      {
        $this->assign("showCreate", false);
      }
    }

    $this->assign('list', $out);

    $isDisplayCategoryMenu =  true;
    if($isDisplayCategoryMenu) {
      $categoryMenu = new BASE_CMP_SortControl();
      foreach (ADVANCEDGROUPS_BOL_CategoriesService::getInstance()->getCategoriesList() as $key => $item) {
        $categoryMenu->addItem(
          $item['id'],
          $item['title'],
          '?cat=' . $item['id'],
          (isset($_GET['cat']) && (int) $_GET['cat'] == $item['id']) ?  true : false
        );
      }
      $this->addComponent('categoryMenu', $categoryMenu);
    }
  }

  private function getGroupListMenu()
  {

    $language = OW::getLanguage();

    $items = array();

    $items[0] = new BASE_MenuItem();
    $items[0]->setLabel($language->text('groups', 'group_list_menu_item_popular'))
      ->setKey('popular')
      ->setUrl(OW::getRouter()->urlForRoute('groups-most-popular'))
      ->setOrder(1)
      ->setIconClass('ow_ic_comment');

    $items[1] = new BASE_MenuItem();
    $items[1]->setLabel($language->text('groups', 'group_list_menu_item_latest'))
      ->setKey('latest')
      ->setUrl(OW::getRouter()->urlForRoute('groups-latest'))
      ->setOrder(2)
      ->setIconClass('ow_ic_clock');


    if (OW::getUser()->isAuthenticated()) {
      $items[2] = new BASE_MenuItem();
      $items[2]->setLabel($language->text('groups', 'group_list_menu_item_my'))
        ->setKey('my')
        ->setUrl(OW::getRouter()->urlForRoute('groups-my-list'))
        ->setOrder(3)
        ->setIconClass('ow_ic_files');

      $items[3] = new BASE_MenuItem();
      $items[3]->setLabel($language->text('groups', 'group_list_menu_item_invite'))
        ->setKey('invite')
        ->setUrl(OW::getRouter()->urlForRoute('groups-invite-list'))
        ->setOrder(4)
        ->setIconClass('ow_ic_bookmark');
    }

    $items[4] = new BASE_MenuItem();
    $items[4]->setLabel($language->text('advancedgroups', 'group_list_menu_item_tagged'))
      ->setKey('tagged')
      ->setUrl(OW::getRouter()->urlForRoute('groups_tag_list'))
      ->setOrder(5)
      ->setIconClass('ow_ic_tag');

    return new BASE_CMP_ContentMenu($items);
  }

}
// Additional calsses

class GROUPS_UserList extends BASE_CMP_Users
{
    /**
     *
     * @var GROUPS_BOL_Group
     */
    protected $groupDto;

    public function __construct( GROUPS_BOL_Group $groupDto, $list, $itemCount, $usersOnPage, $showOnline = true)
    {
        parent::__construct($list, $itemCount, $usersOnPage, $showOnline);
        $this->groupDto = $groupDto;
    }

    public function getContextMenu($userId)
    {
        if ( !OW::getUser()->isAuthenticated() )
        {
            return null;
        }

        $isOwner = $this->groupDto->userId == OW::getUser()->getId();
        $isGroupModerator = OW::getUser()->isAuthorized('groups');

        $contextActionMenu = new BASE_CMP_ContextAction();

        $contextParentAction = new BASE_ContextAction();
        $contextParentAction->setKey('group_user_' . $userId);
        $contextActionMenu->addAction($contextParentAction);

        if ( ($isOwner || $isGroupModerator) && $userId != OW::getUser()->getId() )
        {
            $contextAction = new BASE_ContextAction();
            $contextAction->setParentKey($contextParentAction->getKey());
            $contextAction->setKey('delete_group_user');
            $contextAction->setLabel(OW::getLanguage()->text('groups', 'delete_group_user_label'));

            if ( $this->groupDto->userId != $userId )
            {
                $callbackUri = OW::getRequest()->getRequestUri();
                $deleteUrl = OW::getRequest()->buildUrlQueryString(OW::getRouter()->urlFor('GROUPS_CTRL_Groups', 'deleteUser', array(
                    'groupId' => $this->groupDto->id,
                    'userId' => $userId
                )), array(
                    'redirectUri' => urlencode($callbackUri)
                ));

                $contextAction->setUrl($deleteUrl);

                $contextAction->addAttribute('data-message', OW::getLanguage()->text('groups', 'delete_group_user_confirmation'));
                $contextAction->addAttribute('onclick', "return confirm($(this).data().message)");
            }
            else
            {
                $contextAction->setUrl('javascript://');
                $contextAction->addAttribute('data-message', OW::getLanguage()->text('groups', 'group_owner_delete_error'));
                $contextAction->addAttribute('onclick', "OW.error($(this).data().message); return false;");
            }

            $contextActionMenu->addAction($contextAction);
        }

        return $contextActionMenu;
    }

    public function getFields( $userIdList )
    {
        $fields = array();

        $qs = array();

        $qBdate = BOL_QuestionService::getInstance()->findQuestionByName('birthdate');

        if ( $qBdate !== null && $qBdate->onView )
            $qs[] = 'birthdate';

        $qSex = BOL_QuestionService::getInstance()->findQuestionByName('sex');

        if ( $qSex !== null && $qSex->onView )
            $qs[] = 'sex';

        $questionList = BOL_QuestionService::getInstance()->getQuestionData($userIdList, $qs);

        foreach ( $questionList as $uid => $question )
        {

            $fields[$uid] = array();

            $age = '';

            if ( !empty($question['birthdate']) )
            {
                $date = UTIL_DateTime::parseDate($question['birthdate'], UTIL_DateTime::MYSQL_DATETIME_DATE_FORMAT);

                $age = UTIL_DateTime::getAge($date['year'], $date['month'], $date['day']);
            }

            $sexValue = '';
            if ( !empty($question['sex']) )
            {
                $sex = $question['sex'];

                for ( $i = 0; $i < 31; $i++ )
                {
                    $val = pow(2, $i);
                    if ( (int) $sex & $val )
                    {
                        $sexValue .= BOL_QuestionService::getInstance()->getQuestionValueLang('sex', $val) . ', ';
                    }
                }

                if ( !empty($sexValue) )
                {
                    $sexValue = substr($sexValue, 0, -2);
                }
            }

            if ( !empty($sexValue) && !empty($age) )
            {
                $fields[$uid][] = array(
                    'label' => '',
                    'value' => $sexValue . ' ' . $age
                );
            }
        }

        return $fields;
    }
}

class GROUPS_GroupForm extends Form
{
    public function __construct( $formName )
    {
        parent::__construct($formName);

        $this->setEnctype(Form::ENCTYPE_MULTYPART_FORMDATA);

        $language = OW::getLanguage();

        $field = new TextField('title');
        $field->setRequired(true);
        $field->setLabel($language->text('groups', 'create_field_title_label'));
        $this->addElement($field);

        $field = new WysiwygTextarea('description');
        $field->setLabel($language->text('groups', 'create_field_description_label'));
        $field->setRequired(true);
        $this->addElement($field);

        // category
        $categoryField = new Selectbox('category');
        $categoryField->setLabel(OW::getLanguage()->text('advancedgroups', 'category'));
        $categories = array();
        foreach (ADVANCEDGROUPS_BOL_CategoriesService::getInstance()->getCategoriesList() as $key => $item) {
          $categories[$item['id']] = $item['title'];
        }
        $categoryField->setOptions($categories);
        $categoryField->setRequired(true);
        $this->addElement($categoryField);

        $field = new GROUPS_Image('image');
        $field->setLabel($language->text('groups', 'create_field_image_label'));
        $field->addValidator(new GROUPS_ImageValidator());
        $this->addElement($field);

        $field = new TagsInputField('tf');
        $field->setLabel(OW::getLanguage()->text('advancedgroups', 'tags_field_label'));
        $field->setMinChars(2);
        $this->addElement($field);

        $whoCanView = new RadioField('whoCanView');
        $whoCanView->setRequired();
        $whoCanView->addOptions(
            array(
                ADVANCEDGROUPS_BOL_GroupService::WCV_ANYONE => $language->text('groups', 'form_who_can_view_anybody'),
                ADVANCEDGROUPS_BOL_GroupService::WCV_INVITE => $language->text('groups', 'form_who_can_view_invite')
            )
        );
        $whoCanView->setLabel($language->text('groups', 'form_who_can_view_label'));
        $this->addElement($whoCanView);

        $whoCanInvite = new RadioField('whoCanInvite');
        $whoCanInvite->setRequired();
        $whoCanInvite->addOptions(
            array(
                ADVANCEDGROUPS_BOL_GroupService::WCI_PARTICIPANT => $language->text('groups', 'form_who_can_invite_participants'),
                ADVANCEDGROUPS_BOL_GroupService::WCI_CREATOR => $language->text('groups', 'form_who_can_invite_creator')
            )
        );
        $whoCanInvite->setLabel($language->text('groups', 'form_who_can_invite_label'));
        $this->addElement($whoCanInvite);
    }

    /**
     *
     * @param GROUPS_BOL_Group $group
     * @return GROUPS_BOL_Group
     */
    public function processGroup( ADVANCEDGROUPS_BOL_Group $group )
    {
        $values = $this->getValues();
        $service = ADVANCEDGROUPS_BOL_GroupService::getInstance();

        if ( $values['image'] )
        {
            if ( !empty($group->imageHash) )
            {
                OW::getStorage()->removeFile($service->getGroupImagePath($group));
                OW::getStorage()->removeFile($service->getGroupImagePath($group, ADVANCEDGROUPS_BOL_GroupService::IMAGE_SIZE_BIG));
            }

            $group->imageHash = uniqid();
        }

        $group->title = strip_tags($values['title']);
        
        $values['description'] = UTIL_HtmlTag::stripJs($values['description']);
        $values['description'] = UTIL_HtmlTag::stripTags($values['description'], array('frame'), array(), true);
        
        $group->description = $values['description'];
        $group->categoryId  = $values['category'];
        $group->whoCanInvite = $values['whoCanInvite'];
        $group->whoCanView = $values['whoCanView'];
        
        $service->saveGroup($group);

        if ( !empty($values['image']) )
        {
            $this->saveImages($values['image'], $group);
        }


        $tags = $values['tf'];
        foreach ($tags as $id => $tag)
        {
          $tags[$id] = UTIL_HtmlTag::stripTags($tag);
        }
        $tagService = BOL_TagService::getInstance();
        $tagService->updateEntityTags($group->id, ADVANCEDGROUPS_BOL_GroupService::ENTITY_TYPE_GROUP, $tags );

        return $group;
    }

    protected function saveImages( $postFile, ADVANCEDGROUPS_BOL_Group $group )
    {
        $service = ADVANCEDGROUPS_BOL_GroupService::getInstance();
        
        $smallFile = $service->getGroupImagePath($group, ADVANCEDGROUPS_BOL_GroupService::IMAGE_SIZE_SMALL);
        $bigFile = $service->getGroupImagePath($group, ADVANCEDGROUPS_BOL_GroupService::IMAGE_SIZE_BIG);
        
        $tmpDir = OW::getPluginManager()->getPlugin('groups')->getPluginFilesDir();
        $smallTmpFile = $tmpDir . uniqid('small_') . '.jpg';
        $bigTmpFile = $tmpDir . uniqid('big_') . '.jpg';

        $image = new UTIL_Image($postFile['tmp_name']);
        $image->resizeImage(ADVANCEDGROUPS_BOL_GroupService::IMAGE_WIDTH_BIG, null)
            ->saveImage($bigTmpFile)
            ->resizeImage(ADVANCEDGROUPS_BOL_GroupService::IMAGE_WIDTH_SMALL, ADVANCEDGROUPS_BOL_GroupService::IMAGE_WIDTH_SMALL, true)
            ->saveImage($smallTmpFile);

        try
        {
            OW::getStorage()->copyFile($smallTmpFile, $smallFile);
            OW::getStorage()->copyFile($bigTmpFile, $bigFile);
        }
        catch ( Exception $e ) {}

        unlink($smallTmpFile);
        unlink($bigTmpFile);
    }

    public function process()
    {

    }
}

class GROUPS_CreateGroupForm extends GROUPS_GroupForm
{

    public function __construct()
    {
        parent::__construct('GROUPS_CreateGroupForm');

        $this->getElement('title')->addValidator(new GROUPS_UniqueValidator());

        $field = new Submit('save');
        $field->setValue(OW::getLanguage()->text('groups', 'create_submit_btn_label'));
        $this->addElement($field);
    }

    /**
     * (non-PHPdoc)
     * @see ow_plugins/groups/controllers/GROUPS_GroupForm#process()
     */
    public function process()
    {
        $groupDto = new ADVANCEDGROUPS_BOL_Group();
        $groupDto->timeStamp = time();
        $groupDto->userId = OW::getUser()->getId();

        $data = array();
        foreach ( $groupDto as $key => $value )
        {
            $data[$key] = $value;
        }

        $event = new OW_Event(ADVANCEDGROUPS_BOL_GroupService::EVENT_BEFORE_CREATE, array('groupId' => $groupDto->id), $data);
        OW::getEventManager()->trigger($event);
        $data = $event->getData();

        foreach ( $data as $k => $v )
        {
            $groupDto->$k = $v;
        }

        $group = $this->processGroup($groupDto);
        
        $is_forum_connected = OW::getConfig()->getValue('groups', 'is_forum_connected');
        // Add forum group
        if ( $is_forum_connected )
        {
            $event = new OW_Event('forum.create_group', array('entity' => 'groups', 'name' => $group->title, 'description' => $group->description, 'entityId' => $group->getId()));
            OW::getEventManager()->trigger($event);
        }
        
        if ( $group )
        {
            $event = new OW_Event(ADVANCEDGROUPS_BOL_GroupService::EVENT_CREATE, array('groupId' => $group->id));
            OW::getEventManager()->trigger($event);
        }
        
        $group = ADVANCEDGROUPS_BOL_GroupService::getInstance()->findGroupById($group->id);
        
        if ( $group->status == ADVANCEDGROUPS_BOL_Group::STATUS_ACTIVE )
        {
            BOL_AuthorizationService::getInstance()->trackAction('groups', 'create');
        }
        
        return $group;
    }
}

class GROUPS_EditGroupForm extends GROUPS_GroupForm
{
    /**
     *
     * @var ADVANCEDGROUPS_BOL_Group
     */
    private $groupDto;

    public function __construct( ADVANCEDGROUPS_BOL_Group $group )
    {
        parent::__construct('GROUPS_EditGroupForm');

        $this->groupDto = $group;

        $this->getElement('title')->setValue($group->title);
        $this->getElement('title')->addValidator(new GROUPS_UniqueValidator($group->title));
        $this->getElement('category')->setValue($group->categoryId);
        $this->getElement('description')->setValue($group->description);
        $this->getElement('whoCanView')->setValue($group->whoCanView);
        $this->getElement('whoCanInvite')->setValue($group->whoCanInvite);

        $tags = array();
        $arr = BOL_TagService::getInstance()->findEntityTags($group->id, ADVANCEDGROUPS_BOL_GroupService::ENTITY_TYPE_GROUP);

        foreach ( (!empty($arr) ? $arr : array() ) as $dto )
        {
          $tags[] = $dto->getLabel();
        }
        $this->getElement('tf')->setValue($tags);

        $field = new Submit('save');
        $field->setValue(OW::getLanguage()->text('groups', 'edit_submit_btn_label'));
        $this->addElement($field);
    }

    /**
     * (non-PHPdoc)
     * @see ow_plugins/groups/controllers/GROUPS_GroupForm#process()
     */
    public function process()
    {
        $result = $this->processGroup($this->groupDto);

        if ( $result )
        {
            $event = new OW_Event(ADVANCEDGROUPS_BOL_GroupService::EVENT_EDIT, array('groupId' => $this->groupDto->id));
            OW::getEventManager()->trigger($event);
        }

        return $result;
    }
}

class GROUPS_ImageValidator extends OW_Validator
{

    public function __construct()
    {

    }

    /**
     * @see OW_Validator::isValid()
     *
     * @param mixed $value
     */
    public function isValid( $value )
    {
        if ( empty($value) )
        {
            return true;
        }

        $realName = $value['name'];
        $tmpName = $value['tmp_name'];

        switch ( false )
        {
            case is_uploaded_file($tmpName):
                $this->setErrorMessage(OW::getLanguage()->text('groups', 'errors_image_upload'));
                return false;

            case UTIL_File::validateImage($realName):
                $this->setErrorMessage(OW::getLanguage()->text('groups', 'errors_image_invalid'));
                return false;
        }

        return true;
    }
}

class GROUPS_Image extends FileField
{

    public function getValue()
    {
        return empty($_FILES[$this->getName()]['tmp_name']) ? null : $_FILES[$this->getName()];
    }
}

class GROUPS_UniqueValidator extends OW_Validator
{
    private $exception;

    public function __construct( $exception = null )
    {
        $this->setErrorMessage(OW::getLanguage()->text('groups', 'group_already_exists'));

        $this->exception = $exception;
    }

    public function isValid( $value )
    {
        if ( !empty($this->exception) && trim($this->exception) == trim($value) )
        {
            return true;
        }

        $dto = ADVANCEDGROUPS_BOL_GroupService::getInstance()->findByTitle($value);

        if ( $dto === null )
        {
            return true;
        }

        return false;
    }
}
