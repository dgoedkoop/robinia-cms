<?php

abstract class tpl_Translator {
    public function Translate($text, $number = 1)
    {
        return $this->GetText($text, $this->GetPluralityClass($number));
    }
    /* 
     * This function should take the number and return the plurality
     * class, which is then passed into the actual translation
     * function GetText;
     */
    public abstract function GetPluralityClass($number);
    /*
     * Perform the actual translation.
     */
    public abstract function GetText($text, $pluralityclass);
}
?>