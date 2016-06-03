<?php

class tpl_ImageGallery extends mod_ImageGallery implements tpl_ElementInterface
{
    public function GetContents()
    {
        // By itself, an image gallery contains nothing
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
        return 'Galerij';
    }
    public function SetFromModel(mod_Element $mod_element)
    {
        if (!($mod_element instanceof mod_ImageGallery)) {
            return false;
        }
        return true;
    }
}

?>
