<?php

require_once 'model/database.php';
require_once 'checklogin.php';

class ctrl_Edit
{
    const file_upload_max_size = 2097152;
    private $db = null;
    private $grouplist = false;
    private $checklogin;
    private $currentuser = null;
    private $clipboardelement = null;
    private $clipboardcut = true;
    private $parenttree = array();
    
    public function __construct()
    {
        mod_Options::instance()->SetOption('template', 'editor');
        require_once 'template/editor/sheet.php';
    }
    
    public function GetGrouplist()
    {
        if (is_null($this->db)) {
            $this->SetupDB();
        }
        if ($this->grouplist === false) {
            $oldmode = $this->db->GetMode();
            $this->db->SetMode(mod_Database::mode_view);
            $this->grouplist = $this->db->LoadTypeAll('Usergroup');
            $this->db->SetMode($oldmode);
        }
        return $this->grouplist;
    }
    
    private function SetupDB()
    {
        $this->db = new mod_Database();
        if (!$this->db->Connect()) {
            die('Kon geen verbinding maken.');
        }
        $this->checklogin = new ctrl_CheckLogin();
        $this->checklogin->SetDb($this->db);
        $this->checklogin->CheckLogin();
        $this->currentuser = $this->checklogin->GetCurrentUser();
        if (!is_null($this->currentuser)) {
            $this->db->SetCurrentUser($this->currentuser);
        }
    }
    
    private function PgStart($title)
    {
        header('Content-Type: text/html; charset=utf-8');
        echo '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" '
           . "\"http://www.w3.org/TR/html4/strict.dtd\">\n"
           . "<HTML>\n"
           . "<HEAD>\n"
           . "<TITLE>$title</TITLE>\n"
           . '<LINK rel="stylesheet" type="text/css" href="template/editor/main.css">'
           . '<META http-equiv="Content-type" content="text/html; '
           . 'charset=utf-8">'
           . "\n</HEAD>\n"
           . "<BODY>\n"
           . "<div class=page>\n"
           . "<div class=pageheadingleft><div class=pageheadingheading>\n"
           . "<H1>&nbsp;</H1>\n"
           . "</div></div>\n"
           . "<div class=pageheadingright><div class=pageheadingheading>\n"
           . "<h1>$title</h1>\n"
           . "</div></div>\n"
           . "<div class=pagemain>";
    }
    private function PgEnd()
    {
        echo "</div></div></BODY>\n"
           . "</HTML>";

    }
    
