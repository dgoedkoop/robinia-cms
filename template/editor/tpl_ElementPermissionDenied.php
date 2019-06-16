<?php

class tpl_ElementPermissionDenied extends mod_Container
                                  implements tpl_ElementInterface
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
        return tr('typepermissiondenied');
    }
    public function SetFromModel(mod_Element $mod_element)
    {
        if (!($mod_element instanceof mod_ElementPermissionDenied)) {
            return false;
        }
        return true;
    }
}
?>
