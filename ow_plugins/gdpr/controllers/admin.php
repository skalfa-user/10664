<?php

/**
 * Copyright (c) 2018, Skalfa LLC
 * All rights reserved.
 *
 * ATTENTION: This commercial software is intended for use with Oxwall Free Community Software http://www.oxwall.com/
 * and is licensed under Oxwall Store Commercial License.
 *
 * Full text of this license can be found at http://developers.oxwall.com/store/oscl
 */

class GDPR_CTRL_Admin extends ADMIN_CTRL_Abstract
{
    /**
     * Menu
     */
    private $menu;

    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct();

        $this->menu = $this->getMenu();
    }

    /**
     * Default action index
     */
    public function index()
    {
        $form = new GDPR_CLASS_SettingsForm();
        $this->addForm($form);

        if ( isset($_POST['gdpr-save']) && $form->isValid($_POST) )
        {
            $form->process();
            OW::getFeedback()->info(OW::getLanguage()->text('gdpr', 'gdpr_save_config'));
        }

        $this->addComponent('menu', $this->getMenu('settings'));
        $this->setPageHeading(OW::getLanguage()->text('gdpr', 'gdpr_configuration'));
    }

    /**
     * Action search
     */
    public function search()
    {
        $search_form = new GDPR_CLASS_SearchForm();
        $this->addForm($search_form);
        $isSubmit = false;

        if ( isset($_POST['gdpr_search_btn']) && $search_form->isValid($_POST) )
        {
            $isSubmit = true;
            $username = strip_tags($_POST['gdpr_search_input']);
            $user = BOL_UserService::getInstance()->findByUsername($username);

            if ( $user )
            {
                $avatar = BOL_AvatarService::getInstance()->getDataForUserAvatars([$user->id]);

                foreach ( $avatar as $item )
                {
                    $avatarItem[] = $item;
                }

                $this->assign('avatar', $avatarItem[0]);
                $this->assign('user', $user);
            }
        }

        $this->assign('isSubmit', $isSubmit);
        $this->addComponent('menu', $this->getMenu('search'));
        $this->setPageHeading(OW::getLanguage()->text('gdpr', 'gdpr_configuration'));
    }

    /**
     * Action export csv
     */
    public function exportCsv()
    {
        ini_set('memory_limit', '-1');

        if ( isset($_GET['userId']) )
        {
            $userId = intval($_GET['userId']);
        }

        $data = $this->userData($userId);
        $userData = array_values($data);
        $userDataLabels = array_keys($data);

        $this->createDumpDir();

        $fileName = 'dump-' . date('d-m-y') . '.csv';
        $tmpDirPath = $this->tmpDir();
        $file = $tmpDirPath . $fileName;
        $fp = fopen( $file, 'w');

        fputcsv($fp, $userDataLabels);
        fputcsv($fp, $userData);
        fclose($fp);

        if ( file_exists($file) )
        {
            ob_end_clean();
            header('Content-Description: File Transfer');
            header('Content-Type: application/octet-stream');
            header('Content-Disposition: attachment; filename=' . $fileName);
            header('Content-Transfer-Encoding: binary');
            header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
            header('Pragma: public');
            header('Content-Length: ' . filesize($file));
            ob_end_clean();
            readfile($file);
            @unlink($file);

            exit();
        }

        $this->redirect($_SERVER['HTTP_REFERER']);

        exit;
    }

    private function getViewQuestions( array $userIds, array $exclude = [] )
    {
        $questionService = BOL_QuestionService::getInstance();
        $userService = BOL_UserService::getInstance();

        $sortedSections = $questionService->findSortedSectionList();
        $viewSections  = [];

        // process user list
        foreach( $userIds as $userId )
        {
            $userDto = $userService->findUserById($userId);

            if ( $userDto )
            {
                // get all view questions
                $viewQuestionList = $userService->getUserViewQuestions($userId, false);

                // process questions
                foreach ( $viewQuestionList['questions'] as $sectionName => $section )
                {
                    $order = 0;

                    foreach ( $sortedSections as $sorted )
                    {
                        if ( $sorted->name == $sectionName )
                        {
                            $order = $sorted->sortOrder;
                        }
                    }

                    $viewSections[$userDto->getId()][$sectionName] = [
                        'order' => (int) $order,
                        'section' => $questionService->getSectionLang($sectionName),
                        'items' => []
                    ];
                }

                // fill sections with questions
                $data = $viewQuestionList['data'][$userId];
                unset($viewQuestionList['questions']['about_my_match']);

                foreach ( $viewQuestionList['questions'] as $sectName => $section )
                {
                    foreach ( $section as $question )
                    {
                        $name = $question['name'];

                        if (in_array($name, $exclude)) {
                            continue;
                        }

                        $value = is_array($data[$name]) ? implode(', ', $data[$name]) :  $data[$name];

                        // get new label
                        $event = new OW_Event('base.questions_field_get_label', [
                            'presentation' => $question['presentation'],
                            'fieldName' => $question['name'],
                            'configs' => $question['custom'],
                            'type' => 'view'
                        ]);

                        OW::getEventManager()->trigger($event);
                        $newLabel = $event->getData();

                        // get new value
                        $event = new OW_Event('base.questions_field_get_value', [
                            'presentation' => $question['presentation'],
                            'fieldName' => $question['name'],
                            'value' => $data[$name],
                            'questionInfo' => $question,
                            'userId' => $userId
                        ]);

                        OW::getEventManager()->trigger($event);
                        $newValue = $event->getData();

                        $viewSections[$userId][$sectName]['items'][] = [
                            'name' => $name,
                            'label' => !empty($newLabel) ? $newLabel : $questionService->getQuestionLang($name),
                            'value' => !empty($newValue) ? trim(strip_tags($newValue)) : trim(strip_tags($value))
                        ];
                    }
                }

                // sort sections
                usort($viewSections[$userDto->getId()], function( $el1, $el2 )
                {
                    if ( $el1['order'] === $el2['order'] )
                    {
                        return 0;
                    }

                    return $el1['order'] > $el2['order'] ? 1 : -1;
                });
            }
        }

        return $viewSections;
    }

    private function createDumpDir()
    {
        $pluginFileDir = $this->pluginFileDir();

        if ( !dir($pluginFileDir) )
        {
            @mkdir($pluginFileDir);
        }

        chmod($pluginFileDir, 0777);

        $dumpDir = $this->tmpDir();
        @mkdir($dumpDir);
        chmod($dumpDir, 0777);
    }

    private function tmpDir()
    {
        return  OW::getPluginManager()->getPlugin('gdpr')->getPluginFilesDir() . 'dump' . DS . 'tmp' . DS;
    }

    private function pluginFileDir()
    {
        return  OW::getPluginManager()->getPlugin('gdpr')->getPluginFilesDir() . 'dump' . DS;
    }

    private function userData( $userId )
    {
        $userData = [];
        $questions = $this->getViewQuestions([$userId], []);

        if ( !empty($questions[$userId]) )
        {
            foreach ( $questions[$userId] as $questionKey => $question )
            {
                if ( !empty($question['items']) )
                {
                    foreach ( $question['items'] as $key => $value )
                    {
                        $userData[$value['label']] = $value['value'];
                    }
                }
            }
        }

        return $userData;
    }

    private function getMenu( $active = 'settings' )
    {
        $language = OW::getLanguage();

        $item = new BASE_MenuItem();
        $item->setLabel($language->text('gdpr', 'gdpr_admin_menu_settings'));
        $item->setUrl(OW::getRouter()->urlForRoute('gdpr-admin', ['listType' => 'settings']));
        $item->setKey('settings');
        $item->setOrder(1);
        $item->setActive($active == 'settings');

        $item2 = new BASE_MenuItem();
        $item2->setLabel($language->text('gdpr', 'gdpr_admin_menu_search'));
        $item2->setUrl(OW::getRouter()->urlForRoute('gdpr-admin', ['listType' => 'search']));
        $item2->setKey('search');
        $item2->setOrder(2);
        $item2->setActive($active == 'search');

        return new BASE_CMP_ContentMenu([$item, $item2]);
    }
}