    public function Commit(array $parameters)
    {
        if (isset($parameters['id'])) {
            $element_id = $parameters['id'];
        } else {
            throw new InvalidArgumentException('Invalid element ID.');
        }
        $this->SetupDB();
        $this->db->SetMode(mod_Database::mode_edit);
        $this->db->SetRecursive(mod_Database::recursive_no);
        $mod_element = $this->db->LoadElement($element_id);
        $code = $this->db->Commit($mod_element);
        if ($code & mod_Database::commit_error) {
            echo 'Error committing';
        } else {
            header('Location: index.php?c=edit&a=page&id=' . $parameters['id']);
            echo 'Success';
        }
    }
    public function Rollback(array $parameters)
    {
        if (isset($parameters['id'])) {
            $element_id = $parameters['id'];
        } else {
            throw new InvalidArgumentException('Invalid element ID.');
        }
        $this->SetupDB();
        $this->db->SetMode(mod_Database::mode_edit);
        $this->db->SetRecursive(mod_Database::recursive_no);
        $mod_element = $this->db->LoadElement($element_id);
        $this->db->SetRecursive(mod_Database::recursive_yes);
        $this->db->Rollback($mod_element) || die('Er ging iets mis.');
        header('Location: index.php?c=edit&a=page&id=' . $parameters['id']);
        echo 'Success';
    }
    public function Delete(array $parameters)
    {
        if (isset($parameters['id'])) {
            $element_id = $parameters['id'];
        } else {
            throw new InvalidArgumentException('Invalid element ID.');
        }
        $this->SetupDB();
        $this->db->SetMode(mod_Database::mode_edit);

        $this->db->SetRecursive(mod_Database::recursive_no);
        $mod_element = $this->db->LoadElement($element_id);
        $this->db->Deactivate($mod_element);
        if (isset($parameters['root_id'])) {
            header('Location: index.php?c=edit&a=page&id=' . $parameters['root_id']);
        }
        echo 'Success';
    }
    public function Reactivate(array $parameters)
    {
        if (isset($parameters['id'])) {
            $element_id = $parameters['id'];
        } else {
            throw new InvalidArgumentException('Invalid element ID.');
        }
        $this->SetupDB();
        $this->db->SetMode(mod_Database::mode_edit);

        $this->db->SetRecursive(mod_Database::recursive_no);
        $mod_element = $this->db->LoadElement($element_id);
        if ($mod_element->GetStatus() != 'd') {
            throw new InvalidArgumentException('Can only reactivate elements where deletion is pending.');
        }
        $this->db->Rollback($mod_element);
        if (isset($parameters['root_id'])) {
            header('Location: index.php?c=edit&a=page&id=' . $parameters['root_id']);
        }
        echo 'Success';
    }
    public function Add(array $parameters)
    {
        if (isset($parameters['parent_id'])) {
            $parent_id = $parameters['parent_id'];
        } else {
            throw new InvalidArgumentException('Invalid parent ID.');
        }
        if (isset($parameters['root_id'])) {
            $root_id = $parameters['root_id'];
        } else {
            throw new InvalidArgumentException('Invalid root ID.');
        }
        if (isset($parameters['after_element'])) {
            $after_element = $parameters['after_element'];
        }
        
        $output = '<div class=inputarea><fieldset><legend>Nieuw element</legend>'
                . '<form action="index.php" method="get">'
                . '<input type=hidden name="c" value="edit">'
                . '<input type=hidden name="a" value="addForm">'
                . '<input type=hidden name="parent_id" value="'
                . htmlspecialchars($parent_id) . '">'
                . '<input type=hidden name="root_id" value="'
                . htmlspecialchars($root_id) . '">';
        if (isset($after_element)) {
            $output .= '<input type=hidden name="after_element" value="'
                     . htmlspecialchars($after_element) . '">';
        }
        $output .= '<label for="type">Type voor nieuw element:</label>'
                 . '<select name="type">';
        
        $this->SetupDB();
        $this->db->SetMode(mod_Database::mode_edit);
        
        $mod_parent = $this->db->LoadElement($parent_id);
        $childclasses = $mod_parent->FindPossibleChildClasses();
        foreach ($childclasses as $classbase) {
            $classname = 'tpl_' . $classbase;
            $name = call_user_func(array($classname, 'GetName'));
            $screenname = call_user_func(array($classname, 'TypeName'));
            $output .= '<option value="' . $name . '">' . $screenname
                     . '</option>';
        }
        $output .= '</select></fieldset>'
                 . '<fieldset><legend>Bevestigen</legend>'
                 . '<input type=submit value="Verder"></form></div>';
        $this->PgStart('Nieuw element');
        echo $output;
        $this->PgEnd();
    }
    public function AddForm(array $parameters)
    {
        if (isset($parameters['parent_id'])) {
            $parent_id = $parameters['parent_id'];
        } else {
            throw new InvalidArgumentException('Invalid parent ID.');
        }
        if (isset($parameters['root_id'])) {
            $root_id = $parameters['root_id'];
        } else {
            throw new InvalidArgumentException('Invalid root ID.');
        }
        if (isset($parameters['type'])) {
            $name = $parameters['type'];
        } else {
            throw new InvalidArgumentException('Invalid type name.');
        }
        if (isset($parameters['after_element'])) {
            $after_element = $parameters['after_element'];
        }
        $classname = "";
        foreach (mod_Options::instance()->GetOption('classlist') as $classbase) {
            $iname = call_user_func(array('tpl_' . $classbase, 'GetName'));
            if ($iname == $name) {
                $classname = 'tpl_' . $classbase;
            }
        }
        if ($classname == '') {
            throw new InvalidArgumentException('Invalid type');
        }
        $tpl_element = new $classname();
        $this->SetupDB();
        $tpl_element->SetCurrentUser($this->db->GetCurrentUser());
        if (method_exists($tpl_element, 'SetGrouplist')) {
            $tpl_element->SetGrouplist($this->GetGrouplist());
        }

        $has_upload = (method_exists($tpl_element, 'GetFormHasFileUpload') &&
            $tpl_element->GetFormHasFileUpload());
        if ($has_upload) {
            $enctype = 'multipart/form-data';
        } else {
            $enctype = 'application/x-www-form-urlencoded';
        }
        
        $output = '<div class=inputarea><fieldset><legend>' 
                . $tpl_element->TypeName() . '</legend>'
                . '<form action="index.php?c=edit&a=addNow" method="post" '
                . 'enctype="' . $enctype . '">';
        if ($has_upload) {
            $output .= '<input type=hidden name="MAX_FILE_SIZE" value="'
                     . self::file_upload_max_size . '">';
        }
        $output .= '<input type=hidden name="type" value="'
                 . htmlspecialchars($name) . '">'
                 . '<input type=hidden name="parent_id" value="'
                 . htmlspecialchars($parent_id) . '">'
                 . '<input type=hidden name="root_id" value="'
                 . htmlspecialchars($root_id) . '">';
        if (isset($after_element)) {
            $output .= '<input type=hidden name="after_element" value="'
                     . htmlspecialchars($after_element) . '">';
        }
        $output .= $tpl_element->GetForm()
                 . '</fieldset>'
                 . '<fieldset><legend>Bevestigen</legend>'
                 . '<input type=submit value="Opslaan">'                 
                 . '<input type=submit name="addanother" '
                 . 'value="Opslaan en nog eenzelfde element toevoegen">';

        $this->PgStart('Nieuw element');
        echo $output;
        $this->PgEnd();
    }
    public function AddNow(array $parameters)
    {
        /*
         * Load data
         */
        if (isset($_POST['parent_id'])) {
            $parent_id = $_POST['parent_id'];
        } else {
            throw new InvalidArgumentException('Invalid parent ID.');
        }
        if (isset($_POST['root_id'])) {
            $root_id = $_POST['root_id'];
        } else {
            throw new InvalidArgumentException('Invalid root ID.');
        }
        if (isset($_POST['type'])) {
            $name = $_POST['type'];
        } else {
            throw new InvalidArgumentException('Invalid root ID.');
        }
        if (isset($_POST['after_element'])) {
            $after_element = $_POST['after_element'];
        }
        /*
         * Sanity check: can we insert this type of element at this position?
         * Also find out what class we will be inserting.
         */
        $this->SetupDB();
        $this->db->SetRecursive(mod_Database::recursive_no);
        $this->db->SetMode(mod_Database::mode_edit);
        $mod_parent = $this->db->LoadElement($parent_id);
        $childclasses = $mod_parent->FindPossibleChildClasses();
        $classname = "";
        foreach ($childclasses as $classbase) {
            $tmp_classname = 'tpl_' . $classbase;
            $searchname = call_user_func(array($tmp_classname, 'GetName'));
            if ($name == $searchname) {
                $classname = $tmp_classname;
            }
        }
        if ($classname == '') {
            throw new InvalidArgumentException('Invalid type at this position');
        }
        /*
         * Create the new element
         */
        $tpl_element = new $classname();
        if (method_exists($tpl_element, 'SetGrouplist')) {
            $tpl_element->SetGrouplist($this->GetGrouplist());
        }
        $tpl_element->SetFromForm($_POST);
        /*
         * Store it.
         */
        if (isset($after_element)) {
            $this->db->AddElement($tpl_element, $parent_id, $after_element);
        } else {
            $this->db->AddElement($tpl_element, $parent_id);
        }
        /*
         * Output
         */     
        if (isset($_POST['addanother'])) {
            $this->AddForm(array('parent_id' => $parent_id,
                                 'root_id' => $root_id,
                                 'type' => $name,
                                 'after_element' => $tpl_element->GetID()));
        } else {
            header('Location: index.php?c=edit&a=page&id=' . $root_id . '#'
                . $tpl_element->GetID());
            echo "Success";
        }
    }

