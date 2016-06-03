<?php

class tpl_Folder extends mod_Folder implements tpl_ElementInterface
{
    public function GetContents()
    {
        return '';
    }
    public function GetForm() 
    {
        return false;
    }
    public function SetFromForm(array $formdata)
    {
        return true;
    }
    public static function TypeName()
    {
        return 'Map';
    }
    public function SetFromModel(mod_Element $mod_element)
    {
        if (!($mod_element instanceof mod_Folder)) {
            return false;
        }
        return true;
    }
}

?>
