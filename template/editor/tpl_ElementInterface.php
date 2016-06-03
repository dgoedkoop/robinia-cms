<?php

interface tpl_ElementInterface
{
    public function GetContents();
    public function GetForm();
    public static function TypeName();
    /* 
     * This function should copy all data from the mod_ version of the class,
     * so that afterwards the tpl_* class represents the same data as the
     * equivalent mod_* class. Only class-specific data needs to be copied, so
     * copying the element ID etc. is not necessary.
     */
    public function SetFromModel(mod_Element $mod_element);
    /*
     * This function should set all class-specific properties from the form
     * data.
     */
    public function SetFromForm(array $formdata);
    /*
     * If the form of a descendent class provides a file upload field, the
     * following function should be implemented, so that the correct encoding
     * type for the form is set.
     */
//  public function GetFormHasFileUpload() { return true; }
}

?>
