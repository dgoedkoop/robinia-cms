<?php

class mod_User extends mod_Element
{
    const tbl_user_namelength = 32;
    const tbl_user_passwordlength = 255;
    const tbl_user_groupslength = 1000;
    const user_modified_none = 0;
    const user_modified_password = 1;
    const user_modified_admin = 2;
    const user_modified_other = 4;
    private $name = '';
    private $passhash = '';
    private $uid = 0;
    private $uid_set = false;
    private $groups = array();
    private $modified = self::user_modified_none;
    private $admin = false;
    private $root_id = 1;
    
    public function SetRootID($element_id)
    {
        $this->root_id = $element_id;
        $this->SetModified(self::user_modified_other);
    }
    public function GetRootID()
    {
        return $this->root_id;
    }
    public function SetAdmin($admin)
    {
        $this->admin = $admin;
        $this->SetModified(self::user_modified_admin);
    }
    public function GetAdmin()
    {
        return $this->admin;
    }
    public function ResetModified()
    {
        $this->modified = self::user_modified_none;
    }
    public function SetModified($modified)
    {
        $this->modified = $this->modified | $modified;
    }
    public function GetModified()
    {
        return $this->modified;
    }
    public function SetUsername($username)
    {
        $this->name = $username;
        $this->SetModified(self::user_modified_other);
    }
    public function GetUsername()
    {
        return $this->name;
    }
    public function SetUID($uid)
    {
        $this->uid = $uid;
        $this->uid_set = true;
        $this->SetModified(self::user_modified_other);
    }
    public function GetUID()
    {
        return $this->uid;
    }
    public function UIDIsset()
    {
        return $this->uid_set;
    }
    public function SetPasshash($hash)
    {
        $this->passhash = $hash;
        if ($this->modified == self::user_modified_none) {
            $this->modified = self::user_modified_password;
        }
    }
    public function GetPasshash()
    {
        return $this->passhash;
    }
    public function AddGroup($group_id)
    {
        $this->groups[] = $group_id;
        $this->SetModified(self::user_modified_other);
    }
    public function GetGroups()
    {
        return $this->groups;
    }
    public function SetGroups($groups)
    {
        $this->groups = $groups;
        $this->SetModified(self::user_modified_other);
    }
    
    public static function GetName()
    {
        return 'user';
    }
    public static function GetDbTableName()
    {
        return 'user';
    }
    public function GetDbColumnNames()
    {
        return array('uid', 'username', 'password', 'groups', 'admin', 
            'root_id');
    }
    public function AddFromDb(array $row)
    {
        $this->SetUID($row['uid']);
        $this->SetUsername($row['username']);
        $this->SetPasshash($row['password']);
        if ($row['groups'] != '') {
            foreach(explode(',', $row['groups']) as $group_id) {
                $this->AddGroup($group_id);
            }
        }
        $this->SetAdmin($row['admin'] == 'y');
        $this->SetRootID($row['root_id']);
        $this->ResetModified();
    }
    public function GetForDb()
    {
        $groups = implode(',', $this->groups);
        if ($this->GetAdmin()) {
            $admin = 'y';
        } else {
            $admin = 'n';
        }
        return array(array('uid' => $this->GetUID(),
                           'username' => $this->GetUsername(),
                           'password' => $this->GetPasshash(),
                           'groups' => $groups,
                           'admin' => $admin,
                           'root_id' => $this->GetRootID()));
    }
    public function GetDbTableDefinition()
    {
        return 'uid INT NOT NULL, '
             . 'username VARCHAR(' . self::tbl_user_namelength . ') NOT NULL, '
             . 'password CHAR(' . self::tbl_user_passwordlength . ') NOT NULL, '
             . 'groups VARCHAR(' . self::tbl_user_groupslength . ') NOT NULL, '
             . "admin ENUM('y', 'n') NOT NULL DEFAULT 'n', "
             . "root_id INT NOT NULL DEFAULT 1, "
             . 'KEY (uid), KEY (username)';
    }
    public function LimitParent()
    {
        return array('Folder');
    }
    public function LimitChildren()
    {
        return array();
    }
}
?>
