<?php session_start();
	
	
	require_once('library/salesforceLocalObject.php');
	$salesforceLocalObject=new SalesForceLocalManager();
	$message = "update_caller_entry.php ---- CALLBACK==> ";
	foreach($_REQUEST as $key => $value)
	{
		$message.= $key." => ".$value." || ";
	}
	$number='';
	updateData("insert into twilio_debug set message='".$message."' , time_stamp ='".date("d-m-Y H:i:s")."'");
	$dataArray=array();
	$dataArray['call_sid']=$_REQUEST['CallSid'];
	$dataArray['recording_url']=$_REQUEST['RecordingUrl'];
	$dataArray['call_duration']=$_REQUEST['RecordingDuration'];
	//$dataArray['is_active']='0';
	if(isset($_REQUEST['call_log_id']) || (isset($_REQUEST['incoming_call_log_id'])))
	{
		if(isset($_REQUEST['call_log_id']))
			$dataArray['call_id']=$_REQUEST['call_log_id'];
		else
			$dataArray['call_id']=$_REQUEST['incoming_call_log_id'];
		$dataArray['call_from']=$_REQUEST['From'];
		$number=$_REQUEST['To'];
		$salesforceLocalObject->updateCallVariablesById($dataArray);
		//updateData("insert into twilio_debug set message='test recording url incoming ".$message."' , time_stamp ='".date("d-m-Y H:i:s")."'");			
		//updateData("DELETE FROM tbconference_ids WHERE conference_sid ='".$_REQUEST['ConferenceSid']."'");		
	}
	else
	{
		$dataArray['call_sid']=$_REQUEST['CallSid'];
		$number=$_REQUEST['From'];
		$salesforceLocalObject->updateCallVariablesByCallId($dataArray);
	}
	
	// for adding entry in salesforce calls	for incoming call
	if(isset($_REQUEST['incoming_call_log_id']))
	{
		$arr_details  = getData("SELECT * FROM salesforce_calls WHERE call_id ='".$_REQUEST['incoming_call_log_id']."'");
		$chk_save_sidebar_entry = $arr_details[0]['is_comingfrom_savesidebar'];	
		updateData("insert into twilio_debug set message=' update caller entry : incoming ".$message."' , time_stamp ='".date("d-m-Y H:i:s")."'");			
	}
	else	// for adding entry in salesforce calls	for outgoing call
	{		
		$chk_save_sidebar_entry  = getOne("SELECT is_comingfrom_savesidebar FROM salesforce_calls WHERE call_sid ='".$_REQUEST['CallSid']."'");	
		$arr_details = getData("SELECT * FROM salesforce_calls WHERE call_sid='".$_REQUEST['CallSid']."'"); 
	}
	if($chk_save_sidebar_entry == 1)
	{			
		$arr_calls = array();
		//$agent_name = str_replace("client:", "", $arr_details[0]['call_from']);
		//$arr_calls['Agent__c'] = $agent_name;
		$arr_calls['Call_Duration__c'] = $arr_details[0]['call_duration'];
		$arr_calls['Caller_s_Number__c'] = $arr_details[0]['call_from'];		
		$arr_calls['Caller_Disposition__c'] = $arr_details[0]['call_disposition'];
		$arr_calls['Call_Notes__c'] = $arr_details[0]['note'];
		$arr_calls['Call_Result__c'] = $arr_details[0]['call_result'];		
		$arr_calls['Call_SID__c'] = $_REQUEST['CallSid'];
		$arr_calls['Date_Time_of_Call__c'] = date("Y-m-d\TH:i:s\Z");
		//$arr_calls['Call_Subject__c']=$_REQUEST['call_id'];
		
		if(isset($_REQUEST['call_log_id']))
		{
			$arr_calls['Call_Type__c']= 'Outgoing';			
		}
		else
		{
			$arr_calls['Call_Type__c']= 'Incoming';	
		}
		$arr_calls['Lead__c'] = $arr_details[0]['account_id'];
		$arr_calls['Franchise_ID__c'] = $arr_details[0]['franchise_id'];
		$arr_calls['Recording_Link__c'] = $arr_details[0]['recording_url'];
		$arr_calls['Related_To__c'] = 'Lead';
		//updateData("insert into twilio_debug set message='inside loop' , time_stamp ='".date("d-m-Y H:i:s")."'"); 		 
		$call_response_array = add_calls($arr_calls); 
		
		updateData("insert into twilio_debug set message=' sync update caller".addslashes($call_response_array)."' , time_stamp ='".date("d-m-Y H:i:s")."'"); 			
		//$call_response = json_decode($resp_array, true);
		$salesforceLocalObject->syncCalls();
	}
		
	$number=substr($number,-10);

	updateData("Delete from calls_on_hold where phone_number like '%".$number."%'");	
	updateData("Delete from active_calls where call_sid='".$_REQUEST['CallSid']."'");
	header("content-type:application/xml");
?>
<Response></Response>