<?php

require_once('model/passwords.php');

class tpl_User extends mod_User implements tpl_ElementInterface
{
    private $allgroups = array();
    
    public function GetContents()
    {
        $output = '<p>Naam: ' . $this->GetUsername() . '</p>'
                . '<p>Lid van groepen: ';
        $groupnames = array();
        foreach($this->GetGroups() as $gid) {
            $groupname = false;
            foreach($this->allgroups as $group) {
                if ($gid == $group->GetGID()) {
                    $groupname = $group->GetGroupname();
                }
            }
            if ($groupname === false) {
                $groupname = "[$gid]";
            }
            $groupnames[] = $groupname;
        }
        $output .= implode(', ', $groupnames)
                 . '</p>';
        if ($this->GetAdmin()) {
            $output .= '<p>Deze gebruiker is een beheerder.</p>';
        }
        if ($this->GetRootID() > 1) {
            $output .= '<p>Startpunt voor deze gebruiker is element: '
                     . $this->GetRootID() . '</p>';
        }
        return $output;
    }
    public function GetForm()
    {
        $output = '<label for="name">Naam:</label>'
                . '<input type="text" name="name" size="70" value="' 
                . htmlspecialchars($this->GetUsername()) . "\">\n"
                . '<label for="password">Wachtwoord (laat leeg om niet te '
                . 'wijzigen):</label>'
                . '<input type="password" name="password" size="70">' . "\n"
                . '</fieldset><fieldset><legend>Groepslidmaatschappen</legend>'
                . '<div class=checkboxlist>';
        foreach($this->allgroups as $group) {
            if ($group->GetGID() > 2) {
                // Find out group name
                $groupname = $group->GetGroupname();
                $groupmember = false;
                foreach($this->GetGroups() as $gid) {
                    if ($group->GetGID() == $gid) {
                        $groupmember = true;
                    }
                }
                $element_name = 'group_' . $group->GetGID();
                $output .= '<div class=checkboxline><input type="checkbox" '
                         . 'name="' . htmlspecialchars($element_name)
                         . '" value="y"';
                if ($groupmember) {
                    $output .= ' CHECKED';
                }
                $output .= '><label for="' . htmlspecialchars($element_name)
                         . '">'. htmlspecialchars($groupname) 
                         . "</label></div>\n";
            }
        }
        $output .= "</div>\n";
        if ((!is_null($this->currentuser) && $this->currentuser->GetAdmin())
            || $this->GetAdmin()) {
            $output .= '<div class=checkboxline><input type="checkbox" '
                     . 'name="admin" value="y"';
            if ($this->GetAdmin()) {
                $output .= ' CHECKED';
            }
            $output .= '><label for="admin">Gebruiker is beheerder</label>'
                     . "</div>\n";
        }
        $output .= '<label for="rootid">Startpunt in backend is element:'
                 . '</label><input type="text" name="rootid" size="70" value="' 
                . htmlspecialchars($this->GetRootID()) . "\">\n";

        return $output;
    }
    public static function TypeName()
    {
        return 'Gebruiker';
    }
    public function SetFromModel(mod_Element $mod_element)
    {
        if (!($mod_element instanceof mod_User)) {
            return false;
        }
        if ($mod_element->UIDIsset()) {
            $this->SetUID($mod_element->GetUID());
        }
        foreach($mod_element->GetGroups() as $gid) {
            if ($gid > 2) {
                $this->AddGroup($gid);
            }
        }
        $this->SetUsername($mod_element->GetUsername());
        $this->SetPasshash($mod_element->GetPasshash());
        $this->SetModified($mod_element->GetModified());
        $this->SetAdmin($mod_element->GetAdmin());
        $this->SetRootID($mod_element->GetRootID());
        $this->ResetModified();
        return true;
    }
    public function SetFromForm(array $formdata)
    {
        if (isset($formdata['name']) && 
            ($formdata['name'] != $this->GetUsername())) {
            $this->SetUsername($formdata['name']);
        }
        $newgroups = array();
        foreach($formdata as $key => $value) {
            if ((substr($key, 0, 6) == 'group_') && ($value == 'y')){
                $gid = substr($key, 6);
                $ok = false;
                foreach ($this->allgroups as $group) {
                    if ($group->GetGID() == $gid) {
                        $ok = true;
                    }
                }
                if ($ok) {
                    $newgroups[] = $gid;
                }
            }
        }
        if ($this->GetGroups() != $newgroups) {
            $this->SetGroups($newgroups);
        }
        if (isset($formdata['password']) && ($formdata['password'] != '')) {
            mod_Passwords::SetPassword($this, $formdata['password']);
        }
        $admin_new = isset($formdata['admin']) && ($formdata['admin'] == 'y');
        if ($this->GetAdmin() != $admin_new) {
            $this->SetAdmin($admin_new);
        }
        if (isset($formdata['rootid']) && 
            ($formdata['rootid'] != $this->GetRootID())) {
            $this->SetRootID($formdata['rootid']);
        }
    }
    
    public function SetGrouplist($list)
    {
        $this->allgroups = $list;
    }
}

?>
