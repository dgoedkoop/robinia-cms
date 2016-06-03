<?php

class mod_PermissionsFactory
{
    public function PermissionsForChild(mod_Element $parent, $user = null)
    {
        $new_permissions = clone $parent->GetPermissions();
        if (!is_null($user) && ($user instanceof mod_User)) {
            if ($new_permissions->GetUserPermission($user->GetUID()) == 0) {
                $new_permissions->SetUserPermission($user->GetUID(),
                    mod_Permissions::perm_default);
            }
        }
        return $new_permissions;
    }
}
?>
