<?php

class tpl_Image extends mod_Image implements tpl_ElementInterface
{
    private $displaywidth = false;
    private $displayheight = false;
    
    public function SetDisplayWidth($width) {
        $this->displaywidth = $width;
    }
    public function SetDisplayHeight($height) {
        $this->displayheight = $height;
    }
    
    public function GetOutput()
    {
	$output = '<p class="imgself">';
	$link = $this->displaywidth || $this->displayheight;
	if ($link) {
	    $param = "";
	    if ($this->displaywidth) $param = "w=".$this->displaywidth;
	    if ($this->displaywidth && $this->displayheight) $param .= "&amp;";
	    if ($this->displayheight) $param .= "h=".$this->displayheight;
	    $output .= '<a href="'.$this->filename.'" rel="lightbox"';
            if ($this->options->GetOption('img_lightbox')) {
                $output .= ' title="' . htmlspecialchars($this->caption) . '"';
            }
            $output .= '>'
	    . '<img src="image.php?id='.$this->filename.'&amp;'.$param.'"';
	} else
	    $output .= '<img src="'.$this->filename.'"';
        $output .= ' class="withborder" style="width: 100%"';
	if ($this->alttext) {
	    $output .= ' alt="'.$this->alttext.'"';
        }
	if ($link) {
            $output .= '></a>';
        } else {
            $output .= '>';
        }
	if ($this->caption) {
            $output .= '</p><p class="imgcaption">' . $this->caption;
        }
        $output .= '</p>';
	return $output;
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
