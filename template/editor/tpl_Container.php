<?php

/*
 * Because tpl_Container does not have any contents by itself, it is not
 * editable and tpl_Element::tpl_fmt_showform is therefore not supported.
 */
class tpl_Container extends mod_Container implements tpl_ElementInterface
{
    public function GetContents()
    {
        // By itself, a container has no contents.
        return "";
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
        return tr('typecontainer');
    }
    public function SetFromModel(mod_Element $mod_element)
    {
        if (!($mod_element instanceof mod_Container)) {
            return false;
        }
        return true;
    }
}
?>
