<?php

class mod_Container extends mod_Element
{
    public static function GetName()
    {
        return 'container';
    }
    public static function GetDbTableName()
    {
        return false;
    }
    public function GetDbColumnNames()
    {
        return false;
    }
    public function AddFromDb(array $row)
    {
        return true;
    }
    public function GetForDb()
    {
        return array();
    }
    public function GetDbTableDefinition()
    {
        return false;
    }
    public function LimitParent()
    {
        return false;
    }
    public function LimitChildren()
    {
        return false;
    }
}

?>
