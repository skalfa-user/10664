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
if (!OW::getPluginManager()->isPluginActive('groups')) {
    $event = new OW_Event(OW_EventManager::ON_BEFORE_PLUGIN_UNINSTALL, array( 'pluginKey' => 'Advanced Groups' ));
    OW::getEventManager()->trigger($event);
    ADVANCEDGROUPS_CLASS_Api::getInstance()->databaseUninstall();
    BOL_PluginService::getInstance()->uninstall('advancedgroups');
    OW::getFeedback()->error('"Advanced Groups" is uninstalled. Please install/active Groups plugin first!');
}

$plugin = OW::getPluginManager()->getPlugin('advancedgroups');
//Frontend Routs
OW::getRouter()->removeRoute('groups-create');
OW::getRouter()->addRoute(new OW_Route('groups-create', 'groups/create', 'ADVANCEDGROUPS_CTRL_Groups', 'create'));
OW::getRouter()->removeRoute('groups-edit');
OW::getRouter()->addRoute(new OW_Route('groups-edit', 'groups/:groupId/edit', 'ADVANCEDGROUPS_CTRL_Groups', 'edit'));
OW::getRouter()->removeRoute('groups-most-popular');
OW::getRouter()->addRoute(new OW_Route('groups-most-popular', 'groups/most-popular', 'ADVANCEDGROUPS_CTRL_Groups', 'mostPopularList'));
OW::getRouter()->removeRoute('groups-latest');
OW::getRouter()->addRoute(new OW_Route('groups-latest', 'groups/latest', 'ADVANCEDGROUPS_CTRL_Groups', 'latestList'));
OW::getRouter()->removeRoute('groups-index');
OW::getRouter()->addRoute(new OW_Route('groups-index', 'groups', 'ADVANCEDGROUPS_CTRL_Groups', 'index'));
OW::getRouter()->addRoute(new OW_Route('groups_tag_list', 'groups/tagged', "ADVANCEDGROUPS_CTRL_Groups", 'taglist'));
OW::getRouter()->addRoute(new OW_Route('groups_view_tagged_list', 'groups/tagged/:tag', "ADVANCEDGROUPS_CTRL_Groups", 'taglist'));

OW::getRouter()->addRoute(new OW_Route('advancedgroups-admin', 'admin/advancedgroups', "ADVANCEDGROUPS_CTRL_Admin", 'index'));
OW::getRouter()->addRoute(new OW_Route('groups-admin-categories', 'admin/advancedgroups/categories', "ADVANCEDGROUPS_CTRL_Admin", 'categories'));
OW::getRouter()->addRoute(new OW_Route('advancedgroups-uninstall', 'admin/advancedgroups/uninstall', 'ADVANCEDGROUPS_CTRL_Admin', 'uninstall'));

/**
 * decorators
 */
OW::getThemeManager()->addDecorator('advancedgroups_list_item', $plugin->getKey());
OW::getThemeManager()->addDecorator('advancedgroups_list', $plugin->getKey());