<?php

class mod_Passwords
{
    public function CheckPassword(mod_User $user, $password)
    {
        return password_verify($password, $user->GetPasshash());
    }
    
    public function SetPassword(mod_User $user, $password)
    {
        $user->SetPasshash(password_hash($password, PASSWORD_DEFAULT));
    }
}

?>
