<?php

/*
 * This class doesn't output anything by itself. It is rather a container for
 * data to be used by the parent class.
 */
class tpl_TitleDescription extends mod_TitleDescription implements tpl_ElementInterface
{
    public function GetOutput()
    {
        return false;
    }
    public function SetFromModel(mod_Element $mod_element)
    {
        if (!($mod_element instanceof mod_TitleDescription)) {
            return false;
        }
        $this->SetTitle($mod_element->GetTitle());
        $this->SetDescription($mod_element->GetDescription());
        $this->SetLinkname($mod_element->GetLinkname());
        return true;
    }
}

?>
