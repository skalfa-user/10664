<?php

class CUSTOMPROFILEVIEW_CMP_DragAndDropEntityPanel extends BASE_CMP_DragAndDropFrontendPanel
{
    private $entityScheme;
    private $entitySettingList = array();
    private $entityPositionList = array();
    private $entityComponentList = array();
    private $entityClonedNameList = array();
    private $entityId;

    public function __construct( $placeName, $entityId, array $componentList, $customizeMode, $componentTemplate, $responderController = 'BASE_CTRL_AjaxComponentEntityPanel' )
    {
        $responderController = empty($responderController) ? "BASE_CTRL_AjaxComponentEntityPanel" : $responderController;

        parent::__construct($placeName, $componentList, $customizeMode, $componentTemplate, $responderController);

        $this->entityId = (int) $entityId;
        $this->assign('entityId', $this->entityId);
        $this->sharedData['entity'] = $this->entityId;

        $this->setSettingsClassName("BASE_CMP_ComponentEntitySettings");

        $this->assign('isPhotoActive', OW::getPluginManager()->isPluginActive('photo'));
        $this->assign('isCvideoUploadActive', OW::getPluginManager()->isPluginActive('cvideoupload'));
    }

    public function setEntityScheme( $scheme )
    {
        $this->entityScheme = $scheme;
    }

    public function setEntitySettingList( array $settingList )
    {
        $this->entitySettingList = $settingList;
    }

    public function setEntityPositionList( array $positionList )
    {
        $this->entityPositionList = $positionList;
    }

    public function setEntityComponentList( array $entityComponentList )
    {
        $this->entityComponentList = $entityComponentList;
    }

    protected function getCurrentScheme( $defaultScheme )
    {
        if ( empty($this->entityScheme) )
        {
            return $defaultScheme;
        }

        return $this->entityScheme;
    }

    protected function makePositionList( $defaultPositions )
    {
        $entityComponentList = $this->entityComponentList;

        $tmpList = array();

        foreach ( $defaultPositions as $item )
        {
            $componentFreezed = isset($this->settingList[$item['componentPlaceUniqName']]['freeze'])
                && $this->settingList[$item['componentPlaceUniqName']]['freeze'];

            if ( isset($entityComponentList[$item['componentPlaceUniqName']]) && !$componentFreezed )
            {
                continue;
            }

            $tmpList[$item['componentPlaceUniqName']] = $item;
        }

        foreach ( $this->entityPositionList as $item )
        {
            $tmpList[$item['componentPlaceUniqName']] = $item;
        }

        return parent::makePositionList($tmpList);
    }

    protected function makeComponentList( $defaultComponentList )
    {
        $entityList = array();

        foreach ( $this->entityComponentList as $item )
        {
            if ( !isset($defaultComponentList[$item['uniqName']]) )
            {
                $this->entityClonedNameList[] = $item['uniqName'];
            }
            $entityList[$item['uniqName']] = $item;
        }

        unset($defaultComponentList['profile-PHOTO_CMP_UserPhotoAlbumsWidget']);
        unset($defaultComponentList['profile-CVIDEOUPLOAD_CMP_UserVideoWidget']);
        unset($defaultComponentList['profile-CVIDEOUPLOAD_CMP_MyVideoWidget']);
        return parent::makeComponentList(array_merge($defaultComponentList, $entityList));
    }

    protected function makeSettingList( $defaultSettingtList )
    {
        foreach ( $this->entitySettingList as $key => $item )
        {
            $defaultSettingtList[$key] = empty($defaultSettingtList[$key]) ? $this->entitySettingList[$key] : array_merge($defaultSettingtList[$key], $this->entitySettingList[$key]);
        }

        return parent::makeSettingList($defaultSettingtList);
    }

    protected function isComponentClone($uniqName)
    {
        return in_array($uniqName, $this->entityClonedNameList);
    }
}