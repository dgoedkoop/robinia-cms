<?php

class tpl_ImageGallery extends mod_ImageGallery implements tpl_ElementInterface
{
    const img_perline = 2;
    const page_width = 680;
    const img_bordersize = 0;
    /* 
     * mod_ImageGallery limits children to mod_Image and descendants, so here
     * it isn't necessary to handle anything else.
     */
    public function GetOutput()
    {
        $buffer = $this->children;
	$output = '';
        while (count($buffer) > 0) {
            /*
             * Strategy A: maybe everything fits.
             */
            $totalwidth = 0;
            foreach($buffer as $img) {
                if ($img instanceof mod_ImageInterface) {
                    $totalwidth += $img->GetWidth() + self::img_bordersize * 2;
                }
            }
            if ($totalwidth <= self::page_width) {
                $width = $img->GetWidth();
                if ($width == 666) {
                    $width = 680;
                }
                if (count($buffer) == 1) {
                    $box_start = '<div style="display: inline-block; width: '
                               . round($width / self::page_width * 100, 2)
                               . '%">';
                    $box_end = '</div>';
                } else {
                    $box_start = '<div style="float: left; width: '
                               . round($width / self::page_width * 100, 2)
                               . '%">';
                    $box_end = '</div>';
                }
                $output .= "<div class=albumregel>";
                foreach($buffer as $img) {
                    $output .= $box_start . $img->GetOutput() . $box_end;
                }
        	$output .= "</div>\n";
                $buffer = array();
            } else {
                /*
                 * Strategy B: resize images appropriately.
                 */
                $count = count($buffer);
                $handlingcount = min($count, self::img_perline);
                $handling = array();
                $newbuffer = array();
                $bhsum = 0;
                foreach($buffer as $img) {
                    if (count($handling) < $handlingcount) {
                        $handling[] = $img;
                        $bhsum += $img->GetWidth() / $img->GetHeight();
                    } else {
                        $newbuffer[] = $img;
                    }
                }
                $buffer = $newbuffer;
                $pagewidth = self::page_width
                           - self::img_bordersize * 2 * $handlingcount;
                $height = false;
                $output .= "<div class=albumregel>";
                foreach($handling as $img) {
                    $factor = $pagewidth / ($img->GetHeight() * $bhsum);
                    if ($height === false) {
                        $height = round($img->GetHeight() * $factor);
                    }
                    $width = round($img->GetWidth() * $factor);
                    $img->SetDisplayWidth($width);
                    $img->SetDisplayHeight($height);
                    if (count($handling) == 1) {
                        $box_start = '<div style="float: left; width: 100%">';
                        $box_end = '</div>';
                    } else {
                        $box_start = '<div style="float: left; width: '
                                   . round($width / $pagewidth * 100, 2)
                                   . '%">';
                        $box_end = '</div>';
                    }
                    $output .= $box_start . $img->GetOutput() . $box_end;
                }
        	$output .= "</div>\n";
            }
        }
	return $output;
    }
    public function SetFromModel(mod_Element $mod_element)
    {
        if (!($mod_element instanceof mod_ImageGallery)) {
            return false;
        }
        return true;
    }
}

?>
