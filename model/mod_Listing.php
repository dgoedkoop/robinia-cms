<?php

class mod_Listing extends mod_Element {
    protected $items = array();
     
    public function GetItems() {
        return $this->items;
    }
    public function ResetItems()
    {
        $this->items = array();
    }
    public function AddItem($text) {
        $this->items[] = $text;
    }
    public function AddItems(array $items) {
        $this->items = array_merge($this->items, $items);
    }
    public static function GetName()
    {
        return 'listing';
    }
    public static function GetDbTableName()
    {
        return 'listing_item';
    }
    public function GetDbColumnNames()
    {
        return array('text');
    }
    public function AddFromDb(array $row)
    {
        $this->AddItem($row['text']);
        return true;
    }
    public function GetForDb() {
        $output = array();
        foreach ($this->GetItems() as $item) {
            $output[] = array('text' => $item);
        }
        return $output;
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
