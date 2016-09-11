<?php
include_once("library/Config.php");
include_once("library/commonFunctions.php");
include_once('library/twilio/Twilio.php');

	extract($_REQUEST);
	$message = "dial_number2.php --- ";
	foreach($_REQUEST as $key => $value)
	{
		$message.= $key." => ".$value." || ";
	}		
	//updateData("insert into twilio_debug set message='dial_number2 (top) : ".$message."' , time_stamp ='".date("d-m-Y H:i:s")."'");
	$twilio_creds = getTwlioCreds();
	$client = new Services_Twilio($twilio_creds['sid'], $twilio_creds['auth_token']); 
	header('Content-type: text/xml'); 	
	
	if(isset($for) && ($for == 'conference')) // comes here after modify url call
	{
		
		$conf_name = substr(htmlspecialchars($To), -10);
		$arr_details = getData("SELECT * FROM tbconference_ids WHERE conf_name='".$conf_name."'");
		if(count($arr_details)>0)
		{ 
			$i=0;
			foreach ($client->account->conferences->getIterator(0, 50, array(
																"Status" => "in-progress",
																"DateCreated" => date('Y-m-d')
																)) as $conference
																) 
			{
				if($conference->friendly_name == $conf_name)
				{	
					foreach($conference->participants as $participant)
					{
						$i++;
						updateData("UPDATE tbconference_ids set conference_sid='".$conference->sid."' WHERE conf_name = '".$conf_name."'");
					} 
				}
				
			}
			if($i > 0)
			{
			?>
			<Response>	
				<Dial action="update_caller_entry.php<?php if(isset($call_log_id)){echo "?call_log_id=".$call_log_id;} ?>" method="GET" record="true" callerId="<?php echo $From; ?>"> 
					<Conference startConferenceOnEnter="true" endConferenceOnExit="true">
						 <?php echo $conf_name; ?>
					</Conference>    	       
				</Dial>
			</Response>
			<?php
				updateData("insert into twilio_debug set message='dial_number2 (end conference) :  =".$i.'--'.count($arr_details)."--".$call_log_id.'---'.$message."' , time_stamp ='".date("d-m-Y H:i:s")."'");
			}
		}
		//updateData("insert into twilio_debug set message='dial_number2 (participant) :  =".$i.'--'.count($arr_details)."--".$message."' , time_stamp ='".date("d-m-Y H:i:s")."'");		
	}
	elseif(isset($check_status) && ($check_status == 'true')) // to check status of conference if disconnected by client then delete the conference
	{
		//updateData("insert into twilio_debug set message='dial_number2 (check status) : ".$conf_name.'----'.$message."' , time_stamp ='".date("d-m-Y H:i:s")."'");
		
		//$my_file = 'file.txt';
		//$handle = fopen($my_file, 'w') or die('Cannot open file:  '.$my_file); //implicitly creates file 
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
		//fclose($handle);
		updateData("insert into twilio_debug set message='dial_number2 (check status) : ".$conf_name.'----'.$message."' , time_stamp ='".date("d-m-Y H:i:s")."'");	
		?><Response></Response><?php 	
	}
	elseif(isset($agentconfname)) // save conference id of agent
	{
		updateData("insert into twilio_debug set message='dial_number2 (set conferenceid) : ".$agentconfname.'---'.$ConferenceSid.'--'.$message."' , time_stamp ='".date("d-m-Y H:i:s")."'");
		updateData("UPDATE tbconference_ids set conference_sid='".$ConferenceSid."' WHERE conf_name = '".$agentconfname."' and agent_callsid = '".$CallSid."'");
		?><Response></Response><?php 
	}
	elseif(isset($call_log_id))	// comes here modify url after call to agent
	{
		
		$call = $client->account->calls->get($CallSid);
		$call->update(array(
						"Url" => HTTP_ROOT."dial_number2.php?for=conference&call_log_id=".$call_log_id,
						"Method" => "GET"
						));
		updateData("insert into twilio_debug set message='dial_number2 (modify url) : ".$message."' , time_stamp ='".date("d-m-Y H:i:s")."'");
		?>
        <Say>Hello, We are calling from GaminRide, Please wait.</Say>
        <?php 
	}
?>
