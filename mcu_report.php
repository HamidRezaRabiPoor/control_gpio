<?php 
include_once("functions.php");

sensor_insertion($_POST['temperature'], $_POST['humidity'], $_POST['gpio16'], $_POST['gpio5'], $_POST['gpio4'], $_POST['gpio0']);




?>
