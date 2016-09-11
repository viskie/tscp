<?php
	session_start();
	include_once("../Config.php");
	include_once("../commonFunctions.php");
	include_once("../DAL.php");
	require_once('../REST/rest_functions.php');
	extract($_POST);
	
	$message = "save_sidebar_note.php --- ";
	foreach($_REQUEST as $key => $value)
	{
		$message.= $key." => ".$value." || ";
	}
	if($calltype == 'incoming')
		$condition = " call_from = '".$from."' and has_to_sync=1 ORDER BY call_id DESC ";
	else
		$condition = " call_id = '".$callid."'";
	
	$chk_update_caller_entry  = getOne("SELECT recording_url FROM salesforce_calls WHERE ".$condition);	 
	$updateQuery = "update salesforce_calls set note = '".$note."',	call_result = '".$result."', call_disposition = '".$disposition."',	franchise_id = '".$selfranchiseid."', 					is_comingfrom_savesidebar = 1 where ".$condition;
	updateData($updateQuery);
	
	if($chk_update_caller_entry != '')
	{	
		require_once('../salesforceLocalObject.php');
		$salesforceLocalObject=new SalesForceLocalManager();
		$salesforceLocalObject->syncCalls();
		
		// for adding record in calls
		$arr_details = getData("SELECT * FROM salesforce_calls WHERE ".$condition);
		updateData("insert into twilio_debug set message='save sidebar note 2:".$arr_details[0]['call_duration'].'--'.$message."' , time_stamp ='".date("d-m-Y H:i:s")."'");		
		$arr_calls = array();
		//$agent_name = str_replace("client:", "", $arr_details[0]['call_from']);
		//$arr_calls['Agent__c'] = $agent_name;
		$arr_calls['Call_Duration__c'] = $arr_details[0]['call_duration'];
		$arr_calls['Caller_s_Number__c'] = $arr_details[0]['call_from'];		
		$arr_calls['Caller_Disposition__c'] = $arr_details[0]['call_disposition'];
		$arr_calls['Call_Notes__c'] = $arr_details[0]['note'];
		$arr_calls['Call_Result__c'] = $arr_details[0]['call_result'];		
		$arr_calls['Call_SID__c'] = $arr_details[0]['call_sid'];
		$arr_calls['Date_Time_of_Call__c'] = date("Y-m-d\TH:i:s\Z");
		//$arr_calls['Call_Subject__c']=$_REQUEST['call_id'];
		//$arr_calls['Call_Type__c']=$_REQUEST['call_id'];
		require_once('../groupObject.php');
		$groupObj= new GroupManager();
		$my_phone_numbers = $groupObj->getconnectedphonenos();	
		if(multi_in_array($arr_details[0]['call_from'],$my_phone_numbers))
			$arr_calls['Call_Type__c']= 'Outgoing';	
		else
			$arr_calls['Call_Type__c']= 'Incoming';	
		//$arr_calls['Call_Type__c'] = $calltype;	
		$arr_calls['Lead__c'] = $arr_details[0]['account_id'];
		$arr_calls['Franchise_ID__c'] = $arr_details[0]['franchise_id'];
		$arr_calls['Recording_Link__c'] = $arr_details[0]['recording_url'];
		$arr_calls['Related_To__c'] = 'Lead';
		//echo "<pre>"; print_r($arr_calls);
		$call_response_array= add_calls($arr_calls);
		//$call_response = json_decode($resp_array, true);		
	}	
	
	
?>