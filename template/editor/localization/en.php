<?php

require_once 'template/translator.php';

class tpl_Translator_en extends tpl_Translator {
    private $messages = array(
        's_add'             => 'Add',
        's_addright'        => 'Add new permission',
        's_addrightforuser' => 'Add new permission for user',
        's_addrightforgroup' => 'Add new permission for group',
        's_addsubelements'  => 'Add sub-elements',
        's_cancel'          => 'Cancel',
        's_commit'          => 'Commit changes',
        's_confirm'         => 'Confirm',
        's_continue'        => 'Continue',
        's_cut'             => 'Cut',
        's_delete'          => 'Delete',
        's_deletesubelements' => 'Delete sub-elements',
        's_description'     => 'Description',
        's_edit'            => 'Edit',
        's_editelement'     => 'Edit element',
        's_editelementrights' => 'Modify permissions for element',
        's_elementproperties' => 'Element properties',
        's_elementrights'   => 'Permissions for element',
        's_filename'        => 'File name',
        's_group'           => 'Group',
        's_groupmembership' => 'Group membership',
        's_groupname'       => 'Group name',
        's_headingtext'     => 'Heading text',
        's_imgalttext'      => 'Alt text',
        's_imgcaption'      => 'Caption',
        's_imgchange'       => 'Change image',
        's_imgdimensions'   => 'Dimensions',
        's_imgupload'       => 'Upload image',
        's_linkname'        => 'Name for link',
        's_listitems'       => 'List items',
        's_loggedinas'      => 'Logged in as',
        's_logout'          => 'Logout',
        's_newelement'      => 'New element',
        's_newelementtype'  => 'Type of the new element',
        's_open(action)'    => 'Open',
        's_passwordchangeemptyunchanged' => 'New password (leave empty to leave it unchanged)',
        's_paste'           => 'Paste',
        's_preview'         => 'Preview',
        's_removeright'     => 'Remove this permission',
        's_rights(action)'  => 'Modify permissions',
        's_rollback'        => 'Rollback changes',
        's_save'            => 'Save',
        's_saveadd'         => 'Save and add another element of the same type',
        's_text'            => 'Text',
        's_thisusergroupmembership' => 'This user member of the following groups',
        's_thisuserisadmin' => 'This user is an administrator.',
        's_thisuserstartelement' => 'The root element for this user is %s.',
        's_title'           => 'Title',
        's_toindex'         => 'Return to index',
        's_tools'           => 'TOOLS',
        's_typecontainer'   => 'Container',
        's_typefolder'      => 'Folder',
        's_typegallery'     => 'Gallery',
        's_typeheading'     => 'Heading',
        's_typeimage'       => 'Image',
        's_typelist'        => 'List',
        's_typepage'        => 'Page',
        's_typeparagraph'   => 'Paragraph',
        's_typepermissiondenied' => 'Unknown element (permission denied)',
        's_typetitledescription' => 'Title and description',
        's_typeuser'        => 'User',
        's_typeusergroup'   => 'User group',
        's_typevideo'       => 'Video',
        's_undodelete'      => 'Undo deletion',
        's_undoinsert'      => 'Undo insertion',
        's_url'             => 'URL',
        's_user'            => 'User',
        's_userisadmin'     => 'User is administrator',
        's_usermenu'        => 'USER',
        's_username'        => 'User name',
        's_userstartelement' => 'Starting point in the backend is element with number',
        's_view(action)'    => 'View',
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