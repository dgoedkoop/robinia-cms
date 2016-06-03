<?php

require_once 'model/options.php';

class tpl_Sheet implements tpl_ElementInterface
{
    private $modeltree = null;
    private $roots = array();
    private $clipboardelement = null;
    private $options;
    private $grouplistcallback;
    private $currentuser = null;
    
    public function __construct($options, $grouplistcallback = false)
    {
        $this->options = $options;
        $this->grouplistcallback = $grouplistcallback;
    }
    
    public function SetModelTree($mod_element)
    {
	$this->modeltree = $mod_element;
    }
    
    public function SetRoots($roots)
    {
        $this->roots = $roots;
    }
    public function SetClipboardElement(mod_Element $element)
    {
        $this->clipboardelement = $element;
    }
    
    public function SetCurrentUser(mod_User $user)
    {
        $this->currentuser = $user;
    }
    
    private function ContainerContainsHeading(mod_Element $container)
    {
	$found = false;
	foreach ($container->GetChildren() as $mod_child)
	    if ($mod_child instanceof mod_Heading)
		$found = true;
	return $found;
    }
    
    public function ConvertModelToTpl(mod_Element $mod_element, $headinglevel = 1)
    {
        $mod_class = get_class($mod_element);
        if (!(substr($mod_class, 0, 4) == 'mod_')) {
            return false;
        }
        $tpl_class = 'tpl_' . substr($mod_class, 4);
        $i = new $tpl_class($this->options);
        $i->SetID($mod_element->GetID());
        $i->SetStatus($mod_element->GetStatus());
        $i->SetFullyLoaded($mod_element->GetFullyLoaded());
        $i->SetCurrentUser($mod_element->GetCurrentUser());
        $i->SetPermissions($mod_element->GetPermissions());
        $i->SetFromModel($mod_element);
        if (method_exists($i, 'SetGrouplist') && 
            is_callable($this->grouplistcallback)) {
            $i->SetGrouplist(call_user_func($this->grouplistcallback));
        }
        if ($i instanceof tpl_Heading) {
            $i->SetLevel($headinglevel);
        }
    	if ($i) {
	    $nieuweheadinglevel = $headinglevel;
	    if ($this->ContainerContainsHeading($mod_element))
		$nieuweheadinglevel += 1;
	    foreach ($mod_element->GetChildren() as $mod_child) {
		$tpl_child = $this->ConvertModelToTpl($mod_child, $nieuweheadinglevel);
		if ($tpl_child)
		    $i->AddChild($tpl_child);
	    }
	}
	return $i;
    }
    
    public function EditSub($parenttree, mod_Element $after_element = null)
    {
        /*
         * Cannot do anything if not fully loaded. The user should open this
         * element instead before adding new children.
         */
        if (!$parenttree[0]->GetFullyLoaded()) {
            return '';
        }
        /*
         * Check if we have permission to add sub-elements.
         */
        $permissions = $parenttree[0]->GetPermissions();
        if (is_null($this->currentuser) || !$permissions->HasPermission(
            $this->currentuser, mod_Permissions::perm_addchild)) {
            return '';
        }
        /*
         * If we can add a sub-element, offer an option to insert a new one.
         */
        if ($parenttree[0]->FindPossibleChildClasses($this->options) 
            === array()) {
            $output = '';
        } else {
            $newlink = new tpl_DynamicLink();
            $newlink->SetClass('editbutton');
            $newlink->SetURL('index.php');
            $newlink->AddParameter('c', 'edit');
            $newlink->AddParameter('a', 'add');
            $newlink->AddParameter('parent_id', $parenttree[0]->GetID());
            $newlink->AddParameter('root_id', $this->modeltree->GetID());
            if ($after_element) {
                $newlink->AddParameter('after_element', $after_element->GetID());
            }
            $newlink->SetCaption('+');
            $output = $newlink->GetLink();
        }
        /*
         * If we have anything on the clipboard, offer to move it here. But
         * not if this would induce a circular reference
         */
        if (!is_null($this->clipboardelement)) {
            $found = false;
            foreach ($parenttree as $parent) {
                if ($parent->GetID() == $this->clipboardelement->GetID()) {
                    $found = true;
                    break;
                }
            }
            /*
             * We can paste the clipboard item, if the position is not a child
             * of the item itself, thus, if we do not find the clipboard item
             * between the (grand)parents for this position.
             */
            if (!$found 
                && $parenttree[0]->CanHaveAsChild($this->clipboardelement)) {
                $pastelink = new tpl_DynamicLink();
                $pastelink->SetClass('editbutton');
                $pastelink->SetURL('index.php');
                $pastelink->AddParameter('c', 'edit');
                $pastelink->AddParameter('a', 'clipboardPaste');
                $pastelink->AddParameter('parent_id', $parenttree[0]->GetID());
                $pastelink->AddParameter('root_id', $this->modeltree->GetID());
                if ($after_element) {
                    $pastelink->AddParameter('after_element', $after_element->GetID());
                }
                $pastelink->SetCaption('Plakken');
                $output .= $pastelink->GetLink();
            }
        }
        if ($output != '') {
            $output = '<p class="editorlinkbox" id="' 
                    . urlencode($parenttree[0]->GetID()) . '">'
                    . $output . '</p>';
        }
        return $output;
    }
    
