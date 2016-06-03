<?php

class tpl_Page extends mod_Page implements tpl_ElementInterface
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
        return 'Pagina';
    }
    public function SetFromModel(mod_Element $mod_element)
    {
        if (!($mod_element instanceof mod_Page)) {
            return false;
        }
        return true;
    }
}

?>
