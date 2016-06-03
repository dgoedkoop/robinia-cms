<?php

require_once 'control/options.php';

class MissingException extends Exception
{
}

function __autoload($classname) {
    if (substr($classname, 0, 4) == 'mod_') {
        require_once('model/' . $classname . '.php');
    } elseif (substr($classname, 0, 4) == 'tpl_') {
        global $options;
        require_once('template/' . $options->GetOption('template') . '/'
            . $classname . '.php');
    } else {
        throw new MissingException("Unable to load $classname.");
    }
}

?>