    public function Edit(array $parameters)
    {
        if (isset($parameters['id'])) {
            $element_id = $parameters['id'];
        } else {
            throw new InvalidArgumentException('Invalid element ID.');
        }
        if (isset($parameters['root_id'])) {
            $root_id = $parameters['root_id'];
        } else {
            throw new InvalidArgumentException('Invalid root ID.');
        }
        $this->SetupDB();
        $this->db->SetRecursive(mod_Database::recursive_no);
        $this->db->SetMode(mod_Database::mode_edit);
        $mod_element = $this->db->LoadElement($element_id);
        $sheet = new tpl_Sheet(array($this, 'GetGrouplist'));
        $tpl_element = $sheet->ConvertModelToTpl($mod_element);
        
        $has_upload = (method_exists($tpl_element, 'GetFormHasFileUpload') &&
            $tpl_element->GetFormHasFileUpload());
        if ($has_upload) {
            $enctype = 'multipart/form-data';
        } else {
            $enctype = 'application/x-www-form-urlencoded';
        }
        
        $output = '<div class=inputarea>'
                . '<fieldset><legend>Eigenschappen van element</legend>'
                . '<form action="index.php?c=edit&a=editSave" method="post" '
                . 'enctype="' . $enctype . '">';
        if ($has_upload) {
            $output .= '<input type=hidden name="MAX_FILE_SIZE" value="'
                     . self::file_upload_max_size . '">';
        }
        $output .= '<input type=hidden name="id" value="'
                 . htmlspecialchars($element_id) . '">'
                 . '<input type=hidden name="root_id" value="'
                 . htmlspecialchars($root_id) . '">'
                 . $tpl_element->GetForm()
                 . '</fieldset><fieldset><legend>Bevestigen</legend>'
                 . '<input type=submit value="Opslaan"></fieldset></form>'
                 . '</div>';

        $this->PgStart('Element bewerken');
        echo $output;
        $this->PgEnd();
    }
    public function EditSave(array $parameters)
    {
        /*
         * Load data
         */
        if (isset($_POST['id'])) {
            $element_id = $_POST['id'];
        } else {
            throw new InvalidArgumentException('Invalid element ID.');
        }
        if (isset($_POST['root_id'])) {
            $root_id = $_POST['root_id'];
        } else {
            throw new InvalidArgumentException('Invalid root ID.');
        }
        $this->SetupDB();
        $this->db->SetRecursive(mod_Database::recursive_no);
        $this->db->SetMode(mod_Database::mode_edit);
        $mod_element = $this->db->LoadElement($element_id);
        $sheet = new tpl_Sheet(array($this, 'GetGrouplist'));
        $tpl_element = $sheet->ConvertModelToTpl($mod_element);
        $tpl_element->SetFromForm($_POST);
        $this->db->UpdateElement($tpl_element);

        header('Location: index.php?c=edit&a=page&id=' . $root_id . '#'
            . $element_id);
        echo "Success";
    }
    
