<?php

class tpl_Listing extends mod_Listing implements tpl_ElementInterface
{
    public function GetOutput()
    {
	$output = "<ul>\n";
	foreach ($this->items as $item) {
	    $output .= "<li>".$item."\n";
        }
	$output .= "</ul>\n\n";
	return $output;	
    }
    public function SetFromModel(mod_Element $mod_element)
    {
        if (!($mod_element instanceof mod_Listing)) {
            return false;
        }
        $this->AddItems($mod_element->GetItems());
        return true;
    }
}

?>
