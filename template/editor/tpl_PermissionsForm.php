<?php

class tpl_PermissionsForm
{
    const perm_remove = 'x';
    private $permlist = array(mod_Permissions::perm_view => 'v',
                              mod_Permissions::perm_edit => 'e',
                              mod_Permissions::perm_commit => 'c',
                              mod_Permissions::perm_addchild => 'a',
                              mod_Permissions::perm_deletechild => 'd',
                              mod_Permissions::perm_changeperm => 'p');

    private $userlist = array();
    private $grouplist = array();
    private $mod_element = null;
    
    public function SetElement(mod_Element $mod_element)
    {
        $this->mod_element = $mod_element;
    }
    public function GetElement()
    {
        return $this->mod_element;
    }
    public function SetUserlist($userlist)
    {
        $this->userlist = $userlist;
    }
    public function SetGrouplist($grouplist)
    {
        $this->grouplist = $grouplist;
    }
    
    private function PermissionLine($permission, $prefix, $subject_id)
    {
        $output = '';
        foreach ($this->permlist as $testperm => $suffix) {
            $output .= '<td><input type="checkbox" '
                     . "name=\"{$prefix}{$suffix}_"
                     . htmlspecialchars($subject_id) . '" value="y"';
            if ($permission & $testperm) {
                $output .= ' CHECKED';
            }
            $output .= '></td>';
        }
        $output .= '<td><input type="checkbox" name="' . $prefix 
                 . self::perm_remove . '_' . htmlspecialchars($subject_id)
                 . '" value="y"></td>';
        return $output;
    }
    
    public function GetForm($permissions = null)
    {
        $output = '<table class=tablewithvertical>'
                . '<tr><th></th><th class=vertical><div>Weergeven</div></th>'
                . '<th class=vertical><div>Bewerken</div></th>'
                . '<th class=vertical><div>Wijzingen doorvoeren</div></th>'
                . '<th class=vertical><div>Sub-elementen toevoegen</div></th>'
                . '<th class=vertical><div>Sub-elementen verwijderen</div></th>'
                . '<th class=vertical><div>Rechten aanpassen</div></th>'
                . '<th class=vertical><div>Dit recht verwijderen</div></th></tr>';
        $users = array();
        $groups = array();
        if (is_null($permissions)) {
            $permissions = $this->mod_element->GetPermissions();
        }
        foreach($permissions->GetRulesArray() as $rule) {
            $what = mod_Permissions::RuleWhat($rule);
            $subject_id = mod_Permissions::RuleWho($rule);
            $permission = mod_Permissions::RulePermission($rule);
            $output .= '<tr><td><label>';
            if ($what == mod_Permissions::perm_user) {
                $users[] = $subject_id;
                $prefix = 'u';
                $output .= 'Gebruiker: ';
                $found = false;
                foreach ($this->userlist as $user) {
                    if ($user->GetUID() == $subject_id) {
                        $output .= $user->GetUsername();
                        $found = true;
                    }
                }
                if (!$found) {
                    $output .= "[$subject_id]";
                }
            } elseif ($what == mod_Permissions::perm_group) {
                $groups[] = $subject_id;
                $prefix = 'g';
                $output .= 'Groep: ';
                $found = false;
                foreach ($this->grouplist as $group) {
                    if ($group->GetGID() == $subject_id) {
                        $output .= $group->GetGroupname();
                        $found = true;
                    }
                }
                if (!$found) {
                    $output .= "[$subject_id]";
                }
            } else {
                return false;
            }
            $output .= '</label></td>'
                     . $this->PermissionLine($permission, $prefix, $subject_id)
                     . "</tr>\n";
        }
        
        $output .= '</table>';
        $userstr = implode(',', $users);
        $groupstr = implode(',', $groups);
        $output .= '<input type=hidden name=users value="'
                 . htmlspecialchars($userstr) . '">'
                 . '<input type=hidden name=groups value="'
                 . htmlspecialchars($groupstr) . '">';
        $output .= '</fieldset><fieldset><legend>Nieuw recht aanmaken</legend>'
                 . '<div class=checkboxline><input type="radio" '
                 . 'name="createwhat" value="user" CHECKED>'
                 . '<label>Nieuw recht voor gebruiker</label></div>'
                 . '<label for="user">Gebruiker:</label><select name="user">'
                 . '<option value="">-</option>';
        foreach($this->userlist as $user) {
            $found = false;
            foreach($permissions->GetRulesArray() as $rule) {
                $what = mod_Permissions::RuleWhat($rule);
                $subject_id = mod_Permissions::RuleWho($rule);
                if (($what == mod_Permissions::perm_user)
                    && ($subject_id == $user->GetUID())) {
                    $found = true;
                }
            }
            if (!$found) {
                $output .= '<option value="' 
                         . htmlspecialchars($user->GetUID()) . '">' 
                         . htmlspecialchars($user->GetUsername())
                         . '</option>';
            }
        }
        $output .= '</select>'
                 . '<div class=checkboxline><input type="radio" '
                 . 'name="createwhat" value="group">'
                 . '<label>Nieuw recht voor groep</label></div>'
                 . '<label for="group">Groep:</label><select name="group">'
                 . '<option value="">-</option>';
        foreach($this->grouplist as $group) {
            $found = false;
            foreach($permissions->GetRulesArray() as $rule) {
                $what = mod_Permissions::RuleWhat($rule);
                $subject_id = mod_Permissions::RuleWho($rule);
                if (($what == mod_Permissions::perm_group)
                    && ($subject_id == $group->GetGID())) {
                    $found = true;
                }
            }
            if (!$found) {
                $output .= '<option value="' 
                         . htmlspecialchars($group->GetGID()) . '">' 
                         . htmlspecialchars($group->GetGroupname())
                         . '</option>';
            }
        }
        $output .= '</select>'
                 . '<input type="submit" name="addpermission" '
                 . 'value="Toevoegen">';
        return $output;
    }
    
