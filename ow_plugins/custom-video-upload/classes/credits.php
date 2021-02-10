<?php

class CVIDEOUPLOAD_CLASS_Credits
{
    /**
     * Actions
     *
     * @var array
     */
    private $actions;

    /**
     * Auth actions
     *
     * @var array
     */
    private $authActions = [];

    /**
     * Class constructor
     */
    public function __construct()
    {
        // register credits actions
        $this->actions[] = [
            'pluginKey' => 'cvideoupload',
            'action' => 'upload_video',
            'amount' => 0
        ];

        $this->authActions['upload_video'] = 'upload_video';
    }

    /**
     * Bind credit action collect
     *
     * @param BASE_CLASS_EventCollector $eventCollector
     * @return void
     */
    public function bindCreditActionsCollect( BASE_CLASS_EventCollector $eventCollector )
    {
        foreach ( $this->actions as $action )
        {
            $eventCollector->add($action);
        }
    }

    /**
     * Trigger credit actions
     *
     * @return void
     */
    public function triggerCreditActionsAdd()
    {
        $eventCollector = new BASE_CLASS_EventCollector('usercredits.action_add');

        foreach ( $this->actions as $action )
        {
            $eventCollector->add($action);
        }

        OW::getEventManager()->trigger($eventCollector);
    }
}