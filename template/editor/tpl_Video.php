<?php

class tpl_Video extends mod_Video implements tpl_ElementInterface
{
    public function GetContents()
    {
        parse_str(parse_url($this->GetURL(), PHP_URL_QUERY), $urlparams);
        if (isset($urlparams['v'])) {
            $video_id = $urlparams['v'];
        } else {
            $video_id = '';
        }

        $output = '<p>'.tr('url').': ' . $this->GetURL() . '</p>';
        if ($video_id != '') {
            $output .= '<p><img src="http://img.youtube.com/vi/'
                     . $video_id . '/0.jpg" alttext="Video"></p>';
        }
        if ($this->caption) {
            $output .= '<p><i>' . $this->caption . '</i></p>';
        }
        return $output;
    }
    public function GetForm()
    {
        $output = '<label for="url">'.tr('url').':</label>'
                 . '<input type="text" name="url" size="70" value="'
                 . htmlspecialchars($this->url) . "\">\n"
                 . '<label for="caption">'.tr('imgcaption').':</label>'
                 . '<input type="text" name="caption" size="70" value="'
                 . htmlspecialchars($this->caption) . "\">\n";
        return $output;
    }
    public function SetFromForm(array $formdata)
    {
        if (isset($formdata['url'])) {
            $this->SetURL($formdata['url']);
        }
        if (isset($formdata['caption'])) {
            $this->SetCaption($formdata['caption']);
        }
    }
    public static function TypeName()
    {
        return tr('typevideo');
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