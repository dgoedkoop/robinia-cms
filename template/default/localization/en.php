<?php

require_once 'template/translator.php';

class tpl_Translator_en extends tpl_Translator {
    private $messages = array(
        's_accept'          => 'Accept',
        's_cookiewarning'   => 'This website can show videos from external websites. This can store cookies on your computer.',
        's_error403'        => 'Access denied',
        's_error403text'    => 'Sorry, you are not allowed to see this page.',
        's_error404'        => 'Page not found',
        's_error404text'    => 'Sorry, but the page you are looking for could not be found.',
        's_errorloggedtext' => 'Of course, this should be fixed as soon as possible, so the webmaster has already been notified.',
        's_errorreturnhome' => 'You can go to the <a href="%s">home page</a> and try to find what you are looking for there.',
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