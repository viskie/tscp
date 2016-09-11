<?
if (strpos($_SERVER["SCRIPT_NAME"],basename(__FILE__)) !== false)
{
	Header("location: index.php");
}

include_once('DAL.php');
require_once('commonObject.php');

$host = "twisalcp.db.8955426.hostedresource.com";
$user = "twisalcp";
$pass = 'tw!S@l314';
$database = "twisalcp";

define("DOC_ROOT" ,$_SERVER['PWD'].'/html/tscp/library/Cron_Config.php');
//echo "<pre>"; print_r($_SERVER);

mysql_connect($host,$user,$pass,TRUE) or die("could not connect");
mysql_select_db($database) or die("could not select database".$database);
?>