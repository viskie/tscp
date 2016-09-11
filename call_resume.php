<?php
session_start();
// Download/Install the PHP helper library from twilio.com/docs/libraries.
// This line loads the library
include 'library/twilio/Twilio.php';
//require('/path/to/twilio-php/Services/Twilio.php');
require_once('library/Config.php');
require_once('library/commonFunctions.php');
$twilio_creds=getTwlioCreds();
// Your Account Sid and Auth Token from twilio.com/user/account
$client = new Services_Twilio($twilio_creds['sid'], $twilio_creds['auth_token']);

$conf_name=$_REQUEST['phone_number'];
$conf_name=substr($conf_name,-10);
$message="Call Resume Request: ".$twilio_creds['sid']." AND ".$twilio_creds['auth_token']." || ";
//echo $message;exit;
foreach ($client->account->conferences->getIterator(0, 50, array("Status" => "in-progress","DateCreated" => date('Y-m-d'))) as $conference){
	if($conference->friendly_name==$conf_name){
//		echo "==".$conf_name;exit;
		$dialed_call_sid=getOne("Select dialed_call_sid from calls_on_hold where phone_number like '%".$conf_name."%'");
		$conference->participants->delete($dialed_call_sid);
		
updateData("insert into twilio_debug set message='Got conf: ".$conf_name."' , time_stamp ='".date("d-m-Y H:i:s")."'");	
if($_REQUEST['from'] != "makecall")
{
		$agent_call_sid=getOne("Select agent_callsid from tbconference_ids where conf_name='".$conf_name."'");
		if($agent_call_sid != "")
			$participant=$conference->participants->get($agent_call_sid)->update(array("Muted" => "False"));
}
//updateData("insert into twilio_debug set message='Muting Participant: ' , time_stamp ='".date("d-m-Y H:i:s")."'");
	}
}

//updateData("insert into twilio_debug set message='".$message."' , time_stamp ='".date("d-m-Y H:i:s")."'");
//echo('Select call_sid from active_calls where number like \'%%'.$_REQUEST['call_to_put_on_hold'].'%\'');exit;
//echo $_REQUEST['call_to_put_on_hold'].$call_sid;exit;
// Get an object from its sid. If you do not have a sid,
// check out the list resource examples on this page
//echo $call_sid;exit;
/*$call = $client->account->calls->get($call_sid);
$call->update(array(
        "Url" => "http://vishak.in/tscp/resume.php"
    ));
echo $call->to;*/