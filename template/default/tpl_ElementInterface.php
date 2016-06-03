<?php

interface tpl_ElementInterface
{
    public function GetOutput();
    /* 
     * This function should copy all data from the mod_ version of the class,
     * so that afterwards the tpl_* class represents the same data as the
     * equivalent mod_* class. Only class-specific data needs to be copied, so
     * copying the element ID etc. is not necessary.
     */
    public function SetFromModel(mod_Element $mod_element);
}
?>
