<?php

class tpl_MenuItem implements tpl_ElementInterface
{
    protected $children = array();
    protected $element_id;
    
    protected $linkactive = true;
    protected $title = "";
    protected $description = "";
    protected $linkname = "";

    public function SetID($id)
    {
	    $this->element_id = $id;
    }
    public function GetID()
    {
	    return $this->element_id;
    }
    public function SetLinkActive($active)
    {
        $this->linkactive = $active;
    }
    public function GetLinkActive()
    {
        return $this->linkactive;
    }
    public function GetChildren()
    {
	    return $this->children;
    }
    public function AddChild(tpl_MenuItem $child_element)
    {
	    $this->children[] = $child_element;
    }
    public function AddChildren(array $children)
    {
	    $this->children = array_merge($this->children, $children);
    }
    public function GetTitle()
    {
        return $this->title;
    }
    public function GetDescription()
    {
        return $this->description;
    }
    public function GetLinkname()
    {
        return $this->linkname;
    }
    
    private function LinkForItem(tpl_MenuItem $item)
    {
        if (!$item->GetLinkActive()) {
            return htmlspecialchars($item->GetTitle());
        } else {
            if ($item->GetLinkname() == '') {
                $url = 'index.php?c=page&amp;a=page&amp;id='
                     . urlencode($item->GetID());
            } else {
                $url = urlencode($item->GetLinkname()) . '.html';
            }
            return '<a href="' . $url . '">'
                 . htmlspecialchars($item->GetTitle()) . '</a>';
        }
    }

    public function GetOutput()
    {
        if (count($this->children) == 0) {
            return '';
        }
        $output = '<div class="menupart"><div class="menutitel">'
                . $this->LinkForItem($this)
                . '</div><div class="menukastje">';
        foreach($this->children as $child) {
            $output .= '<div class="menuitem">' . $this->LinkForItem($child)
                     . '</div>';
        }
        $output .= '</div></div>';
        foreach($this->children as $child) {
            $output .= $child->GetOutput();
        }
        return $output;
    }
    
    public function SetFromModel(mod_Element $mod_element)
    {
        $children = $mod_element->GetChildren();
        if (count($children) == 0) {
            return false;
        }
        $child = $children[0];
        if (!method_exists($child, 'GetTitle')) {
            return false;
        }
        $this->title = $child->GetTitle();
        if (method_exists($child, 'GetDescription')) {
            $this->description = $child->GetDescription();
        }
        if (method_exists($child, 'GetLinkname')) {
            $this->linkname = $child->GetLinkname();
        }
        return true;
    }
}

?>
