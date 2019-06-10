<?php

class tpl_ElementPermissionDenied extends mod_Container
                                  implements tpl_ElementInterface
{
    public function GetOutput()
    {
    return '';
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
