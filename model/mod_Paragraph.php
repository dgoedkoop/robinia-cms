<?php

class mod_Paragraph extends mod_Element {
    protected $text = "";

    public function GetText() {
	return $this->text;
    }
    public function SetText($text) {
	$this->text = $text;
    }
    public static function GetName()
    {
        return 'paragraph';
    }
    public static function GetDbTableName()
    {
        return 'paragraph';
    }
    public function GetDbColumnNames()
    {
        return array('text');
    }
    public function AddFromDb(array $row)
    {
        $this->SetText($row['text']);
        return true;
    }
    public function GetForDb() {
        return array(array('text' => $this->GetText()));
    }
    public function GetDbTableDefinition()
    {
        return 'text TEXT NOT NULL';
    }
    public function LimitParent()
    {
        return array('Container');
    }
    public function LimitChildren()
    {
        return array();
    }
}

?>
