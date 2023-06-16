<?php

class MissingException extends Exception
{
}

spl_autoload_register(function ($classname) {
    if (substr($classname, 0, 4) == 'mod_') {
        require_once('model/' . $classname . '.php');
    } elseif (substr($classname, 0, 4) == 'tpl_') {
        require_once('template/' . mod_Options::instance()->GetOption('template') . '/'
            . $classname . '.php');
    } else {
        throw new MissingException("Unable to load $classname.");
    }
});

?>
