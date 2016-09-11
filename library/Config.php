<?php
if (strpos($_SERVER["SCRIPT_NAME"],basename(__FILE__)) !== false)
{
	Header("location: index.php");
}

include_once('DAL.php');
require_once('commonObject.php');

$host = "";
$user = "";
$pass = "";
$database = "";

//echo "<pre>"; print_r($_SERVER);
//print_r($_SERVER['DOCUMENT_ROOT'].'/tscp/library/Cron_Config.php'); exit;
//trigger_error('My PHP Version: ' . phpversion(), E_USER_NOTICE);
//echo  phpversion();
if($_SERVER['HTTP_HOST']=='showroom')
{
	$host="mydb";
	$user="root";
	$pass="ci52.906";
	$database="tscp";
	
	define('HTTP_ROOT','http://showroom/tscp/Code/tscp/');	
}
else if(($_SERVER['HTTP_HOST']=='app-server') || ($_SERVER['HTTP_HOST']=='10.20.30.40')){
	$host="localhost";
	$user="root";
	$pass="sat_dev_321";
	$database="tscp";
}else if(($_SERVER['HTTP_HOST']=='www.vishak.in') ||($_SERVER['HTTP_HOST']=='vishak.in') ||($_SERVER['HTTP_HOST']=='http://vishak.in') ){
	
	$host="localhost";
	$user="vishakin_tscp";
	$pass="EU~ux;C7Qsn$";
	$database="vishakin_tscp";
	
	define('HTTP_ROOT','http://vishak.in/tscp/');
}
else if(($_SERVER['HTTP_HOST']=='www.vishak.com') ||($_SERVER['HTTP_HOST']=='vishak.com') ||($_SERVER['HTTP_HOST']=='http://vishak.com') ){

	$host="twisalcp.db.8955426.hostedresource.com";
	$user="twisalcp";
	$pass="tw!S@l314";
	$database="twisalcp";
	
	define('HTTP_ROOT','http://vishak.com/tscp/');
}
else{
	$host="localhost";
	$user="root";
	$pass="sat_dev_321";
	$database="tscp";
}
if(!isset($db_connection) || !mysql_ping($db_connection))
{
	$db_connection=mysql_connect($host,$user,$pass,TRUE) or die("could not connect"); //echo $db_connection;
	mysql_select_db($database) or die("could not select database".$database);
}


$arr_officeuseno = array('+14247723144');
$hold_seperate_number = $arr_officeuseno[0]; //+14247723144 use for hold functionality

?>