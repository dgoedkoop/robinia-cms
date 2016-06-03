<?php

class mod_ImageGallery extends mod_Element {
    public static function GetName()
    {
        return 'image_gallery';
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
        return array('Container');
    }
    public function LimitChildren()
    {
        return array('Image', 'Video');
    }
}

?>