    private function ClipboardLink(tpl_ElementInterface $element, 
                                   $parenttree = array())
    {
        if (isset($parenttree[0]) && !is_null($this->currentuser)
            && $parenttree[0]->GetPermissions()->HasPermission(
            $this->currentuser, mod_Permissions::perm_deletechild)) {
            $cbclink = new tpl_DynamicLink();
            $cbclink->SetClass('editbutton');
            $cbclink->SetURL('index.php');
            $cbclink->AddParameter('c', 'edit');
            $cbclink->AddParameter('a', 'clipboardCut');
            $cbclink->AddParameter('id', $element->GetID());
            $cbclink->AddParameter('root_id', $this->modeltree->GetID());
            $cbclink->SetCaption('Knippen');
            return $cbclink->GetLink();
        } else {
            return '';
        }
    }
    
    public function ContentsForFrame(tpl_ElementInterface $element, 
                                     $parenttree = array())
    {
        $status = $element->GetStatus();
        if ($status == 'n') {
            $contentprefix = '<div class="editorinactive">';
            $contentsuffix = '</div>';
        } elseif ($status == 'd') {
            $contentprefix = '<div class="editordeletionmarked">';
            $contentsuffix = '</div>';
        } elseif ($status == 'p') {
            $contentprefix = '<div class="editorpending">';
            $contentsuffix = '</div>';
        } else {
            $contentprefix = '';
            $contentsuffix = '';
        }
        $output = '<p class="editorframeheading">'
                . $element::TypeName();
        $permissions = $element->GetPermissions();
        if (($element instanceof mod_Page) ||
            ($element instanceof mod_Folder)) {
            $previewlink = new tpl_DynamicLink();
            $previewlink->SetClass('editbutton');
            $previewlink->SetURL('index.php');
            $previewlink->AddParameter('c', 'page');
            $previewlink->AddParameter('a', 'page');
            $previewlink->AddParameter('mode', 'preview');
            $previewlink->AddParameter('id', $element->GetID());
            $previewlink->SetCaption('Preview');
            $output .= $previewlink->GetLink();
        }
        if (($status == 'y') || ($status == 'p')) {
            if ($element->GetFullyLoaded()) {
                if (!is_null($this->currentuser) && $permissions->HasPermission(
                    $this->currentuser, mod_Permissions::perm_edit)
                    && ($element->GetForm()) ) {
                    $editlink = new tpl_DynamicLink();
                    $editlink->SetClass('editbutton');
                    $editlink->SetURL('index.php');
                    $editlink->AddParameter('c', 'edit');
                    $editlink->AddParameter('a', 'edit');
                    $editlink->AddParameter('id', $element->GetID());
                    $editlink->AddParameter('root_id', $this->modeltree->GetID());
                    $editlink->SetCaption('Bewerken');
                    $output .= $editlink->GetLink();
                }
            } else {
                $editlink = new tpl_DynamicLink();
                $editlink->SetClass('editbutton');
                $editlink->SetURL('index.php');
                $editlink->AddParameter('c', 'edit');
                $editlink->AddParameter('a', 'page');
                $editlink->AddParameter('id', $element->GetID());
                $editlink->SetCaption('Openen');
                $output .= $editlink->GetLink();
            }
            if (($status == 'p') && !is_null($this->currentuser)
                && $permissions->HasPermission($this->currentuser,
                                               mod_Permissions::perm_edit)) {
                $deletelink = new tpl_DynamicLink();
                $deletelink->SetClass('editbutton');
                $deletelink->SetURL('index.php');
                $deletelink->AddParameter('c', 'edit');
                $deletelink->AddParameter('a', 'delete');
                $deletelink->AddParameter('id', $element->GetID());
                $deletelink->AddParameter('root_id', $this->modeltree->GetID());
                $deletelink->SetCaption('Invoegen ongedaan maken');
                $output .= $this->ClipboardLink($element, $parenttree)
                         . $deletelink->GetLink();
            }
            if (($status == 'y') && !is_null($this->currentuser)
                && $permissions->HasPermission($this->currentuser,
                                               mod_Permissions::perm_edit)
                && isset($parenttree[0])
                && $parenttree[0]->GetPermissions()->HasPermission(
                    $this->currentuser, mod_Permissions::perm_deletechild)) {
                $deletelink = new tpl_DynamicLink();
                $deletelink->SetClass('editbutton');
                $deletelink->SetURL('index.php');
                $deletelink->AddParameter('c', 'edit');
                $deletelink->AddParameter('a', 'delete');
                $deletelink->AddParameter('id', $element->GetID());
                $deletelink->AddParameter('root_id', $this->modeltree->GetID());
                $deletelink->SetCaption('Verwijderen');
                $output .= $this->ClipboardLink($element, $parenttree)
                         . $deletelink->GetLink();
            }
            if (!is_null($this->currentuser) && ($permissions->HasPermission(
                $this->currentuser, mod_Permissions::perm_changeperm)
                || $this->currentuser->GetAdmin())) {
                $permlink = new tpl_DynamicLink();
                $permlink->SetClass('editbutton');
                $permlink->SetURL('index.php');
                $permlink->AddParameter('c', 'edit');
                $permlink->AddParameter('a', 'permissions');
                $permlink->AddParameter('id', $element->GetID());
                $permlink->AddParameter('root_id', $this->modeltree->GetID());
                $permlink->SetCaption('Rechten bewerken');
                $output .= $permlink->GetLink();
            }
        } elseif ($status == 'd') {
            $undellink = new tpl_DynamicLink();
            $undellink->SetClass('editbutton');
            $undellink->SetURL('index.php');
            $undellink->AddParameter('c', 'edit');
            $undellink->AddParameter('a', 'reactivate');
            $undellink->AddParameter('id', $element->GetID());
            $undellink->AddParameter('root_id', $this->modeltree->GetID());
            $undellink->SetCaption('Verwijderen ongedaan maken');
            $output .= $undellink->GetLink();
        }
        $newparents = array_merge(array($element), $parenttree);
        $output .= "</p>\n"
                . $contentprefix . $element->GetContents() . $contentsuffix
                . $this->EditSub($newparents);
        foreach ($element->GetChildren() as $child) {
            $output .= $this->FrameWithElement($child, $newparents)
                     . $this->EditSub($newparents, $child);
        }
        return $output;
    }
    
