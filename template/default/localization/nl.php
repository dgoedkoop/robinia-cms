<?php

require_once 'template/translator.php';

class tpl_Translator_nl extends tpl_Translator {
    private $messages = array(
        's_accept'          => 'Accepteren',
        's_cookiewarning'   => 'Deze website kan video\'s van een externe website tonen. Daarbij kan een cookie op uw computer worden opgeslagen.',
        's_lastmodified'    => 'Laatst gewijzigd op %s',
        's_untitled'        => 'Naamloos',
        's_videonocookies'  => 'De video kan niet worden getoond omdat cookies niet worden geaccepteerd.'
    );

    public function GetPluralityClass($number)
    {
        if (($number == 1) || ($number == -1)) {
            return 's';
        } else {
            return 'p';
        }
    }

    public function GetText($text, $pluralityclass)
    {
        if (isset($this->messages[$pluralityclass.'_'.$text])) {
            return $this->messages[$pluralityclass.'_'.$text];
        } else {
            return null;
        }
    }
}

?>