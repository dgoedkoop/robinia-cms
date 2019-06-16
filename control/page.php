<?php

require_once 'model/database.php';
require_once 'model/clientstorage.php';

class ctrl_Page
{
    private $db;
    
    public function __construct()
    {
        require_once 'template/'.mod_Options::instance()->GetOption('template').'/sheet.php';
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
    
    private function IsEligibleForMenu(mod_Element $mod_element)
    {
        $children = $mod_element->GetChildren();
        return $this->db->IsValidElement($mod_element) &&
               (count($children) > 0) &&
               method_exists($children[0], 'GetTitle');
    }
    
    private function LoadMenuTree($leaf_element_id) {
        $this->db->SetRecursive(mod_Database::recursive_one_level);
        $root = null;
        $element_id = $leaf_element_id;
        while ($element_id) {
            $new_root = $this->db->LoadElement($element_id);
            if ($root && $this->IsEligibleForMenu($new_root)) {
                foreach($new_root->GetChildren() as $child) {
                    if ($child->GetID() == $root->GetID()) {
                        $child->SetChildren($root->GetChildren());
                    }
                }
            }
            if ($this->IsEligibleForMenu($new_root)) {
                /* 
                 * First clean up a bit.
                 */
                $children = $new_root->GetChildren(); 
                foreach($children as $key => $child) {
                    if (($key > 0) && !$this->IsEligibleForMenu($child)) {
                        unset($children[$key]);
                    }
                }
                $new_root->SetChildren($children);
                /*
                 * Iterate.
                 */
                $root = $new_root;
                $element_id = $this->db->ParentID($root);
            } else {
                return $root;
            }
        }
        return $root;
    }
    
    private function Page403($element = null)
    {
        /*
         * Log the error
         */
        $this->db->RegisterEvent('page', false, $this->currentuser, $element, 
            '403 Forbidden');
        
        $world = new mod_Usergroup();
        $world->SetGID(0);
        $readonlypermissions = new mod_Permissions();
        $readonlypermissions->SetGroupPermission($world->GetGID(), 
            mod_Permissions::perm_view);
        
        $page = new mod_Page();
        $page->SetPermissions($readonlypermissions);
        $pagedsc = new mod_TitleDescription();
        $pagedsc->SetPermissions($readonlypermissions);
        $page->AddChild($pagedsc);
        $pagedsc->SetTitle(tr('error403'));
        $pagetext = new mod_Paragraph();
        $pagetext->SetPermissions($readonlypermissions);
        $page->AddChild($pagetext);
        $pagetext->SetText(tr('error403text') . ' ' .
            sprintf(tr('errorreturnhome'), mod_Options::instance()->GetOption('basepath')));
        $outpage = new tpl_Sheet();
        $outpage->SetModelTree($page);
        header('HTTP/1.0 403 Forbidden');
        echo $outpage->GetOutput();
        die;
    }
    private function Page404($what)
    {
        /*
         * Log the error
         */
        $registered = $this->db->RegisterEvent('page', false,
            $this->currentuser, null, '404 Not Found: ' . $what);
        
        $world = new mod_Usergroup();
        $world->SetGID(0);
        $readonlypermissions = new mod_Permissions();
        $readonlypermissions->SetGroupPermission($world->GetGID(), 
            mod_Permissions::perm_view);
        
        $page = new mod_Page();
        $page->SetPermissions($readonlypermissions);
        $pagedsc = new mod_TitleDescription();
        $pagedsc->SetPermissions($readonlypermissions);
        $page->AddChild($pagedsc);
        $pagedsc->SetTitle(tr('error404'));
        $pagetext = new mod_Paragraph();
        $pagetext->SetPermissions($readonlypermissions);
        $page->AddChild($pagetext);
        if ($registered) {
            $pagetext->SetText(tr('error404text') . ' ' . tr('errorloggedtext') . ' ' . 
                sprintf(tr('errorreturnhome'), mod_Options::instance()->GetOption('basepath')));
        } else {
            $pagetext->SetText(tr('error404text') . ' ' .
                sprintf(tr('errorreturnhome'), mod_Options::instance()->GetOption('basepath')));
        }
        $outpage = new tpl_Sheet();
        $outpage->SetModelTree($page);
        header('HTTP/1.0 404 Not Found');
        echo $outpage->GetOutput();
        die;
    }
    
    private function PageByID($element_id)
    {
        $this->db->SetRecursive(mod_Database::recursive_no);
        $headercheck_element = $this->db->LoadElement($element_id);
        if (!$this->db->IsValidElement($headercheck_element)) {
            if ($headercheck_element instanceof mod_ElementPermissionDenied) {
                $this->Page403($headercheck_element);
            } else {
                $this->Page404('id=' . $element_id);
            }
        }
        if (isset($_SERVER['HTTP_IF_MODIFIED_SINCE'])) {
            $if_modified_since = strtotime(preg_replace('/;.*$/', '', 
                $_SERVER['HTTP_IF_MODIFIED_SINCE']));
            if ($if_modified_since >= $headercheck_element->GetTimeModified()) {
                header($_SERVER['SERVER_PROTOCOL'] . ' 304 Not Modified');
                exit();
            }
        }
        header('Last-Modified: '
             . date('r', $headercheck_element->GetTimeModified()));
        
        $this->db->SetRecursive(mod_Database::recursive_yes);
        $pageroot = $this->db->LoadElement($element_id);
        $menuroot = $this->LoadMenuTree($element_id);
        $outpage = new tpl_Sheet();
        $outpage->SetModelTree($pageroot);
        if (!is_null($menuroot)) {
            $outpage->SetMenuTree($menuroot);
        }
        echo $outpage->GetOutput();
    }
        
    public function Page(array $parameters)
    {
        if (isset($parameters['id'])) {
            $element_id = $parameters['id'];
        } else {
            throw new InvalidArgumentException('Invalid element ID.');
        }
        $this->SetupDB();
        if (isset($parameters['mode']) && ($parameters['mode'] == 'preview')) {
            $this->db->SetMode(mod_Database::mode_preview);
        }

        $this->PageByID($element_id);
    }

    public function PageLink(array $parameters)
    {
        if (isset($parameters['link'])) {
            $linkname = $parameters['link'];
        } else {
            throw new InvalidArgumentException('Invalid element ID.');
        }
        $this->SetupDB();
        
        $this->db->SetRecursive(mod_Database::recursive_no);
        $element = $this->db->LoadLink($linkname);
        if ($this->db->IsValidElement($element)) {
            $parent_id = $this->db->ParentID($element);
            $this->PageByID($parent_id);
        } else {
            $this->Page404('filename=' . $linkname);
        }
    }

    public function AjaxSetClientStorage(array $parameters)
    {
        /* The security risk is limited, because the parameters are from HTTP GET,
         * which has quite a small length limit */
        foreach ($parameters as $key => $value) {
            mod_ClientStorage::instance()->SetOption($key, $value);
        }
        echo 'Success!';
    }
    
}

?>
