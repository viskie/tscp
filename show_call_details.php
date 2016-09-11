<?php
// Get the PHP helper library from twilio.com/docs/php/install
require_once('library/twilio/Twilio.php'); // Loads the library
require_once('library/Config.php');
require_once('library/commonFunctions.php');

$twilio_creds=getTwlioCreds();
// Your Account Sid and Auth Token from twilio.com/user/account
$client = new Services_Twilio($twilio_creds['sid'], $twilio_creds['auth_token']);

if(isset($_REQUEST['call_sid'])){
	$call=$client->account->calls->get($_REQUEST['call_sid']);
	var_dump($call);
}else if(isset($_REQUEST['conference_sid'])){
	foreach ($client->account->conferences->get($_REQUEST['conference_sid'])->participants as $participant) {
		echo $participant->sid."<br>";
	}
}else{
	echo "No default set";
}

