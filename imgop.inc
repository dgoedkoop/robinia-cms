<?php

class ImgImg
{
    private $data = false;
    public $format = "";
    
    public function DoInput($filename)
    {
        if (!file_exists($filename)) {
            return false;
        }
        $this->format = exif_imagetype($filename);
        switch ($this->format) {
            case IMAGETYPE_PNG:
                $this->data = @imagecreatefrompng($filename);
                if (!$this->data) {
                    return false;
                }
                break;
            case IMAGETYPE_JPEG:
                $this->data = @imagecreatefromjpeg($filename);
                if (!$this->data) {
                    return false;
                }
                break;
            case IMAGETYPE_GIF:
                $this->data = @imagecreatefromgif($filename);
                if (!$this->data) {
                    return false;
                }
                break;
            default: 
                $this->format = ""; 
                return false;
        }
        return true;
    }

    public function DoOutput($filename)
    {
        if (!$this->data) {
            return false;
        }
        if (!$this->format) {
            return false;
        }
        switch ($this->format) {
            case IMAGETYPE_JPEG:
                imagejpeg($this->data, $filename);
                break;
            case IMAGETYPE_GIF:
                imagegif($this->data, $filename);
                break;
            case IMAGETYPE_PNG:
                imagepng($this->data, $filename);
                break;
            default: return false;
        }
        return true;
    }

    public function Resize($newwidth, $newheight)
    {
        if (!$this->data) {
            return false;
        }
        $tmpimg = imagecreatetruecolor ($newwidth, $newheight);
        imagecopyresampled ($tmpimg, $this->data, 0, 0, 0, 0, $newwidth, 
            $newheight, imagesx($this->data), imagesy($this->data));
        imagedestroy($this->data);
        $this->data = $tmpimg;
        return true;
    }

    public function ResizeToHeight($newheight)
    {
        return $this->Resize(imagesx($this->data)/imagesy($this->data)*$newheight,
            $newheight);
    }

    public function ResizeToWidth($newwidth)
    {
        return $this->Resize ($newwidth, imagesy($this->data)/imagesx($this->data)*$newwidth);
    }

    function __destruct()
    {
        if ($this->data != "") {
            imagedestroy($this->data);
        }
    }
}


?>