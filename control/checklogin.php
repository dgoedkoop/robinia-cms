<?php

class ctrl_CheckLogin
{
    private $db = null;
    private $currentuser = null;
    
    public function SetDb($db)
    {
        $this->db = $db;
    }
    
    public function GetCurrentUser()
    {
        return $this->currentuser;
    }
    
    public function CheckLogin()
    {
        if (isset($_COOKIE[session_name()])) {
            session_start();
            if (isset($_SESSION['uid'])) {
                $uid = $_SESSION['uid'];
                $this->db->SetRecursive(mod_Database::recursive_no);
                $this->db->SetMode(mod_Database::mode_view);
                $this->db->SetPermitCheckLogin();
                $mod_user = $this->db->LoadUser($uid, mod_Database::match_id);
                $this->db->ResetPermit();
                if ($mod_user) {
                    $this->currentuser = $mod_user;
                    return true;
                } else {
                    // User not found
                    return false;
                }
            } else {
                // Session has no uid variable
                return false;
            }
        } else {
            // No session cookie found.
            return false;
        }
    }
}

?>
