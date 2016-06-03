<?php

class ctrl_Login
{
    private $db = null;
    private $options;
    
    public function __construct(mod_Options $options)
    {
        $this->options = $options;
        $this->options->SetOption('template', 'editor');
        require_once 'template/editor/sheet.php';
    }

    private function SetupDB()
    {
        $this->db = new mod_Database($this->options);
        if (!$this->db->Connect()) {
            die('Kon geen verbinding maken.');
        }
    }
    
    private function CheckLogin($username, $password)
    {
        $this->SetupDB();
        $this->db->SetRecursive(mod_Database::recursive_no);
        $this->db->SetMode(mod_Database::mode_view);
        $this->db->SetPermitCheckLogin();
        $mod_user = $this->db->LoadUser($username, mod_Database::match_name);
        $this->db->ResetPermit();
        if ($mod_user) {
            $hash = $mod_user->GetPasshash();
            if (crypt($password, $hash) == $hash) {
                return $mod_user;
            } else {
                return null;
            }
        } else {
            return null;
        }
    }
    
    public function Login(array $parameters)
    {
        if (isset($_POST['username'])) {
            $username = $_POST['username'];
        } else {
            throw new InvalidArgumentException('Invalid username.');
        }
        if (isset($_POST['password'])) {
            $password = $_POST['password'];
        } else {
            throw new InvalidArgumentException('Invalid password.');
        }
        if (isset($_POST['redirect'])) {
            $redirect = $_POST['redirect'];
        }
        
        $user = $this->CheckLogin($username, $password);
        if (!is_null($user)) {
            if (!isset($redirect)) {
                $redirect = 'index.php?c=edit&a=page&id=' . $user->GetRootID();
            }
            session_start();
            $_SESSION['uid'] = $user->GetUID();
            $this->SetupDB();
            $this->db->RegisterEvent('login', true, $user, null, 'IP: ' .
              $_SERVER['REMOTE_ADDR']);
            header('Location: ' . $redirect);
            echo "Success";
        } else {
            $this->db->RegisterEvent('login', false, null, null, 'IP: ' .
              $_SERVER['REMOTE_ADDR']);
            echo "Invalid username or password.";
        }
    }
    
    public function Logout(array $parameters)
    {
        if (!isset($_COOKIE[session_name()])) {
            die('Session nonexistent.');
        }
        session_start();
        session_destroy();
        session_write_close();
        $params = session_get_cookie_params();
        setcookie(session_name(), '', 0, $params['path'], $params['domain'],
            $params['secure'], isset($params['httponly']));
        echo "Success!";
    }
}

?>
