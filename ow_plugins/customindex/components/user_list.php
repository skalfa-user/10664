<?php

/**
 * User list
 */
class CUSTOMINDEX_CMP_UserList extends BASE_CMP_UserList
{
    /**
     * Get users component
     * 
     * @param array $list
     * @return \BASE_CMP_AvatarUserList
     */
    protected  function getUsersCmp( array $list )
    {
        $plugin = OW::getPluginManager()->getPlugin(CUSTOMINDEX_BOL_Service::PLUGIN_KEY);
        OW::getDocument()->addStyleSheet($plugin->getStaticCssUrl() . 'custom_index_circle_user_list.css');
        return new CUSTOMINDEX_CMP_AvatarUserList($list);
    }
}