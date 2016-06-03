<?php

class mod_Usergroup extends mod_Element
{
    const tbl_group_namelength = 32;
    private $name = '';
    private $gid = 0;
    private $gid_set = false;
    
    public function SetGroupname($groupname)
    {
        $this->name = $groupname;
    }
    public function GetGroupname()
    {
        return $this->name;
    }
    public function SetGID($gid)
    {
        $this->gid = $gid;
        $this->gid_set = true;
    }
    public function GetGID()
    {
        return $this->gid;
    }
    public function GIDIsset()
    {
        return $this->gid_set;
    }
    
    public static function GetName()
    {
        return 'group';
    }
    public static function GetDbTableName()
    {
        return 'group';
    }
    public function GetDbColumnNames()
    {
        return array('gid', 'groupname');
    }
    public function AddFromDb(array $row)
    {
        $this->SetGID($row['gid']);
        $this->SetGroupname($row['groupname']);
    }
    public function GetForDb()
    {
        return array(array('gid' => $this->GetGID(),
                           'groupname' => $this->GetGroupname()));
    }
    public function GetDbTableDefinition()
    {
        return 'gid INT NOT NULL, '
             . 'groupname VARCHAR(' . self::tbl_group_namelength . ') NOT NULL, '
             . 'KEY (gid)';
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
