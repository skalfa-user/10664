<?php

OW::getRouter()->addRoute(new OW_Route('custompage.index', 'pricing', "CUSTOMPAGE_CTRL_Custompage", 'index'));#
OW::getRouter()->addRoute(new OW_Route('custompage.suppliers', 'pricing/suppliers', "CUSTOMPAGE_CTRL_Custompage", 'suppliers'));
OW::getRouter()->addRoute(new OW_Route('custompage.customers', 'pricing/customers', "CUSTOMPAGE_CTRL_Custompage", 'customers'));