    private function PermissionsForm(mod_Element $mod_element, $root_id,
        $newpermissions = null)
    {
        $sheet = new tpl_PermissionsForm();
        $sheet->SetElement($mod_element);
        $this->db->SetMode(mod_Database::mode_view);
        $sheet->SetUserlist($this->db->LoadTypeAll('User'));
        $sheet->SetGrouplist($this->GetGrouplist());

        $output = '<div class=inputarea><fieldset><legend>Elementrechten</legend>'
                . '<form action="index.php?c=edit&a=permissionssave" method="post">'
                . '<input type=hidden name="id" value="'
                . htmlspecialchars($mod_element->GetID()) . '">'
                . '<input type=hidden name="root_id" value="'
                . htmlspecialchars($root_id) . '">'
                . $sheet->GetForm($newpermissions)
                . '</fieldset><fieldset><legend>Bevestigen</legend>'
                . '<input type=submit value="Opslaan"></fieldset></form></div>';

        $this->PgStart('Rechten voor element aanpassen');
        echo $output;
        $this->PgEnd();
    }
    
    public function Permissions(array $parameters)
    {
        if (isset($parameters['id'])) {
            $element_id = $parameters['id'];
        } else {
            throw new InvalidArgumentException('Invalid element ID.');
        }
        if (isset($parameters['root_id'])) {
            $root_id = $parameters['root_id'];
        } else {
            throw new InvalidArgumentException('Invalid root ID.');
        }
        $this->SetupDB();
        
        $this->db->SetRecursive(mod_Database::recursive_no);
        $this->db->SetMode(mod_Database::mode_edit);
        $mod_element = $this->db->LoadElement($element_id);
        $this->PermissionsForm($mod_element, $root_id);
    }
    
    public function PermissionsSave(array $parameters)
    {
        /*
         * Load data
         */
        if (isset($_POST['id'])) {
            $element_id = $_POST['id'];
        } else {
            throw new InvalidArgumentException('Invalid element ID.');
        }
        if (isset($_POST['root_id'])) {
            $root_id = $_POST['root_id'];
        } else {
            throw new InvalidArgumentException('Invalid root ID.');
        }
        $this->SetupDB();
        
        $this->db->SetRecursive(mod_Database::recursive_no);
        $this->db->SetMode(mod_Database::mode_edit);
        $mod_element = $this->db->LoadElement($element_id);

        $sheet = new tpl_PermissionsForm();
        $sheet->SetElement($mod_element);
        $newpermissions = $sheet->GetPermissionsFromForm();

        if (isset($_POST['addpermission'])) {
            /*
             * Add the desired rule and then show the permissions form again
             */
            if (isset($_POST['createwhat'])
                && ($_POST['createwhat'] == 'user') && isset($_POST['user'])
                && ($_POST['user'] != '')) {
                $check = $this->db->LoadUser($_POST['user'],
                    mod_Database::match_id);
                if ($this->db->IsValidElement($check)) {
                    $newpermissions->SetUserPermission($_POST['user'], 0);
                }
            } elseif (isset($_POST['createwhat'])
                && ($_POST['createwhat'] == 'group')
                && isset($_POST['group']) && ($_POST['group'] != '')) {
                $check = $this->db->LoadGroup($_POST['group'],
                    mod_Database::match_id);
                if ($this->db->IsValidElement($check)) {
                    $newpermissions->SetGroupPermission($_POST['group'], 0);
                }
            }
            $this->PermissionsForm($mod_element, $root_id, $newpermissions);
        } else {
            /*
             * Default action: save the permissions
             */
            $this->db->UpdatePermissions($mod_element, $newpermissions);
        
            header('Location: index.php?c=edit&a=page&id=' . $root_id);
            echo "Success";
        }
    }
    
