<?php

/*
 * Config setting
 */
$thumb_dir = 'thumbnails';

require_once 'imgop.inc';
/*
 * For rights check
 */
require('model/database.php');
require('control/options.php');
require('control/checklogin.php');
require('control/autoload.php');

function CheckRights($fname) {
    global $options;
    
    $realpath = realpath($fname);
    $scriptpath = pathinfo(__FILE__, PATHINFO_DIRNAME);
    if (substr($realpath, 0, strlen($scriptpath)) != $scriptpath) {
        return false;
    }
    
    if (!$options->GetOption('img_check_rights')) {
        return true;
    }
    
    $db = new PDO('mysql:host=' . $options->GetOption('db_hostname')
                . ';dbname=' . $options->GetOption('db_database')
                . ';charset=utf8',
                $options->GetOption('db_username'),
                $options->GetOption('db_password'));

    $mod_db = new mod_Database($options);
    $mod_db->SetDb($db);
    $mod_db->SetRecursive(mod_Database::recursive_no);

    $checklogin = new ctrl_CheckLogin();
    $checklogin->SetDb($mod_db);
    $checklogin->CheckLogin();
    $currentuser = $checklogin->GetCurrentUser();
    if (!is_null($currentuser)) {
        $mod_db->SetCurrentUser($currentuser);
    }

    $tbl_prefix = $options->GetOption('tbl_prefix');

    $sql = "SELECT element_id FROM {$tbl_prefix}image WHERE filename = :filename";
    $statement = $db->prepare($sql);
    $statement->bindParam(':filename', $fname);
    $statement->execute();
    $found = false;
    $mod_db->SetMode(mod_Database::mode_edit);
    while($row = $statement->fetch(PDO::FETCH_ASSOC)) {
        $mod_element = $mod_db->LoadElement($row['element_id']);
        if ($mod_db->IsValidElement($mod_element)) {
            $found = true;
            break;
        }
    }    
    return $found;
}

/*
 * Parse input
 */
parse_str($_SERVER['QUERY_STRING'], $_GET);

if (isset($_GET["id"])) $fname = $_GET["id"];
if (isset($_GET["w"])) $width = $_GET["w"];
if (isset($_GET["h"])) $height = $_GET["h"];

/*
 * Does this file exist at all?
 */
if (!file_exists($fname)) {
    header('HTTP/1.0 404 Not Found');
    echo "<h1>404 Not Found</h1>";
    echo "The page that you have requested could not be found.";
    die;
}

/*
 * Perform rights check
 */
if (!CheckRights($fname)) {
    header('HTTP/1.0 403 Forbidden');
    echo "<h1>403 Forbidden</h1>";
    echo "You do not have access to this file.";
    die;
}

if (!is_dir($thumb_dir) && !file_exists($thumb_dir)) {
    mkdir($thumb_dir, 0700, true);
}

if (isset($width) && isset($height) && $width && $height) {
    $thumb_spec = $width . 'x' . $height;
} elseif (isset($width) && $width) {
    $thumb_spec = 'w' . $width;
} elseif (isset($height) && $height) {
    $thumb_spec = 'h' . $height;
}

if (isset($thumb_spec)) {
    $dest_fname = $thumb_dir . '/' . $thumb_spec . '_' . pathinfo($fname, PATHINFO_BASENAME);
} else {
    $dest_fname = $fname;
}

if (!file_exists($dest_fname)) {
    $img = new ImgImg();
    if (!$img->DoInput ($fname)) {
        header('HTTP/1.0 500 Internal Server Error');
        echo "<h1>500 Internal Server Error</h1>";
        echo "The image could not be read.";
        exit();
    }
    if (isset($width) && isset($height)) {
        $img -> Resize ($width, $height);
    } elseif (isset($width)) {
        $img -> ResizeToWidth ($width);
    } elseif (isset($height)) {
        $img -> ResizeToHeight ($height);
    }
    if ($dest_fname != $fname) {
        $img -> DoOutput ($dest_fname);
    }
}

$imgtype = exif_imagetype($dest_fname);

if ($imgtype === false) {
    header('HTTP/1.0 403 Forbidden');
    echo "<h1>403 Forbidden</h1>";
    echo "You do not have access to this file.";
    die;
}

header("Content-type: " . image_type_to_mime_type($imgtype));

$last_modified = filemtime($dest_fname);
if (!($last_modified === false)) {
    if (isset($_SERVER['HTTP_IF_MODIFIED_SINCE'])) {
        $if_modified_since = strtotime(preg_replace('/;.*$/', '', 
            $_SERVER['HTTP_IF_MODIFIED_SINCE']));
        if ($if_modified_since >= $last_modified) {
            header($_SERVER['SERVER_PROTOCOL'] . ' 304 Not Modified');
            exit();
        }
    }
    header('Last-Modified: '.date('r', $last_modified));
}

readfile ($dest_fname);
?>