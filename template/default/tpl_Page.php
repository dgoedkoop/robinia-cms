<?php

class tpl_Page extends mod_Page implements tpl_ElementInterface
{
    public function GetOutput()
    {
        if ($this->GetFullyLoaded()) {
            $output = "";
            foreach ($this->children as $child_element) {
                $output .= $child_element->GetOutput();
            }
            return $output;
        } else {
            $child = $this->children[0];
            if ($child instanceof tpl_TitleDescription) {
                $title = $child->GetTitle();
                $description = $child->GetDescription();
                $linkname = $child->GetLinkname();
            } else {
                $title = 'Untitled';
                $description = '';
                $linkname = '';
            }
            if ($linkname == '') {
                $url = 'index.php?c=page&amp;a=page&amp;id='
                     . urlencode($this->GetID());
            } else {
                $url = urlencode($linkname) . '.html';
            }
            $output = '<p><a href="' . $url . '"><b>' . $title . '</b></a>';
            if ($description != '') {
                $output .= ' - ' . $description;
            }
            $output .= '</p>';
            return $output;
        }
    }
    public function SetFromModel(mod_Element $mod_element)
    {
        if (!($mod_element instanceof mod_Page)) {
            return false;
        }
        return true;
    }
}

?>