    public function ClipboardCut(array $parameters)
    {
        /*
         * Load data
         */
        if (isset($parameters['id'])) {
            $element_id = $parameters['id'];
        } else {
            throw new InvalidArgumentException('Invalid element ID.');
        }
        if (isset($parameters['root_id'])) {
            $root_id = $parameters['root_id'];
        } else {
            throw new InvalidArgumentException('Invalid root ID.');
        }
        
        session_start();
        $_SESSION['clipboard.id'] = $element_id;
        $_SESSION['clipboard.cut'] = true;
        
        header('Location: index.php?c=edit&a=page&id=' . $root_id);
        echo "Success";
    }
    
    public function ClipboardPaste(array $parameters)
    {
        /*
         * Load data
         */
        if (isset($parameters['parent_id'])) {
            $parent_id = $parameters['parent_id'];
        } else {
            throw new InvalidArgumentException('Invalid parent ID.');
        }
        if (isset($parameters['root_id'])) {
            $root_id = $parameters['root_id'];
        } else {
            throw new InvalidArgumentException('Invalid root ID.');
        }
        if (isset($parameters['after_element'])) {
            $after_element = $parameters['after_element'];
        }

        $this->SetupDB();
        $this->db->SetMode(mod_Database::mode_edit);
        
        $this->LoadClipboard();
        if ($this->clipboardcut) {
            if (isset($after_element)) {
                $this->db->UpdatePosition($this->clipboardelement, $parent_id,
                    $after_element) || die('Error');
            } else {
                $this->db->UpdatePosition($this->clipboardelement, $parent_id)
                    || die('Error');
            }
        }
        
        header('Location: index.php?c=edit&a=page&id=' . $root_id);
        echo "Success";
    }
    
    private function LoadClipboard()
    {
        if (!isset($_SESSION)) {
            session_start();
        }
        if (isset($_SESSION['clipboard.id'])) {
            $this->db->SetRecursive(mod_Database::recursive_no);
            $element = $this->db->LoadElement($_SESSION['clipboard.id']);
            if ($this->db->IsValidElement($element)) {
                $this->clipboardelement = $element;
            }
            if (isset($_SESSION['clipboard.cut']) 
                && $_SESSION['clipboard.cut']) {
                $this->clipboardcut = true;
            }
        }
    }
    private function LoadParentTree($element_id)
    {
        $newparents = array();
        $this->db->SetRecursive(mod_Database::recursive_no);
        $parent_id = $this->db->ParentID($element_id);
        $parent_element = $this->db->LoadElement($parent_id);
        while (!($parent_element === false)) {
            $newparents[] = $parent_element;
            $parent_id = $this->db->ParentID($parent_element);
            $parent_element = $this->db->LoadElement($parent_id);
        }
        $this->parenttree = $newparents;
    }

    public function Page(array $parameters)
    {
        if (isset($parameters['id'])) {
            $element_id = $parameters['id'];
        } else {
            throw new InvalidArgumentException('Invalid element ID.');
        }
        $this->SetupDB();
        $this->db->SetMode(mod_Database::mode_edit);
        
        $this->LoadClipboard();
        $this->LoadParentTree($element_id);
        
        $this->db->SetRecursive(mod_Database::recursive_yes);
        $sheet = $this->db->LoadElement($element_id);
        if ($this->db->IsValidElement($sheet)) {
            $outpage = new tpl_Sheet(array($this, 'GetGrouplist'));
            if (!is_null($this->db->GetCurrentUser())) {
                $outpage->SetCurrentUser($this->db->GetCurrentUser());
            }
            $outpage->SetModelTree($sheet);
            $outpage->SetRoots($this->parenttree);
            if (!is_null($this->clipboardelement)) {
                $outpage->SetClipboardElement($this->clipboardelement);
            }
            echo $outpage->GetContents();
        } else {
            $this->db->RegisterEvent('edit.page', false,
                $this->db->GetCurrentUser(), null, 'Element: ' . $element_id);
            die("Error opening page.");
        }
    }
}

?>
