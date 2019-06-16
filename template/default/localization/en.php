<?php

require_once 'template/translator.php';

class tpl_Translator_en extends tpl_Translator {
    private $messages = array(
        's_accept'          => 'Accept',
        's_cookiewarning'   => 'This website can show videos from external websites. This can store cookies on your computer.',
        's_lastmodified'    => 'Last modified on %s',
        's_untitled'        => 'Untitled',
        's_videonocookies'  => 'The video cannot be displayed because cookies are not accepted.'
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