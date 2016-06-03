<?php

class tpl_Heading extends mod_Heading implements tpl_ElementInterface
{
    protected $level = 0;

    public function GetLevel()
    {
	return $this->level;
    }
    public function SetLevel($level)
    {
	$this->level = $level;
    }
    
    public function GetOutput()
    {
	return "<h".$this->level.">".$this->text."</h".$this->level.">\n\n";
    }
    public function SetFromModel(mod_Element $mod_element)
    {
        if (!($mod_element instanceof mod_Heading)) {
            return false;
        }
        $this->SetText($mod_element->GetText());
        return true;
    }
}

?>
