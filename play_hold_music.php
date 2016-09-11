<?php
require_once('library/Config.php');
include_once("library/commonFunctions.php");
include_once('library/twilio/Twilio.php');
$message = "play_hold_music.php --- ";
$number= "";
foreach($_REQUEST as $key => $value)
{
	$message.= $key." => ".$value." || ";
}
// 
updateData("insert into twilio_debug set message='".$message."' , time_stamp ='".date("d-m-Y H:i:s")."'");

/*$twilio_creds = getTwlioCreds();
$client = new Services_Twilio($twilio_creds['sid'], $twilio_creds['auth_token']);

$call=$client->account->calls->get($_REQUEST['CallSid']);
$call->update(array(
        "Url" => HTTP_ROOT."dial_number.php?join_to_the_conf=".$_REQUEST['conf_name'],
    	"Method" => "GET"
    ));*/
header('content-type:text/xml');
?>
<Response>
	<Say voice="woman">Your call has been put to hold!</Say>
	<Say voice="woman">Please Wait for some time!</Say>
	<Play loop='0'>http://vishak.com/tscp/hold.mp3</Play>
</Response>