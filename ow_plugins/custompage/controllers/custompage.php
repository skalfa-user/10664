<?php

class CUSTOMPAGE_CTRL_Custompage extends OW_ActionController
{

    public function index()
    {
        $this->setPageTitle(OW::getLanguage()->text('custompage', 'pricing_page_title'));
        $this->setPageHeading(OW::getLanguage()->text('custompage', 'pricing_page_heading'));

    }

    public function suppliers()
    {
        $this->setPageTitle(OW::getLanguage()->text('custompage', 'pricing_page_title'));
        $this->setPageHeading(OW::getLanguage()->text('custompage', 'pricing_page_heading'));

    }
    public function customers()
    {
        $this->setPageTitle(OW::getLanguage()->text('custompage', 'pricing_page_title'));
        $this->setPageHeading(OW::getLanguage()->text('custompage', 'pricing_page_heading'));

    }
}

