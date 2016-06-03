<?php

class tpl_DynamicLink
{
    private $parameters = array();
    private $url = "";
    private $caption = "";
    private $class = "";
    private $id = "";
    
    public function AddParameter($key, $value)
    {
        $this->parameters[$key] = $value;
    }
    public function SetURL($url)
    {
        $this->url = $url;
    }
    public function SetCaption($caption)
    {
        $this->caption = $caption;
    }
    public function SetClass($class)
    {
        $this->class = $class;
    }
    public function SetID($id)
    {
        $this->id = $id;
    }
    public function GetLink()
    {
        $output = '<a ';
        if ($this->class) {
            $output .= 'class="' . urlencode($this->class) . '" ';
        }
        if ($this->id) {
            $output .= 'id="' . urlencode($this->id) . '" ';
        }
        $output .= 'href="' . urlencode($this->url);
        if ($this->parameters != array()) {
            $output .= '?';
            $first = true;
            foreach ($this->parameters as $key => $value) {
                if ($first) {
                    $first = false;
                } else {
                    $output .= '&amp;';
                }
                $output .= urlencode($key) . '=' . urlencode($value);
            }
        }
        $output .= '">' . $this->caption . '</a>';
        return $output;
    }
}

?>
