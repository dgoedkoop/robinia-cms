<?php

class tpl_Usergroup extends mod_Usergroup implements tpl_ElementInterface
{
    public function GetContents()
    {
        return '<p>Naam: ' . $this->GetGroupname() . '</p>';
    }
    public function GetForm()
    {
        return '<label for="groupname">Naam:</label>'
             . '<input type="text" name="groupname" size="70" value="' 
             . htmlspecialchars($this->GetGroupname()) . "\">\n";
    }
    public static function TypeName()
    {
        return 'Gebruikersgroep';
    }
    public function SetFromModel(mod_Element $mod_element)
    {
        if (!($mod_element instanceof mod_Usergroup)) {
            return false;
        }
        if ($mod_element->GIDIsset()) {
            $this->SetGID($mod_element->GetGID());
        }
        $this->SetGroupname($mod_element->GetGroupname());
        return true;
    }
    public function SetFromForm(array $formdata)
    {
        if (isset($formdata['groupname'])) {
            $this->SetGroupname($formdata['groupname']);
        }
    }
}

?>
