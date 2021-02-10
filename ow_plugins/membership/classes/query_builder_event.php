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

/**
 * Class MEMBERSHIP_CLASS_QueryBuilderEvent
 */
class MEMBERSHIP_CLASS_QueryBuilderEvent extends BASE_CLASS_QueryBuilderEvent
{
    const OPTION_GROUP_BY = "group by";
    
    public function __construct( $name, array $options = array() )
    {
        parent::__construct($name, $options);

        $this->data["select"] = array();
        $this->data["group_by"] = array();
    }

    public function addSelect( $select )
    {
        $this->data["select"][] = $select;
    }

    public function getSelectList()
    {
        return $this->data["select"];
    }

    public function getSelect()
    {
        $selectList = $this->getSelectList();

        if ( empty($selectList) )
        {
            return "";
        }

        $selectStr = "";
        $sep = ", ";

        foreach ( $selectList as $select )
        {
            $selectStr .= $sep . $select;
        }

        return $selectStr;
    }

    public function addGroupBy( $field )
    {
        $this->data["group_by"][$field] = '';
    }

    public function getGroupByList()
    {
        return $this->data["group_by"];
    }

    public function getGroupBy()
    {
        $groupByList = $this->getGroupByList();

        if ( empty($groupByList) )
        {
            return "";
        }
        
        $groupByStr = "";
        $sep = "";
        
        foreach ( $groupByList as $field => $empty )
        {
            $groupByStr .= $sep . self::OPTION_GROUP_BY  . " " . $field;
            $sep = ", ";
        }
        
        return $groupByStr;
    }
}