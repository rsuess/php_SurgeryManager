<?php

/// connection to main database
DEFINE('DB_SERVER', "localhost");
DEFINE('DB_USER', "");
DEFINE('DB_PASS', "");
DEFINE('DB_DATABASE', "");


///Connection to clineo database
DEFINE('CLINEO_SERVER', '192.168.0.99');
DEFINE('CLINEO_USER', '');
DEFINE('CLINEO_PASS', '');
DEFINE('CLINEO_DATABASE', '');


// pull in the file with the database class
require_once("Database.class.php");
//require_once("surgerymanager/includes/JSON.class.php");
// create the $db object 
$db = new Database(DB_SERVER, DB_USER, DB_PASS, DB_DATABASE);
$clineo_db = new Database(CLINEO_SERVER, CLINEO_USER, CLINEO_PASS, CLINEO_DATABASE);
$db->connect(true); //db connection
$clineo_db->connect(true); //clineo connection

$GLOBALS['db'] = $db;
$GLOBALS['clineo_db'] = $clineo_db;
?>