    public function FormForFrame(tpl_Element $element)
    {
        $output = '<p class="editorframeheading">' . $element::TypeName() . "\n"
                . "<form>\n"
                . $element->GetForm()
                . '<input type=submit value="Opslaan">';
        $element_id = $element->GetID();
        if ($element_id) {
            $output .= '<input type=reset class=editcancel id="' . $element_id
                     . '" value="Annuleren">';
        } else {
            $output .= '<input type=reset class=editnewcancel '
                     . 'value="Annuleren">';
        }
        $output .= "</form>\n";
        return $output;
    }
    
    public function FrameWithElement(tpl_ElementInterface $element, 
                                     $parenttree = array())
    {
        $output = '<div class="editorframe" id="'
                . htmlspecialchars($element->GetID()) . '"><a name="'
                . htmlspecialchars($element->GetID()) . '"></a>'
                . $this->ContentsForFrame($element, $parenttree)
                . "</div>\n";
        return $output;
    }
    
    private function PageStart($title)
    {
        header('Content-Type: text/html; charset=utf-8');
        return '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" '
             . "\"http://www.w3.org/TR/html4/strict.dtd\">\n"
             . "<HTML>\n"
             . "<HEAD>\n"
             . "<TITLE>$title</TITLE>\n"
             . '<LINK rel="stylesheet" type="text/css" href="/main.css">'
             . '<META http-equiv="Content-type" content="text/html; '
             . 'charset=utf-8">'
             . "\n</HEAD>\n"
             . "<BODY>\n"
             . "<div class=pagina>\n"
             . "<div class=paginakoplinks><div class=paginakopkop>\n"
             . "<H1>&nbsp;</H1>\n"
             . "</div></div>\n"
             . "<div class=paginakoprechts><div class=paginakopkop>\n"
             . "<h1>$title</h1>\n"
             . "</div></div>\n"
             . "<div class=paginazelf>";        
    }
    
