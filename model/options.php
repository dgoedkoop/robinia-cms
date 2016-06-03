<?php

class mod_Options {
    private $options = array();
    
    public function SetOption($name, $value)
    {
        $this->options[$name] = $value;
    }
    public function SetOptions(array $options)
    {
        $this->options = array_merge($this->options, $options);
    }
    public function GetOption($name)
    {
        return $this->options[$name];
    }
}

?>
