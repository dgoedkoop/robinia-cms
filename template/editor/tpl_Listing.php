<?php

class tpl_Listing extends mod_Listing implements tpl_ElementInterface
{
    public function GetContents()
    {
        $output = "<ul>\n";
        foreach ($this->items as $item)
            $output .= "<li>".$item."\n";
        $output .= "</ul>\n\n";
        return $output;
    }
    public function GetForm()
    {
        $output = '<label for="text">Lijstitems:</label>'
                . '<textarea name="text" rows="10" cols="70">';
        foreach ($this->items as $item) {
            $output .= htmlspecialchars($item)."\n";
        }
        $output .= "</textarea>\n";
        return $output;
    }
    public function SetFromForm(array $formdata)
    {
        $this->ResetItems();
        if (isset($formdata['text'])) {
            $items = preg_split("/(\r\n|\n|\r)/", $formdata['text']);
            $this->AddItems($items);
        }
    }
    public static function TypeName()
    {
        return 'Lijst';
    }
    public function SetFromModel(mod_Element $mod_element)
    {
        if (!($mod_element instanceof mod_Listing)) {
            return false;
        }
        $this->AddItems($mod_element->GetItems());
        return true;
    }
}
?>
