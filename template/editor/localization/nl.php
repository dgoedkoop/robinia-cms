<?php

require_once 'template/translator.php';

class tpl_Translator_nl extends tpl_Translator {
    private $messages = array(
        's_add'             => 'Toevoegen',
        's_addright'        => 'Nieuw recht toevoegen',
        's_addrightforuser' => 'Nieuw recht toevoegen voor gebruiker',
        's_addrightforgroup' => 'Nieuw recht toevoegen voor groep',
        's_addsubelements'  => 'Subelement toevoegen',
        's_cancel'          => 'Annuleren',
        's_commit'          => 'Wijzigingen doorvoeren',
        's_confirm'         => 'Bevestigen',
        's_continue'        => 'Verder gaan',
        's_cut'             => 'Knippen',
        's_delete'          => 'Verwijderen',
        's_deletesubelements' => 'Sub-elementen verwijderen',
        's_description'     => 'Beschrijving',
        's_edit'            => 'Bewerken',
        's_editelement'     => 'Element bewerken',
        's_editelementrights' => 'Rechten voor element bewerken',
        's_elementproperties' => 'Eigenschappen voor element',
        's_elementrights'   => 'Rechten voor element',
        's_filename'        => 'Bestandsnaam',
        's_group'           => 'Groep',
        's_groupmembership' => 'Groepslidmaatschap',
        's_groupname'       => 'Naam',
        's_headingtext'     => 'Koptekst',
        's_imgalttext'      => 'Alt-text',
        's_imgcaption'      => 'Onderschrift',
        's_imgchange'       => 'Afbeelding wijzingen',
        's_imgdimensions'   => 'Formaat',
        's_imgupload'       => 'Afbeelding uploaden',
        's_linkname'        => 'Naam voor link',
        's_listitems'       => 'Lijstitems',
        's_loggedinas'      => 'Ingelogd als',
        's_logout'          => 'Uitloggen',
        's_newelement'      => 'Nieuw element',
        's_newelementtype'  => 'Type van het nieuwe element',
        's_open(action)'    => 'Openen',
        's_passwordchangeemptyunchanged' => 'Nieuw wachtwoord (leeg laten om niet te wijzigen)',
        's_paste'           => 'Plakken',
        's_preview'         => 'Voorbeeld',
        's_removeright'     => 'Dit recht verwijderen',
        's_rights(action)'  => 'Rechten bewerken',
        's_rollback'        => 'Wijzigingen ongedaan maken',
        's_save'            => 'Opslaan',
        's_saveadd'         => 'Opslaan en nog eenzelfde element toevoegen',
        's_text'            => 'Tekst',
        's_thisusergroupmembership' => 'Deze gebruiker is lid van de volgende groepen',
        's_thisuserisadmin' => 'Deze gebruiker is een beheerder.',
        's_thisuserstartelement' => 'Het startpunt voor deze gebruiker is het element nummer %s.',
        's_title'           => 'Titel',
        's_toindex'         => 'Terug naar index',
        's_tools'           => 'TOOLS',
        's_typecontainer'   => 'Container',
        's_typefolder'      => 'Map',
        's_typegallery'     => 'Gallerij',
        's_typeheading'     => 'Kop',
        's_typeimage'       => 'Afbeelding',
        's_typelist'        => 'Lijst',
        's_typepage'        => 'Pagina',
        's_typeparagraph'   => 'Alinea',
        's_typepermissiondenied' => 'Onbekend element (toegang geweigerd)',
        's_typetitledescription' => 'Titel en beschrijving',
        's_typeuser'        => 'Gebruiker',
        's_typeusergroup'   => 'Groep',
        's_typevideo'       => 'Video',
        's_undodelete'      => 'Verwijderen ongedaan maken',
        's_undoinsert'      => 'Invoegen ongedaan maken',
        's_url'             => 'URL',
        's_user'            => 'Gebruiker',
        's_userisadmin'     => 'Gebruiker is administrator',
        's_usermenu'        => 'GEBRUIKER',
        's_username'        => 'Naam',
        's_userstartelement' => 'Startpunt in de backend is het element met nummer',
        's_view(action)'    => 'Weergeven',
        's_xpixels'         => '%s x %s pixel',
        'p_xpixels'         => '%s x %s pixels'
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