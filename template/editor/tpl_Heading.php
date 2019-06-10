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
    
    public function GetContents()
    {
        return "<h".$this->level.">".$this->text."</h".$this->level.">\n\n";
    }
    public function GetForm()
    {
        return '<label for="text">Koptekst:</label>'
             . '<input type="text" name="text" size="70" value="' 
             . htmlspecialchars($this->text) . "\">\n";
    }
    public function SetFromForm(array $formdata)
    {
        if (isset($formdata['text'])) {
            $this->SetText($formdata['text']);
        }
    }
    public static function TypeName()
    {
        return 'Kop';
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
