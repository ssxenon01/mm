<?php
/*
	Template Name: Sabai IMG redirect
*/
$img = $_GET['img_id'];
if(!$img)
    http_response_code(204);
else{

    global $wpdb;


    $id = $wpdb->get_row("SELECT f.file_name as id FROM menu_sabai_entity_field_file_image a , menu_sabai_file_file f WHERE f.file_id = a.file_id AND a.entity_id = $img " , OBJECT)->id;


    header("Location: http://mymenu.mn/resources/sabai/File/thumbnails/$id");
    die();

}

?>

