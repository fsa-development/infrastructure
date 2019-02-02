<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);
date_default_timezone_set('America/Denver');

//Put this somewhere.. in a like a config structure.
global $db;
global $mysql_db_name;

$mysql_db_name ='FSA_DB';
$db = new mysqli($ip, $username, $pass, $mysql_db_name) or die('Due to an error, a connection to the server could not be made.');


//ini_set('error_log', '/var/log/httpd/error_log_php');

/*
  Approach Context Variables
  TO DO:
  Namespacing,
  Thread-Friendly Context Class
  Static Approach Instance Handler

  Note: how many of these globals still matter?
  How to make them unmatter?
*/

global $ApproachServiceCall;
$ApproachServiceCall = false;

//$RecurseCount=0;
//$ApproachHTML5=true;

//$APPROACH_EDITMODE=false;
$APPROACH_JQUERY_EVENTHANDLING='';
$APPROACH_JQUERY_EVENTS=array();
$APPROACH_REGISTERED_FILES;
//$APPROACH_SAVE_FLAG=array();

//$SiteRoot = str_replace($_SERVER['SCRIPT_NAME'], '', $_SERVER['SCRIPT_FILENAME']);


?>