    private function SwitchToMenu()
    {
        return '</div><div class=menu>';
    }
    
    private function PageEnd()
    {
        return "</div></BODY>\n"
             . "</HTML>";
    }
    
    public function __TMPEDITMENU($element_id) {
        if (!is_null($this->currentuser)) {
            $root_id = $this->currentuser->GetRootID();
        } else {
            $root_id = 1;
        }
        $output = '<div class=menutitel>'
                . 'TOOLS'
                . '</div><div class=menukastje>'
                . '<a href="index.php?c=edit&amp;a=page&amp;id='
                . urlencode($root_id) . '">'
                . 'Terug naar index</a><br>'
                . '<a href="index.php?c=edit&amp;a=commit&amp;id='
                . urlencode($element_id)
                . '">Wijzigingen doorvoeren</a><br>'
                . '<a href="index.php?c=edit&amp;a=rollback&amp;id='
                . urlencode($element_id)
                . '">Wijzigingen ongedaan maken</a></div>';
        if (!is_null($this->currentuser)) {
            $output .= '<div class=menutitel>GEBRUIKER</div>'
                     . '<div class=menukastje>Ingelogd als: ' 
                     . htmlspecialchars($this->currentuser->GetUsername())
                     . "<br>\n"
                     . '<a href="index.php?c=login&amp;a=logout">Uitloggen</a>'
                     . '</div>';
        }
        return $output;
    }
    
    private function GetTitle(tpl_ElementInterface $tplroot)
    {
        $children = $tplroot->GetChildren();
        if ((count($children)==0) || 
            !($children[0] instanceof tpl_TitleDescription)) {
            return '';
        }
        return $children[0]->GetTitle();
    }
    
    public function GetContents()
    {
	$tplroot = $this->ConvertModelToTpl($this->modeltree, 1);
        $title = $this->GetTitle($tplroot);
	return $this->PageStart($title) 
             . $this->FrameWithElement($tplroot, $this->roots) 
             . $this->SwitchToMenu()
             . $this->__TMPEDITMENU($tplroot->GetID())
             . $this->PageEnd();
    }
    
    public function GetForm() {
	return false;
    }
    
    public function SetFromForm(array $formdata)
    {
        return true;
    }
    
    public static function TypeName()
    {
        return 'Pagina';
    }
    
    public function SetFromModel(mod_Element $mod_element)
    {
        return true;
    }
}
?>
