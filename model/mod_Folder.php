<?php

class mod_Folder extends mod_Container
{
    public static function GetName()
    {
        return 'folder';
    }
    public function LimitParent()
    {
        return array('Folder');
    }
    public function LimitChildren()
    {
        return array('Folder', 'Page', 'TitleDescription', 'User', 'Usergroup');
    }
}

?>
