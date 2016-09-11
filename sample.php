<?php
require_once('library/Config.php');
require_once("library/commonFunctions.php");
require_once('library/twilio/Twilio.php');
/* $message = "sample.php --- ";
foreach($_REQUEST as $key => $value){
	$message.= $key." => ".$value." || ";
}
updateData("insert into twilio_debug set message='".$message."' , time_stamp ='".date("d-m-Y H:i:s")."'");
echo $message;exit;

$twilio_creds = getTwlioCreds();
$client = new Services_Twilio($twilio_creds['sid'], $twilio_creds['auth_token']);

$parent_call_sid=$_REQUEST['sid'];
foreach ($client->account->calls->getIterator(0, 50, array("ParentCallSid" => $parent_call_sid)) as $call){
    var_dump($call);
}
echo "<br /><hr /><br />";
foreach ($client->account->calls as $call) {
    var_dump($call);
	echo "<br />";
}*/
//header('content-type:text/xml');
//<Response></Response>

	echo "On sample.php";
	$twilio_creds = getTwlioCreds();
	$client = new Services_Twilio($twilio_creds['sid'], $twilio_creds['auth_token']);

	$call = $client->account->calls->create(
			  '+14247723149', //$call_to_transfer, // From this number // '+18553634827'
			  "client:agent2", // Call this number
			  "http://vishak.com/LaunchPad/library/twilio/handlers/thankyou.php",	//'http://vishak.com/tscp/conference_calls/conf_call_init?confname='.$room_id
			   array()
			);  
	echo $call->sid;
	exit;

	$conf_name=$_REQUEST['conf'];
	$twilio_creds = getTwlioCreds();
	$client = new Services_Twilio($twilio_creds['sid'], $twilio_creds['auth_token']);
	
	foreach ($client->account->conferences->getIterator(0, 50, array("Status" => "in-progress","DateCreated" => date('Y-m-d'))) as $conference){
		echo "Conf_name: ".$conference->friendly_name." || ";
		if($conference->friendly_name==$conf_name){
			echo "<br> Found Conf: ".$conf_name." || ";
			$agent_call_sid=getOne("Select agent_callsid from tbconference_ids where conf_name='".$conf_name."'");
			echo "<br> Callsid: ".$agent_call_sid;
			$participant=$conference->participants->get($agent_call_sid);
			var_dump($participant);
			$participant->update(array("Muted" => "True"));
			vardump($participant->muted);
			/*foreach($conference->participants as $participant){
				$call=$client->account->calls->get($participant->call_sid);
				$message.="Participant Call sid: ".$participant->call_sid." AND From: ".$call->from." AND To: ".$call->to." || ";
				if(strpos($call->from,'client')!== FALSE){
					$message.="FINALLY GOT HIM!";
					$participant->update(array("Muted" => "True"));
				}
			}*/
		}
	}
?>