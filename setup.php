<?php

require 'model/database.php';
require 'control/options.php';
require 'control/autoload.php';
require 'model/passwords.php';

function SavePages(mod_Database $db, mod_Element $mod_element, $parent_id = 0, $after = 0) {
    $db->AddElement($mod_element, $parent_id, $after);
    $prev = false;
    foreach($mod_element->GetChildren() as $child) {
        if ($prev === false) {
            $after = 0;
        } else {
            $after = $prev->GetID();
        }
        if (!SavePages($db, $child, $mod_element->GetID(), $after)) {
            return false;
        }
        $prev = $child;
    }
    return true;
}

/*
 * Create the database
 */
$x = new mod_Database($options);
if ($x->Connect()) {
    if ($x->CreateTables()) {
	echo "Setup succeeded creating tables.\n";
    } else {
	echo "Setup failed: Error creating tables.\n";
    }
} else {
    echo "Setup failed: Error connecting to database.\n";
}

/*
 * Fill the database with minimal contents:
 * 1) The tree root
 * 2) An administrator user
 * 3) A world-readable login page so that the administrator can actually login.
 */
$x->SetPermitSetup();

// Create these first, so we can use them in permissions.
$admin = new mod_User($options); 
$admin->SetUID(1);
$admin->SetAdmin(true);
$admin->SetUsername('admin');
mod_Passwords::SetPassword($admin, 'admin');
$world = new mod_Usergroup($options);
$world->SetGID(0);
$world->SetGroupname('Everyone');
$loggedin = new mod_Usergroup($options);
$loggedin->SetGID(1);
$loggedin->SetGroupname('Not logged in');
$notloggedin = new mod_Usergroup($options);
$notloggedin->SetGID(2);
$notloggedin->SetGroupname('Logged in');

$root = new mod_Folder($options);
$rootpermissions = new mod_Permissions();
$rootpermissions->SetUserPermission($admin->GetUID(),
    mod_Permissions::perm_default);
$root->SetPermissions($rootpermissions);

$rootdsc = new mod_TitleDescription($options); $root->AddChild($rootdsc);
$rootdsc->SetPermissions($rootpermissions);
$rootdsc->SetTitle('Root');
$rootdsc->SetDescription('This is the CMS root folder. Please do not remove '
                       . 'this folder because you cannot recreate it.');

$loginpagepermissions = clone $rootpermissions;
$loginpagepermissions->SetGroupPermission($world->GetGID(), 
    mod_Permissions::perm_view);

$readonlypermissions = new mod_Permissions();
$readonlypermissions->SetGroupPermission($world->GetGID(), 
    mod_Permissions::perm_view);

$uf = new mod_Folder($options); $root->AddChild($uf);
$uf->SetPermissions($readonlypermissions);
$ufdsc = new mod_TitleDescription($options); $uf->AddChild($ufdsc);
$ufdsc->SetPermissions($readonlypermissions);
$ufdsc->SetTitle('System users and groups');
$ufdsc->SetDescription('This folder contains the admin user and system groups. '
                     . 'Please do not remove any of the system groups because '
                     . 'they cannot be recreated using the backend.');

$uf->AddChild($admin);
$admin->SetPermissions($loginpagepermissions);
$uf->AddChild($world);
$world->SetPermissions($readonlypermissions);
$uf->AddChild($loggedin);
$loggedin->SetPermissions($readonlypermissions);
$uf->AddChild($notloggedin);
$notloggedin->SetPermissions($readonlypermissions);

$loginpage = new mod_Page($options); $root->AddChild($loginpage);
$loginpage->SetPermissions($loginpagepermissions);

$loginpagedsc = new mod_TitleDescription($options);
$loginpage->AddChild($loginpagedsc);
$loginpagedsc->SetPermissions($loginpagepermissions);
$loginpagedsc->SetTitle('Login');
$loginpagedsc->SetDescription('This page can be used for logging in.');
$loginpagedsc->SetLinkname('login');

$loginparagraph = new mod_Paragraph($options);
$loginpage->AddChild($loginparagraph);
$loginparagraph->SetPermissions($loginpagepermissions);
$loginparagraph->SetText(<<<EOT
<div class=invoer><form method=post action="index.php?c=login&amp;a=login">
<fieldset><legend>Login</legend>
<label>Username:</label><input type=text name=username>
<label>Password:</label><input type=password name=password>
<input type=submit>
</fieldset></form></div>
EOT
    );
        
$x->SetMode(mod_Database::mode_edit);
        
if (SavePages($x, $root)) {
    if ($x->Commit($root)) {
        $loginpageid = $loginpage->GetID();
        echo "Successfully inserted test data.\n";
        echo "Login page has ID {$loginpageid}.\n";
    } else {
        echo "Error committing.\n";
    } 
} else {
    echo "Error saving page.\n";
}

?>
