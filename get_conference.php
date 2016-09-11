<?php
	
	include_once("library/Config.php");
	include_once('library/twilio/Twilio.php');
	include_once("library/commonFunctions.php");
	
	$twilio_creds = getTwlioCreds();
	$client = new Services_Twilio($twilio_creds['sid'], $twilio_creds['auth_token']); 
	//print_r($twilio_creds);
	
 	echo "All in-progress conferences : ";
	echo "<br/>";
	$j=0;
	foreach ($client->account->conferences->getIterator(0, 50, array(
																"Status" => "in-progress",
																"DateCreated" => date('Y-m-d')
																)) as $conference
																) 
	{
		echo ($j+1).') '.$conference->sid.'###'. $conference->friendly_name;
		echo "<br/>";
		echo "Participants : ";
		echo "<br/>";
		$i = 0;
		foreach($conference->participants as $participant)
		{
			$i++;
			echo $i." .".$participant->call_sid." || muted: ".$participant->muted;
			echo "<br/>Call Details:<br>";
			$call=$client->account->calls->get($participant->call_sid);
			echo "From: ".$call->from." || TO: ".$call->to." || Direction: ".$call->direction." || Caller name: ".$call->caller_name;
			echo "<br><hr><br>";
		}
		echo "Total : ".$i;
		echo "<br/>";
		$j++;	
	}
	
	
	echo "All Calls :";
	echo "<br/>";
	foreach ($client->account->calls->getIterator(0, 50, array("Status" => "in-progress")) as $call)
	{
    	echo "SID: ".$call->sid." || From: ".$call->from." || To: ".$call->to."<br>";
	}

	
?>