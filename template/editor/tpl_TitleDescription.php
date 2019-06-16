<?php

class tpl_TitleDescription extends mod_TitleDescription implements tpl_ElementInterface
{
    public function GetContents()
    {
        return '<p>'.tr('title').': ' . $this->title . '</p>'
             . '<p>'.tr('description').': ' . $this->description . '</p>'
             . '<p>'.tr('linkname').': ' . $this->linkname . '</p>';
    }
    public function GetForm()
    {
        return '<label for="title">'.tr('title').':</label>'
             . '<input type="text" name="title" size="70" value="' 
             . htmlspecialchars($this->title) . "\">\n"
             . '<label for="description">'.tr('description').':</label>'
             . '<textarea name="description" rows="10" cols="70">'
             . htmlspecialchars($this->description) . "</textarea>\n"
             . '<label for="linkname">'.tr('linkname').':</label>'
             . '<input type="text" name="linkname" size="70" value="' 
             . htmlspecialchars($this->linkname) . "\">\n";
    }
    public static function TypeName()
    {
        return tr('typetitledescription');
    }
    public function SetFromModel(mod_Element $mod_element)
    {
        if (!($mod_element instanceof mod_TitleDescription)) {
            return false;
        }
        $this->SetTitle($mod_element->GetTitle());
        $this->SetDescription($mod_element->GetDescription());
        $this->SetLinkname($mod_element->GetLinkname());
        return true;
    }
    public function SetFromForm(array $formdata)
    {
        if (isset($formdata['title'])) {
            $this->SetTitle($formdata['title']);
        }
        if (isset($formdata['description'])) {
            $this->SetDescription($formdata['description']);
        }
        if (isset($formdata['linkname'])) {
            $this->SetLinkname($formdata['linkname']);
        }
    }
}

?>
