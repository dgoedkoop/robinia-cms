<?php

class tpl_Video extends mod_Video implements tpl_ElementInterface
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
        if (mod_ClientStorage::instance()->GetOption('GoogleCookies'))
        {
	        $output = '<p class="imgself">';
            if ($this->displaywidth && $this->displayheight) {
                $width = $this->displaywidth;
                $height = $this->displayheight;
            } else {
                $width = $this->GetWidth();
                $height = $this->GetHeight();
            }
            
            parse_str(parse_url($this->GetURL(), PHP_URL_QUERY), $urlparams);
            if (isset($urlparams['v'])) {
                $video_id = $urlparams['v'];
            } else {
                $video_id = '';
            }
            
            if ($this->options->GetOption('img_lightbox')) {
                $output .= '<a href="' . $this->url . '" rel="lightbox">'
                        . '<img src="template/'.$this->options->GetOption('template').'/play.png" class="playbtn">'
                        . '<img src="http://img.youtube.com/vi/' . $video_id
    //                     . '/0.jpg" alttext="Video" width="' . $width
    //                     . '" height="' . $height . '" class="withborder">'
                        . '/0.jpg" alttext="Video" style="width: 100.2%"'
                        . ' class="withborder">'
                        . '</a>';
            } else {
                $output .= '<iframe id="ytplayer" type="text/html" width="'
                        . $width . '" height="' . $height. '" '
                        . 'src="http://www.youtube.com/embed/'
                        . $video_id . '?rel=0&origin='
                        . $this->options->GetOption('basepath');
                if ($this->caption) {
                    $output .= '&showinfo=0';
                }
                $output .= '" frameborder="0"></iframe>';
            }
        } else {
            $output = '<p class="imgself"><span class="imgnocookies" style="'
                    . 'width: '.$this->displaywidth.'px; '
                    . 'height: '.$this->displayheight.'px;"><span class="imgnocookiestext">'
                    . 'The video cannot be displayed because cookies are not accepted.'
                    . '</span></span>';
        }
        if ($this->caption) {
            $output .= '</p><p class="imgcaption">' . $this->caption;
        }
        $output .= '</p>';
	return $output;
    }
    public function SetFromModel(mod_Element $mod_element)
    {
        if (!($mod_element instanceof mod_Video)) {
            return false;
        }
	$this->SetURL($mod_element->GetURL());
	$this->SetCaption($mod_element->GetCaption());
        return true;
    }
}

?>
