<?php

require_once 'singleton.php';

class mod_ClientStorage extends Singleton {
    private $started = false;
    
    public function SetOption($name, $value)
    {
        if (!$this->started) {
            session_start();
            $this->started = true;
        }
        $_SESSION[$name] = $value;
    }
    public function GetOption($name)
    {
        if (!$this->started) {
            session_start();
            $this->started = true;
        }
        if (isset($_SESSION[$name]))
        {
            return $_SESSION[$name];
        } else {
            return false;
        }
    }
}

?>