<?php
// Get the PHP helper library from twilio.com/docs/php/install
require_once('library/twilio/Twilio.php'); // Loads the library
require_once('library/Config.php');
require_once('library/groupObject.php');
require_once('library/commonFunctions.php');

$message = "cancel_All_current_calls.php --- ";
$number= "";
foreach($_REQUEST as $key => $value)
{
	$message.= $key." => ".$value." || ";
}
// 
updateData("insert into twilio_debug set message='".$message."' , time_stamp ='".date("d-m-Y H:i:s")."'");

$twilio_creds=getTwlioCreds();
// Your Account Sid and Auth Token from twilio.com/user/account
$client = new Services_Twilio($twilio_creds['sid'], $twilio_creds['auth_token']);
echo "All calls";
foreach ($client->account->calls->getIterator(0, 50, array("Status" => "in-progress")) as $call){
    echo "SID: ".$call->sid." || From: ".$call->from." || To: ".$call->to."<br>";
	$call->update(array("Status" => "completed"));
}

/*echo "All Calls : ";
foreach ($client->account->calls as $call) {
	if($call->Status == 'in-progress')
	{
		echo $call->sid;
		$call->update(array("Status" => "completed"));
		echo "<br/>";
	}
}*/