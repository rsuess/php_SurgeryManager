<?

//echo("the datestamp is : ".$_REQUEST['datestamp']);

$actiontime = $_REQUEST['datestamp'];

$phptime = $actiontime/1000;

print_r($actiontime);
echo("PHP TIME IS ".$phptime."\r\n");
//print_r(date('r', $actiontime));


?>