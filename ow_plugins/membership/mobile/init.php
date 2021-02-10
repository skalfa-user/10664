<?php

/**
 * Copyright (c) 2016, Skalfa LLC
 * All rights reserved.
 *
 * ATTENTION: This commercial software is intended for use with Oxwall Free Community Software http://www.oxwall.com/
 * and is licensed under Oxwall Store Commercial License.
 *
 * Full text of this license can be found at http://developers.oxwall.com/store/oscl
 */

OW::getRouter()->addRoute(new OW_Route('membership_subscribe', 'membership/subscribe', 'MEMBERSHIP_MCTRL_Subscribe', 'index'));
OW::getRouter()->addRoute(new OW_Route('membership_info_mobile', 'membership/info-mobile/:membershipId/:showCurrentMembershipInfo/', 'MEMBERSHIP_MCTRL_Subscribe', 'membershipInfo'));
OW::getRouter()->addRoute(new OW_Route('your_membership_info_mobile', 'membership/info-mobile/:membershipId/:showCurrentMembershipInfo/:yourMembership/', 'MEMBERSHIP_MCTRL_Subscribe', 'membershipInfo'));
OW::getRouter()->addRoute(new OW_Route('membership_pay_page_mobile', 'membership/payPage-mobile/:planId/', 'MEMBERSHIP_MCTRL_Subscribe', 'payPage'));

OW_ViewRenderer::getInstance()->registerFunction('membership_format_date', array('MEMBERSHIP_BOL_MembershipService', 'formatDate'));

MEMBERSHIP_MCLASS_EventHandler::getInstance()->init();
MEMBERSHIP_CLASS_EventHandler::getInstance()->genericInit();
