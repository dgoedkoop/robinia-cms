<?php

class mod_Page extends mod_Container {
    public static function GetName()
    {
        return 'page';
    }
    public function LimitParent()
    {
        return array('Folder', 'Page');
    }
    public function LimitChildren()
    {
        return false;
    }
}

?>
