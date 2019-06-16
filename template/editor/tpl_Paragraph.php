<?php

class tpl_Paragraph extends mod_Paragraph implements tpl_ElementInterface
{
    public function GetContents()
    {
        return "<p>".$this->text."</p>\n\n";
    }
    public function GetForm() 
    {
        return '<label for="text">'.tr('text').':</label>'
             . '<textarea name="text" rows="10" cols="70">'
             . htmlspecialchars($this->text) . "</textarea>\n";
    }
    public function SetFromForm(array $formdata)
    {
        if (isset($formdata['text'])) {
            $this->SetText($formdata['text']);
        }
    }
    public static function TypeName()
    {
        return tr('typeparagraph');
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
