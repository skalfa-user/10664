<?php

/**
 * Class BIRTHDAYF_CLASS_EventHandler
 *
 * @author (A. S. B.) (D. P.) <azazel9966@gmail.com>.
 * @package ow_plugins.pluginkey
 * @since 1.8.4
 */
class BIRTHDAYF_CLASS_EventHandler
{
    /**
     * @var BIRTHDAYF_BOL_Service|null
     */
    private $service = null;

    /**
     * Singleton instance.
     *
     * @var BIRTHDAYF_CLASS_EventHandler
     */
    private static $classInstance;

    /**
     * Returns an instance of class (singleton pattern implementation).
     *
     * @return BIRTHDAYF_CLASS_EventHandler
     */
    public static function getInstance()
    {
        if ( self::$classInstance === null )
        {
            self::$classInstance = new self();
        }

        return self::$classInstance;
    }

    private function __construct()
    {
        $this->service = BIRTHDAYF_BOL_Service::getInstance();
    }

    public function init()
    {
        OW::getEventManager()->bind(OW_EventManager::ON_PLUGINS_INIT, [$this, 'afterInit']);
    }

    public function afterInit()
    {
        $eventManager = OW::getEventManager();

        $eventManager->bind('admin.plugins_list_view', [$this, 'pluginsListView']);
        $eventManager->bind('base.event.on_get_empty_required_questions', [$this, 'onGetEmptyRequiredQuestions'], 0);
        $eventManager->bind('base.event.on_find_edit_questions_for_account_type', [$this, 'onGetEmptyRequiredQuestions'], 0);
        $eventManager->bind('base.event.on_find_all_questions_for_account_type', [$this, 'onGetEmptyRequiredQuestions'], 0);
        $eventManager->bind('base.event.on_find_view_questions_for_account_type', [$this, 'onGetEmptyRequiredQuestions'], 0);
        $eventManager->bind('admin.disable_fields_on_edit_profile_question', [$this, 'disableProfileQuestions'], 0);
        $eventManager->bind('base.event.on_find_sign_up_questions_for_account_type', [$this, 'disableProfileQuestions'], 0);

        if ( OW::getUser()->isAuthenticated() )
        {
            $eventManager->bind('class.get_instance.HOTLIST_CMP_Index', [$this, 'onHotListIndex']);
        }
    }

    public function pluginsListView( OW_Event $event )
    {
        $data = $event->getData();

        if ( !empty($data['active']) || !empty($data['inactive']) )
        {
            if ( isset($data['active'][BIRTHDAYF_BOL_Service::PLUGIN_KEY]['un_url']) )
            {
                $data['active'][BIRTHDAYF_BOL_Service::PLUGIN_KEY]['un_url'] = null;
            }
        }

        $event->setData($data);
    }

    public function onGetEmptyRequiredQuestions( OW_Event $event )
    {
        $params = $event->getParams();
        $data = $event->getData();

        if ( !empty($params['account']) && !empty($data) )
        {
            foreach ( $data as $key => $question )
            {
                if ( in_array($question['name'], $this->service->getHiddenQuestion()) )
                {
                    unset($data[$key]);
                }
            }

            $event->setData($data);
        }
    }

    public function disableProfileQuestions( OW_Event $event )
    {
        $params = $event->getParams();

        if ( !empty($params['questionDto']) && $params['questionDto'] instanceof BOL_Question )
        {
            if ( in_array($params['questionDto']->name, $this->service->getHiddenQuestion()) )
            {
                $disableActionList = [
                    'disable_account_type' => true,
                    'disable_answer_type' => true,
                    'disable_presentation' => true,
                    'disable_column_count' => true,
                    'disable_display_config' => true,
                    'disable_possible_values' => true,
                    'disable_required' => true,
                    'disable_on_join' => true,
                    'disable_on_view' => true,
                    'disable_on_search' => true,
                    'disable_on_edit' => true
                ];

                $event->setData($disableActionList);
            }
        }
    }

    public function onHotListIndex( OW_Event $event )
    {
        $params = $event->getParams();

        $event->setData( OW::getClassInstanceArray('BIRTHDAYF_CMP_Index', $params['arguments']) );
    }
}