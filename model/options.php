<?php

require_once 'singleton.php';

class mod_Options extends Singleton {
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
