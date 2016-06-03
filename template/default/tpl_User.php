<?php

class tpl_User extends mod_User implements tpl_ElementInterface
{
    public function GetOutput()
    {
	return '';
    }
    public function SetFromModel(mod_Element $mod_element)
    {
        if (!($mod_element instanceof mod_User)) {
            return false;
        }
        return true;
    }
}

?>
