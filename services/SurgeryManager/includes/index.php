<?php

// I'm using a separate config file. so pull in those values 
require_once("config.inc.php");

// pull in the file with the database class 
require_once("Database.class.php");
//require_once("surgerymanager/includes/JSON.class.php");
// create the $db object 
$db = new Database(DB_SERVER, DB_USER, DB_PASS, DB_DATABASE);
$clineo_db = new Database(CLINEO_SERVER, CLINEO_USER, CLINEO_PASS, CLINEO_DATABASE);

//$json = new Services_JSON();

$db->connect(); //db connection
$clineo_db->connect(true); //clineo connection
$GLOBALS['db'] = $db;
$GLOBALS['clineo_db'] = $clineo_db;
require_once("functions_generic.php");
$dat = getApptTimes(10, 9, 2008);
print_r($dat);


?>