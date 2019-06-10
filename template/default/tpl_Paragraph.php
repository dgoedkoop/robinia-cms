<?php

class tpl_Paragraph extends mod_Paragraph implements tpl_ElementInterface
{
    public function GetOutput()
    {
	    return "<p>".$this->text."</p>\n\n";
    }
    public function SetFromModel(mod_Element $mod_element)
    {
        if (!($mod_element instanceof mod_Paragraph)) {
            return false;
        }
        $this->SetText($mod_element->GetText());
        return true;
    }
}

?>
