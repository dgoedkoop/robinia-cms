<?php

class tpl_Sheet implements tpl_ElementInterface
{
    private $modeltree = null;
    private $menutree = null;
    
    public function SetModelTree(mod_Element $mod_element)
    {
        $this->modeltree = $mod_element;
    }
    
    public function SetMenuTree(mod_Element $mod_element)
    {
        $this->menutree = $mod_element;
    }
    
    private function ContainerContainsHeading(mod_Element $container)
    {
        $found = false;
        foreach ($container->GetChildren() as $mod_child)
            if ($mod_child instanceof mod_Heading)
            $found = true;
        return $found;
    }
    
    private function ConvertModelToTpl(mod_Element $mod_element, $headinglevel = 1)
    {
        $mod_class = get_class($mod_element);
        if (!(substr($mod_class, 0, 4) == 'mod_')) {
            return false;
        }
        $tpl_class = 'tpl_' . substr($mod_class, 4);
        $i = new $tpl_class();
        $i->SetID($mod_element->GetID());
        $i->SetStatus($mod_element->GetStatus());
        $i->SetFullyLoaded($mod_element->GetFullyLoaded());
        $i->SetCurrentUser($mod_element->GetCurrentUser());
        $i->SetPermissions($mod_element->GetPermissions());
        $i->SetFromModel($mod_element);
        if ($i instanceof tpl_Heading) {
            $i->SetLevel($headinglevel);
        }
        if ($i) {
            $newheadinglevel = $headinglevel;
            if ($this->ContainerContainsHeading($mod_element))
                $newheadinglevel += 1;
            foreach ($mod_element->GetChildren() as $mod_child) {
                $tpl_child = $this->ConvertModelToTpl($mod_child, $newheadinglevel);
                if ($tpl_child)
                    $i->AddChild($tpl_child);
            }
        }
        return $i;
    }
    
    private function ConvertMenuToTpl(mod_Element $mod_element)
    {
        $i = new tpl_MenuItem();
        $i->SetID($mod_element->GetID());
        $valid = $i->SetFromModel($mod_element);
        if ($valid) {
            if ($mod_element->GetID() == $this->modeltree->GetID()) {
                $i->SetLinkActive(false);
            }
            foreach ($mod_element->GetChildren() as $mod_child) {
                $tpl_child = $this->ConvertMenuToTpl($mod_child);
                if ($tpl_child)
                    $i->AddChild($tpl_child);
            }
            return $i;
        }
        return null;
    }

    private function jQuery_css()
    {
        return '<link rel="stylesheet" href="css/prettyPhoto.css" '
             . 'type="text/css" media="screen" charset="utf-8">';
    }
    private function jQuery_Start()
    {
        return '<script type="text/javascript" src="js/jquery.js"></script>';
    }
    
    private function jQuery_Lightbox()
    {
        return <<<EOT
<script src="js/jquery.prettyPhoto.js" type="text/javascript"
    charset="utf-8">
</script>
<script type="text/javascript" charset="utf-8">
  $(document).ready(function(){
    $("a[rel^='lightbox']").prettyPhoto({
      show_title: false,
      social_tools: false
    });
  });
</script>
EOT;
    }
    
    
    private function PageStart($title, $description = '')
    {
        $options = mod_Options::instance();
        header('Content-Type: text/html; charset=utf-8');
        $output = '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" '
                . "\"http://www.w3.org/TR/html4/strict.dtd\">\n"
                . "<HTML>\n"
                . "<HEAD>\n"
                . "<meta name=\"viewport\" content=\"width=device-width, initial-scale=1.0\">"
                . "<TITLE>" . htmlspecialchars($title);
        if ($options->GetOption('site_title')) {
            $output .= ' - ' . $options->GetOption('site_title');
        }
        $output .= "</TITLE>\n";
        if ($description != '') {
            $output .= '<META NAME="description" CONTENT="'
                     . htmlspecialchars($description) . '">';
        }
        $output .= '<LINK rel="stylesheet" type="text/css" href="template/'.$options->GetOption('template').'/main.css">'
                 . $this->jQuery_css()
                 . '<META http-equiv="Content-type" content="text/html; '
                 . 'charset=utf-8">'
                 . "\n</HEAD>\n"
                 . "<BODY>\n"
                 . $this->jQuery_Start()
                 . "<div class=page>\n"
                 . "<div class=pageheadingleft>"
                 . '<a href="index.html">'
                 . '<img src="template/'.$options->GetOption('template').'/logo.png" alt="logo" class="link"></a>'
                 . "</div>\n"
                 . "<div class=pageright>"
                 . "<div class=pageheadingheading>\n"
                 . "<h1>$title</h1>\n"
                 . "</div><div class=pagemain>\n";
        return $output;
    }
    
    private function PageEnd()
    {
        return "</div></div>"
             . $this->jQuery_Lightbox()
             . "</BODY>\n"
             . "</HTML>";
    }
    
    private function SwitchToMenu()
    {
        if (!is_null($this->modeltree)) {
            return '<p class=copyright>'
                . sprintf(tr('lastmodified'), 
                    date('d-m-Y', $this->modeltree->GetTimeModified()))
                . '</p>'.'</div></div>'
                . '<div class=menu>';
        } else {
            return '';
        }
    }

    private function GetTitle(tpl_ElementInterface $tplroot)
    {
        $children = $tplroot->GetChildren();
        if (!($children[0] instanceof tpl_TitleDescription)) {
            return '';
        }
        return $children[0]->GetTitle();
    }
    private function GetDescription(tpl_ElementInterface $tplroot)
    {
        $children = $tplroot->GetChildren();
        if (!($children[0] instanceof tpl_TitleDescription)) {
            return '';
        }
        return $children[0]->GetDescription();
    }
    
    public function GetOutput()
    {
    $tplroot = $this->ConvertModelToTpl($this->modeltree, 1);
        if (!is_null($this->menutree)) {
            $menuroot = $this->ConvertMenuToTpl($this->menutree);
        } else {
            $menuroot = null;
        }
        $title = $this->GetTitle($tplroot);
        $description = $this->GetDescription($tplroot);
        $output = $this->PageStart($title, $description);
        if ($tplroot) {
            $output .= $tplroot->GetOutput();
        }
        if (!is_null($menuroot)) {
            $output .= $this->SwitchToMenu()
                     . $menuroot->GetOutput();
        }
        $output .= $this->PageEnd();
        return $output;
    }
    
    public function SetFromModel(mod_Element $mod_element)
    {
        return true;
    }
}
?>
