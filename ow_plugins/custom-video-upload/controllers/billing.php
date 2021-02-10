<?php

class CVIDEOUPLOAD_CTRL_Billing extends BASE_CTRL_Billing
{
    use BASE_CLASS_BillingMethodsTrait;

    /**
     * Class constructor
     */
    public function __construct()
    {
        parent::__construct();
    }


    public function completed( array $params )
    {
        $service = CVIDEOUPLOAD_BOL_Service::getInstance();
        /* @var CVIDEOUPLOAD_BOL_Service $service */

        parent::completed($params);

        if ( !empty($service->getSessionBackUrl()) )
        {
            $service->unsetSessionBackUrl();

            if ( $this->assignedVars['message'] )
            {
                OW::getFeedback()->info($this->assignedVars['message']);
            }

            $this->redirect(OW::getRouter()->urlForRoute('cvideoupload.video-upload'));
        }
    }
}