    public function GetPermissionsFromForm()
    {
        /*
         * We need to clone the permissions. Otherwise, it would not be possible
         * to revoke one's own right to change permissions on a page.
         */
        $permissions = new mod_Permissions;
        
        if (!isset($_POST['users'])) {
            return false;
        }
        if (!isset($_POST['groups'])) {
            return false;
        }
        
        if ($_POST['users'] != '') {
            $users = explode(',', $_POST['users']);
        } else {
            $users = array();
        }
        if ($_POST['groups'] != '') {
            $groups = explode(',', $_POST['groups']);
        } else {
            $groups = array();
        }
        
        foreach($users as $uid) {
            $prefix = 'u';
            $boxname = $prefix . self::perm_remove . '_' . $uid;
            if (!isset($_POST[$boxname]) || ($_POST[$boxname] != 'y')) {
                $permissions->SetUserPermission($uid, 0);
                foreach ($this->permlist as $setperm => $suffix) {
                    $boxname = "{$prefix}{$suffix}_{$uid}";
                    if (isset($_POST[$boxname]) && ($_POST[$boxname] == 'y')) {
                        $permissions->SetUserPermission($uid,
                            $permissions->GetUserPermission($uid) | $setperm);
                    }
                }
            }
        }
        foreach($groups as $gid) {
            $prefix = 'g';
            $boxname = $prefix . self::perm_remove . '_' . $gid;
            if (!isset($_POST[$boxname]) || ($_POST[$boxname] != 'y')) {
                $permissions->SetGroupPermission($gid, 0);
                foreach ($this->permlist as $setperm => $suffix) {
                    $boxname = "{$prefix}{$suffix}_{$gid}";
                    if (isset($_POST[$boxname]) && ($_POST[$boxname] == 'y')) {
                        $permissions->SetGroupPermission($gid,
                            $permissions->GetGroupPermission($gid) | $setperm);
                    }
                }
            }
        }
        return $permissions;
    }
}

?>
