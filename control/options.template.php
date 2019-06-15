<?php

require_once 'model/options.php';

$options = new mod_Options();
$options->SetOptions(array('db_hostname'     => 'localhost',
                           'db_database'     => '',
                           'db_username'     => '',
                           'db_password'     => '',
                           'tbl_prefix'      => 'dcm_',
                           'basepath'        => 'http://site/',
                           'startpage'       => 'index.php?c=page&a=page&id=1',
                           'template'        => 'default',
                           'site_title'      => "",
                           'img_upload_dir'  => '/home/user/public_html/uploads/images',
                           'img_upload_webpath' => 'uploads/images/',
                           'img_check_rights'=> false,
                           'img_lightbox'    => true,
                           'classlist' => array('Paragraph', 'Heading',
                               'Listing', 'ImageGallery', 'Image', 'Video',
                               'Container', 'TitleDescription', 'Folder',
                               'Page', 'User', 'Usergroup')));

?>
