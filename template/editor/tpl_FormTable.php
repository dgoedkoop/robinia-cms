<?php

class tpl_FormTable
{
    private $hidden_elements = array();
    private $visible_elements = array();
    
    public function AddHidden($name, $value)
    {
        $this->hidden_elements[] = array($name, $value);
    }
    public function AddVisible($inputhtml)
    {
        $this->visible_elements[] = array('inputhtml' => $inputhtml);
    }
    public function AddVisibleWithLabel($label, $inputhtml)
    {
        $this->visible_elements[] = array('label' => $label,
                                          'inputhtml' => $inputhtml);
    }
    public function AddSetLabel
    
    '<input type=hidden name="' . htmlspecialchars($name)
                . '" value="' . htmlspecialchars($value) . '">'
    
    public
}
?>
