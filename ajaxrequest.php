<?php 
require_once('../../../config.php');
global $DB;
$imageregions  = null;
$exists = false;
if($_POST["id"]){   
    $imageregions = $DB->get_record("format_mapedimage_regions",array("id"=>$_POST["id"]));
    $exists = true;
}
else{
    $imageregions = new \stdClass();
}
$imageregions->courseid = $_POST["courseid"];
$imageregions->xleft = $_POST["x"];
$imageregions->ytop = $_POST["y"];
$imageregions->weigth = $_POST["weigth"];
$imageregions->heigth = $_POST["heigth"];
$imageregions->form = $_POST["form"];
$imageregions->href = $_POST["href"];
if($exists){
    $DB->update_record("format_mapedimage_regions",$imageregions);
}
else{
    $imageregions->id = $DB->insert_record("format_mapedimage_regions",$imageregions);
}

echo json_encode($imageregions);
