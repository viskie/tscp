<?php
	session_start();
	include_once("../Config.php");
	include_once("../commonFunctions.php");
	include_once('../twilio/Twilio.php');
	extract($_REQUEST);
	
	$message = "end_conference.php --- ";
	foreach($_REQUEST as $key => $value)
	{
		$message.= $key." => ".$value." || ";
	}
	
		
	$twilio_creds = getTwlioCreds();
	$client = new Services_Twilio($twilio_creds['sid'], $twilio_creds['auth_token']); 
	
	if(isset($from) && ($from == "end_call"))
	{
		$conf_name = substr(htmlspecialchars($conf_name_no), -10);
		$arr_details = getData("SELECT transfer_call_sid FROM tbconference_ids WHERE conf_name='".$conf_name."'");
		if($arr_details[0]['transfer_call_sid'] != "")
		{ 
			$call = $client->account->calls->get($arr_details[0]['transfer_call_sid']);
			$call->update(array("Status" => "completed"));
		}
		updateData("insert into twilio_debug set message='".$arr_details[0]['transfer_call_sid'].'--'.$message."' , time_stamp ='".date("d-m-Y H:i:s")."'");		
		exit;		
	}
	else
	{
		$conf_name = substr(htmlspecialchars($number), -10);
		foreach ($client->account->conferences->getIterator(0, 50, array(
																"Status" => "in-progress",
																"DateCreated" => date('Y-m-d')
																)) as $conference
																) 
		{
			if($conference->friendly_name==$conf_name)
			{
				foreach($conference->participants as $participant)
				{
					$conference->participants->delete($participant->call_sid);
					//fwrite($handle, serialize($participant));
				}
			}
			
		}
		$arr_details = getData("SELECT transfer_call_sid FROM tbconference_ids WHERE conf_name='".$conf_name."'");
		if($arr_details[0]['transfer_call_sid'] != "")
		{ 
			$call = $client->account->calls->get($arr_details[0]['transfer_call_sid']);
			$call->update(array("Status" => "completed"));
		}
		exit;
	}
?>