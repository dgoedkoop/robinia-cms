<?php

class tpl_Folder extends mod_Folder implements tpl_ElementInterface
{
    public function GetOutput()
    {
	$output = "";
	foreach ($this->children as $child_element) {
	    $output .= $child_element->GetOutput();
	}
	return $output;
    }
    public function SetFromModel(mod_Element $mod_element)
    {
        if (!($mod_element instanceof mod_Folder)) {
            return false;
        }
        return true;
    }
}

?>
