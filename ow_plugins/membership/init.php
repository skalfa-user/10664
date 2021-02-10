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

OW::getRouter()->addRoute(new OW_Route('membership_admin', 'admin/membership', 'MEMBERSHIP_CTRL_Admin', 'index'));
OW::getRouter()->addRoute(new OW_Route('membership_admin_subscribe', 'admin/membership/subscribe', 'MEMBERSHIP_CTRL_Admin', 'subscribe'));
OW::getRouter()->addRoute(new OW_Route('membership_admin_browse_users_st', 'admin/membership/users', 'MEMBERSHIP_CTRL_Admin', 'users'));
OW::getRouter()->addRoute(new OW_Route('membership_admin_browse_users', 'admin/membership/users/role/:roleId', 'MEMBERSHIP_CTRL_Admin', 'users'));
OW::getRouter()->addRoute(new OW_Route('membership_admin_settings', 'admin/membership/settings', 'MEMBERSHIP_CTRL_Admin', 'settings'));
OW::getRouter()->addRoute(new OW_Route('membership_admin_add', 'admin/membership/add', 'MEMBERSHIP_CTRL_Admin', 'add'));
OW::getRouter()->addRoute(new OW_Route('membership_admin_edit_plans', 'admin/membership/edit-plans', 'MEMBERSHIP_CTRL_Admin', 'editPlans'));
OW::getRouter()->addRoute(new OW_Route('membership_delete_plans', 'admin/membership/ajax/delete-plans', 'MEMBERSHIP_CTRL_Ajax', 'deletePlans'));
OW::getRouter()->addRoute(new OW_Route('membership_delete_type', 'admin/membership/ajax/delete', 'MEMBERSHIP_CTRL_Ajax', 'deleteType'));
OW::getRouter()->addRoute(new OW_Route('membership_subscribe', 'membership/subscribe', 'MEMBERSHIP_CTRL_Subscribe', 'index'));

OW::getRouter()->addRoute(new OW_Route('membership_set', 'membership/ajax/set', 'MEMBERSHIP_CTRL_Ajax', 'set'));

OW_ViewRenderer::getInstance()->registerFunction('membership_format_date', array('MEMBERSHIP_BOL_MembershipService', 'formatDate'));

MEMBERSHIP_CLASS_EventHandler::getInstance()->init();