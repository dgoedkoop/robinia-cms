<?php

class tpl_Usergroup extends mod_Usergroup implements tpl_ElementInterface
{
    public function GetOutput()
    {
	return '';
    }
    public function SetFromModel(mod_Element $mod_element)
    {
        if (!($mod_element instanceof mod_Usergroup)) {
            return false;
        }
        return true;
    }
}

?>
