<?php

require_once 'control/options.php';
require_once 'control/autoload.php';
require_once 'control/page.php';
require_once 'control/edit.php';
require_once 'control/login.php';
require_once 'control/disable_quotes.php';

class Main
{
    private $controllername = "index";
    private $controller = NULL;
    private $action = "index";
    private $parameters = array();
    
    public function __construct()
    {
        $this->Initialise();
    }
    
    private function Initialise()
    {
        /*
         * Strategy: mod_rewrite enables URL's like
         * $basepath/$controller/$action/$parameter
         */
        /* $path = parse_url($_SERVER["REQUEST_URI"], PHP_URL_PATH);
        if (strpos($path, $this->basepath) === 0) {
            $path = substr($path, strlen($this->basepath));
        }
        list($controller, $action, $parameters) = explode('/', $path, 3);
        if (isset($controller)) {
            $this->controllername = ucfirst(strtolower($controller));
        }
        if (isset($action)) {
            $this->action = ucfirst(strtolower($action));
        }
        if (isset($parameters)) {
            $this->parameters = explode('/', $parameters);
        } */
        /*
         * Strategy: GET-variables 'c' and 'a' are set
         */
        if (isset($_GET['c'])) {
            $this->controllername = ucfirst(strtolower($_GET['c']));
        }
        if (isset($_GET['a'])) {
            $this->action = ucfirst(strtolower($_GET['a']));
        }
        $this->parameters = $_GET;
        unset($this->parameters['a']);
        unset($this->parameters['c']);
    }
    
    public function Setup()
    {
        $ctrlname = 'ctrl_' . $this->controllername;
        if (class_exists($ctrlname)) {
            global $options;
            $this->controller = new $ctrlname($options);
        } else {
            throw new InvalidArgumentException('Invalid controller: '
                . $this->controllername);
        }
        if (!method_exists($this->controller, $this->action)) {
            throw new InvalidArgumentException('Invalid action: ' . $this->action);
        }
    }
    
    public function Run()
    {
        call_user_func(array($this->controller, $this->action),
            $this->parameters);
    }
}

$main = new Main();
$main->Setup();
$main->Run();
?>
