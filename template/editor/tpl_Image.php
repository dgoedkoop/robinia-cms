<?php

class tpl_Image extends mod_Image implements tpl_ElementInterface
{
    private function DestFilename($origname)
    {
        $uploaddir = mod_Options::instance()->GetOption('img_upload_dir');
        if ($origname == '') {
            return false;
        } elseif (!is_dir($uploaddir)) {
            if (!file_exists($uploaddir)) {
                mkdir($uploaddir, /*umask()*/ 0700, true);
            } else {
                return false;
            }
        } // no else, because then all is ok!
        if (substr($uploaddir, strlen($uploaddir)-1) != '/') {
            $uploaddir .= '/';
        }
        $path_parts = pathinfo($origname);
        $firsttry = $uploaddir . $path_parts['basename'];
        if (!file_exists($firsttry)) {
            return $firsttry;
        }
        $i = 0;
        $notfound = false;
        while (!$notfound) {
            $i++;
            $try = $uploaddir . $path_parts['filename'] . '_' . $i;
            if (isset($path_parts['extension'])) {
                $try .= '.' . $path_parts['extension'];
            }
            if (!file_exists($try)) {
                $notfound = true;
            }
        }
        return $try;
    }
    private function GetImageSize($filename)
    {
        if (file_exists($filename)) {
            $format = exif_imagetype($filename);
            switch ($format) {
                case IMAGETYPE_PNG:
                    $data = @imagecreatefrompng($filename);
                    break;
                case IMAGETYPE_JPEG:
                    $data = @imagecreatefromjpeg($filename);
                    break;
                case IMAGETYPE_GIF:
                    $data = @imagecreatefromgif($filename);
                    break;
                default: 
                    $data = false; 
            }
            if (!($data === false)) {
                $width = imagesx($data);
                $height = imagesy($data);
                return array('width' => $width, 'height' => $height);
            } else {
                return array();
            }
        }
    }
    
    public function GetContents()
    {
        $output = '<p>';
        if (($this->width > 500) || ($this->height > 500)) {
            if ($this->width >= $this->height) {
                $output .= '<img src="image.php?id='.$this->filename.'&amp;w=500"';
            } else {
                $output .= '<img src="image.php?id='.$this->filename.'&amp;h=500"';
            }
        } else {
            $output .= '<img src="'.$this->filename.'"';
        }
        if ($this->alttext)
        $output .= ' alt="'.$this->alttext.'"';
        $output .= '>';
        if ($this->caption) {
            $output .= '<br><i>'.$this->caption.'</i>';
        }
        $output .= '</p>';
        return $output;
    }
    public function GetFormHasFileUpload()
    {
        return true;
    }
    public function GetForm()
    {
        if ($this->filename != '') {
            $output = '<p>Bestandsnaam: '
                    . htmlspecialchars($this->filename) . '</p>'
                    . '<p>Formaat: ' . $this->width . ' x ' . $this->height
                    . ' pixels</p>'
                    . '<label for="imgupload">Afbeelding wijzigen:</label>';
        } else{
            $output = '<label for="imgupload">Afbeelding uploaden:</label>';
        }
        $output .= '<input type="file" name="imgupload">' . "\n"
                 . '<label for="alttext">Alt-text:</label>'
                 . '<input type="text" name="alttext" value="'
                 . htmlspecialchars($this->alttext) . "\">\n"
                 . '<label for="caption">Onderschrift:</label>'
                 . '<input type="text" name="caption" size="70" value="'
                 . htmlspecialchars($this->caption) . "\">\n";
        return $output;
    }
    public function SetFromForm(array $formdata)
    {
        if (isset($formdata['alttext'])) {
            $this->SetAltText($formdata['alttext']);
        }
        if (isset($formdata['caption'])) {
            $this->SetCaption($formdata['caption']);
        }
        if (isset($_FILES['imgupload'])) {
            $destfilename = $this->DestFilename($_FILES['imgupload']['name']);
            if (!($destfilename === false) && move_uploaded_file(
                $_FILES['imgupload']['tmp_name'], $destfilename)) {
                $this->SetFilename(mod_Options::instance()->GetOption(img_upload_webpath)
                    . basename($destfilename));
                $size = $this->GetImageSize($destfilename);
                if (isset($size['width'])) {
                    $this->SetWidth($size['width']);
                }
                if (isset($size['height'])) {
                    $this->SetHeight($size['height']);
                }
            }
        }
    }
    public static function TypeName()
    {
        return 'Afbeelding';
    }
    public function SetFromModel(mod_Element $mod_element)
    {
        if (!($mod_element instanceof mod_Image)) {
            return false;
        }
    $this->SetFilename($mod_element->GetFilename());
    $this->SetAltText($mod_element->GetAltText());
    $this->SetCaption($mod_element->GetCaption());
    $this->SetWidth($mod_element->GetWidth());
    $this->SetHeight($mod_element->GetHeight());
        return true;
    }
}

?>