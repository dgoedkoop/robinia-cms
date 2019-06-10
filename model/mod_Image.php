<?php

class mod_Image extends mod_Element implements mod_ImageInterface {
    const tbl_image_filenamelength = 255;
    const tbl_image_alttextsize = 2000;
    const tbl_image_captionsize = 2000;
    protected $filename = "";
    protected $alttext = "";
    protected $caption = "";
    protected $width = 0;
    protected $height = 0;
    
    public function GetFilename() {
    return $this->filename;
    }
    public function SetFilename($filename) {
        $this->filename = $filename;
    }
    public function GetAltText() {
        return $this->alttext;
    }
    public function SetAltText($alttext) {
        $this->alttext = $alttext;
    }
    public function GetCaption() {
        return $this->caption;
    }
    public function SetCaption($caption) {
        $this->caption = $caption;
    }
    public function GetWidth() {
        return $this->width;
    }
    public function SetWidth($width) {
        $this->width = $width;
    }
    public function GetHeight() {
        return $this->height;
    }
    public function SetHeight($height) {
        $this->height = $height;
    }
    public static function GetName()
    {
        return 'image';
    }
    public static function GetDbTableName()
    {
        return 'image';
    }
    public function GetDbColumnNames()
    {
        return array('filename', 'alttext', 'caption', 'width', 'height');
    }
    public function AddFromDb(array $row)
    {
        $this->SetFilename($row['filename']);
        $this->SetAltText($row['alttext']);
        $this->SetCaption($row['caption']);
        $this->SetWidth($row['width']);
        $this->SetHeight($row['height']);
    }
    public function GetForDb()
    {
        return array(array('filename' => $this->GetFilename(),
                           'alttext' => $this->GetAltText(),
                           'caption' => $this->GetCaption(),
                           'width' => $this->GetWidth(),
                           'height' => $this->GetHeight()));
    }
    public function GetDbTableDefinition()
    {
        return 'filename VARCHAR(' . self::tbl_image_filenamelength 
             . ') NOT NULL, '
             . 'alttext VARCHAR(' . self::tbl_image_alttextsize . ') NOT NULL, '
             . 'caption VARCHAR(' . self::tbl_image_captionsize . ') NOT NULL, '
             . 'width SMALLINT NOT NULL, '
             . 'height SMALLINT NOT NULL';
    }
    public function LimitParent()
    {
        return array('ImageGallery');
    }
    public function LimitChildren()
    {
        return array();
    }
}
?>
