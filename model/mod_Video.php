<?php

class mod_Video extends mod_Element implements mod_ImageInterface {
    const tbl_video_urllength = 500;
    const tbl_video_captionsize = 2000;
    const video_youtube_width = 640;
    const video_youtube_height = 390;
    const video_youtube_preview_width = 480;
    const video_youtube_preview_height = 360;
    protected $url = "";
    protected $caption = "";
    
    public function GetURL() {
	return $this->url;
    }
    public function SetURL($url) {
	$this->url = $url;
    }
    public function GetCaption() {
	return $this->caption;
    }
    public function SetCaption($caption) {
	$this->caption = $caption;
    }
    public function GetWidth() {
        if ($this->options->GetOption('img_lightbox')) {
            return self::video_youtube_preview_width;
        } else {
            return self::video_youtube_width;
        }
    }
    public function GetHeight() {
        if ($this->options->GetOption('img_lightbox')) {
            return self::video_youtube_preview_height;
        } else {
            return self::video_youtube_height;
        }
    }
    public static function GetName()
    {
        return 'video';
    }
    public static function GetDbTableName()
    {
        return 'video';
    }
    public function GetDbColumnNames()
    {
        return array('url', 'caption');
    }
    public function AddFromDb(array $row)
    {
        $this->SetURL($row['url']);
        $this->SetCaption($row['caption']);
    }
    public function GetForDb()
    {
        return array(array('url' => $this->GetURL(),
                           'caption' => $this->GetCaption()));
    }
    public function GetDbTableDefinition()
    {
        return 'url VARCHAR(' . self::tbl_video_urllength . ') NOT NULL, '
             . 'caption VARCHAR(' . self::tbl_video_captionsize . ') NOT NULL';
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
