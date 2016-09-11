<?php
// Get the PHP helper library from twilio.com/docs/php/install
require_once('library/twilio/Twilio.php'); // Loads the library
require_once('library/Config.php');
require_once('library/groupObject.php');
require_once('library/commonFunctions.php');

$message = "change_call_url.php --- ";
$number= "";
foreach($_REQUEST as $key => $value)
{
	$message.= $key." => ".$value." || ";
}
// 
updateData("insert into twilio_debug set message='".$message."' , time_stamp ='".date("d-m-Y H:i:s")."'");


$groupObj= new GroupManager();
$my_phone_numbers = $groupObj->getconnectedphonenos();
$number=$my_phone_numbers[0]['incoming_no'];

$hold_seperate_number='+14247723144';

$twilio_creds=getTwlioCreds();
// Your Account Sid and Auth Token from twilio.com/user/account
$client = new Services_Twilio($twilio_creds['sid'], $twilio_creds['auth_token']);

$url =  HTTP_ROOT.'play_hold_music.php?conf_name='.substr($_REQUEST['phone_number'],-10);
	 
//$tonum = substr(htmlspecialchars($number), -10);
		
	$call = $client->account->calls->create(
					  $hold_seperate_number, // From this number // '+18553634827'
					  $number, // Call this number
					  $url	//'http://vishak.com/tscp/conference_calls/conf_call_init?confname='.$room_id
					);
					echo $call->sid;
				
updateData("Delete from calls_on_hold where phone_number like '%".substr($_REQUEST['phone_number'],-10)."%'");				
updateData("insert into calls_on_hold (`phone_number`,`call_from`,`call_to`) values(NULL,'".$_REQUEST['phone_number']."','".$hold_seperate_number."','".$number."')");