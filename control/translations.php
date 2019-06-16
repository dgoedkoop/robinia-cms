<?php

class ctrl_TranslationDirector extends Singleton {
    private $defaultlanguage;
    private $defaulttranslator = null;
    private $translator = null;

    public function __construct($defaultlanguage = 'en')
    {
        $options = mod_Options::instance();
        $this->defaultlanguage = $defaultlanguage;
        require_once('template/'.$options->GetOption('template').'/localization/'.$this->defaultlanguage.'.php');
        $classname = 'tpl_Translator_'.$this->defaultlanguage;
        $this->defaulttranslator = new $classname();
        $language = $options->GetOption('language');
        if (($language != $this->defaultlanguage) &&
            @include_once('template/'.$options->GetOption('template').'/localization/'.$language.'.php')) {
            $classname = 'tpl_Translator_'.$language;
            $this->translator = new $classname();
        }
    }

    public function Translate($text, $number = 1)
    {
        $translation = null;
        if (!is_null($this->translator)) {
            $translation = $this->translator->Translate($text, $number);
        }
        if (is_null($translation)) {
            $translation = $this->defaulttranslator->Translate($text, $number);
        }
        if (!is_null($translation)) {
            return $translation;
        } else {
            return $text;
        }
    }
}

function tr($text, $number = 1)
{
    return ctrl_TranslationDirector::instance()->Translate($text, $number);
}

?>