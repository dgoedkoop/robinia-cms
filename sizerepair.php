<?php

require('model/database.php');
require('control/options.php');

$db = new PDO('mysql:host=' . $options->GetOption('db_hostname')
            . ';dbname=' . $options->GetOption('db_database')
            . ';charset=utf8',
            $options->GetOption('db_username'),
            $options->GetOption('db_password'));
$tbl_prefix = $options->GetOption('tbl_prefix');

$sql = "SELECT * FROM {$tbl_prefix}image";
$statement = $db->prepare($sql);
$statement->execute();
$update_sql = "UPDATE {$tbl_prefix}image SET width = :width, height = :height "
            . "WHERE element_id = :id";
$update_statement = $db->prepare($update_sql);
while($row = $statement->fetch(PDO::FETCH_ASSOC)) {
    $filename = $row['filename'];
    if (file_exists($filename)) {
        $format = exif_imagetype($filename);
        switch ($format) {
            case IMAGETYPE_PNG:
                $data = @imagecreatefrompng($filename);
                break;
            case IMAGETYPE_JPEG:
                $data = @imagecreatefromjpeg($filename);
                break;
            case IMAGETYPE_GIF:
                $data = @imagecreatefromgif($filename);
                break;
            default: 
                $data = false; 
        }
        if (!($data === false)) {
            $width = imagesx($data);
            $height = imagesy($data);
            $update_statement->bindParam(':width', $width);
            $update_statement->bindParam(':height', $height);
            $update_statement->bindParam(':id', $row['element_id']);
            $update_statement->execute();
        }
    }
}


?>
