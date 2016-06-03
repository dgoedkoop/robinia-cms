<?php

class mod_Permissions
{
    /*
     * New elements inherit the group and world permissions from their parent.
     * Their owner will be the currently logged in user and the owner rights
     * will be the permissions given in perm_default.
     * 
     * The standard value is perm_default = 27 (view, edit, addchild and
     * deletechild) so that the owner of the new item can view and modify it,
     * but not publish (commit) it, or give himself the permission to do so,
     * except when the group or world permissions allow this.
     */
    const perm_default = 27;
    
    const perm_view = 1;
    const perm_edit = 2;
    const perm_commit = 4;
    const perm_addchild = 8;
    const perm_deletechild = 16;
    const perm_changeperm = 32;
    const perm_all = 63;
    const perm_user = 1;
    const perm_group = 2;
    private $permissions = array();
   
    private function SetPermission($what, $who, $permission)
    {
        if (($what != self::perm_user) && ($what != self::perm_group)) {
            return false;
        }
        $found = false;
        foreach ($this->permissions as $key => $rule) {
            if (($rule[0] == $what) && ($rule[1] == $who)) {
                $found = true;
                $this->permissions[$key][2] = $permission;
            }
        }
        if (!$found) {
            $this->permissions[] = array($what, $who, $permission);
        }
        return true;
    }
    private function GetPermission($what, $who)
    {
        foreach ($this->permissions as $rule) {
            if (($rule[0] == $what) && ($rule[1] == $who)) {
                return $rule[2];
            }
        }
        return 0;
    }
    public function SetUserPermission($uid, $permission)
    {
        $this->SetPermission(self::perm_user, $uid, $permission);
    }
    public function SetGroupPermission($gid, $permission)
    {
        $this->SetPermission(self::perm_group, $gid, $permission);
    }
    public function GetUserPermission($uid)
    {
        return $this->GetPermission(self::perm_user, $uid);
    }
    public function GetGroupPermission($gid)
    {
        return $this->GetPermission(self::perm_group, $gid);
    }
    public function GetRulesArray()
    {
        return $this->permissions;
    }
    public function RuleWhat($rule)
    {
        return $rule[0];
    }
    public function RuleWho($rule)
    {
        return $rule[1];
    }
    public function RulePermission($rule)
    {
        return $rule[2];
    }
    
    private function SimpleCheck($user, $permission)
    {
        foreach($this->permissions as $rule) {
            if (($rule[2] & $permission) > 0) {
                if (($rule[0] == self::perm_user) && !is_null($user) &&
                    ($rule[1] == $user->GetUID())) {
                    return true;
                } elseif ($rule[0] == self::perm_group) {
                    if (($rule[1] == 0) ||
                        (($rule[1] == 1) && is_null($user)) ||
                        (($rule[1] == 2) && !is_null($user))) {
                        return true;
                    } elseif (!is_null($user)) {
                        foreach($user->GetGroups() as $gid) {
                            if ($rule[1] == $gid) {
                                return true;
                            }
                        }
                    }
                }
            }
        }
        return false;
    }
    
    public function HasPermission($user, $permission)
    {
        if (!is_null($user) && !($user instanceof mod_User)) {
            return false;
        } else {
            if (($permission == mod_Permissions::perm_changeperm) &&
                !is_null($user) && $user->GetAdmin()) {
                return true;
            } else {
                return $this->SimpleCheck($user, $permission);
            }
        }
    }
}
?>
