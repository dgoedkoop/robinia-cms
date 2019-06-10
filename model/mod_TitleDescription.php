<?php

class mod_TitleDescription extends mod_Element {
    const tbl_page_titlesize = 200;
    const tbl_page_linklength = 200;
    protected $title = "";
    protected $description = "";
    protected $linkname = "";
        
    public function GetTitle() {
        return $this->title;
    }
    public function SetTitle($title) {
        $this->title = $title;
    }
    public function GetDescription() {
        return $this->description;
    }
    public function SetDescription($description) {
        $this->description = $description;
    }
    public function GetLinkname()
    {
        return $this->linkname;
    }
    public function SetLinkname($linkname)
    {
        $this->linkname = $linkname;
    }
    public static function GetName()
    {
        return 'titledescription';
    }
    public static function GetDbTableName()
    {
        return 'title_description';
    }
    public function GetDbColumnNames()
    {
        return array('title', 'description', 'linkname');
    }
    public function AddFromDb(array $row)
    {
        $this->SetTitle($row['title']);
        $this->SetDescription($row['description']);
        $this->SetLinkname($row['linkname']);
        return true;
    }
    public function GetForDb() {
        return array(array('title' => $this->GetTitle(),
                           'description' => $this->GetDescription(),
                           'linkname' => $this->GetLinkname()));
    }
    public function GetDbTableDefinition()
    {
        return 'title VARCHAR(' . self::tbl_page_titlesize . ') NOT NULL, '
             . 'description TEXT NOT NULL, '
             . 'linkname VARCHAR(' . self::tbl_page_linklength . ') NOT NULL, '
             . 'KEY (linkname)';
    }
    public function LimitParent()
    {
        return array('Folder', 'Page');
    }
    public function LimitChildren()
    {
        return array();
    }
}

?>
