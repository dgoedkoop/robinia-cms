<?php

class tpl_TitleDescription extends mod_TitleDescription implements tpl_ElementInterface
{
    public function GetContents()
    {
        return '<p>Titel: ' . $this->title . '</p>'
             . '<p>Beschrijving: ' . $this->description . '</p>'
             . '<p>Naam voor link: ' . $this->linkname . '</p>';
    }
    public function GetForm()
    {
        return '<label for="title">Titel:</label>'
             . '<input type="text" name="title" size="70" value="' 
             . htmlspecialchars($this->title) . "\">\n"
             . '<label for="description">Beschrijving:</label>'
             . '<textarea name="description" rows="10" cols="70">'
             . htmlspecialchars($this->description) . "</textarea>\n"
             . '<label for="linkname">Naam voor link:</label>'
             . '<input type="text" name="linkname" size="70" value="' 
             . htmlspecialchars($this->linkname) . "\">\n";
    }
    public static function TypeName()
    {
        return 'Titel en beschrijving';
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
