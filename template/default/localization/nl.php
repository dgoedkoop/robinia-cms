<?php

require_once 'template/translator.php';

class tpl_Translator_nl extends tpl_Translator {
    private $messages = array(
        's_accept'          => 'Accepteren',
        's_cookiewarning'   => 'Deze website kan video\'s van een externe website tonen. Daarbij kan een cookie op uw computer worden opgeslagen.',
        's_error403'        => 'Toegang geweigerd',
        's_error403text'    => 'Het is niet toegestaan deze pagina te bekijken. Ons excuses voor het ongemak.',
        's_error404'        => 'Pagina niet gevonden',
        's_error404text'    => 'De pagina kon niet worden gevonden. Ons excuses voor het ongemak.',
        's_errorloggedtext' => 'Dit moet natuurlijk zo snel mogelijk worden opgelost, dus de webmaster is al op de hoogte gesteld.',
        's_errorreturnhome' => 'Je kan terug naar de <a href="%s">voorpagina</a> gaan en van daaruit proberen te vinden wat je zoekt.',
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