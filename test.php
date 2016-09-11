<? 
	require_once('library/salesforceLocalObject.php');
	$salesforceLocalObject=new SalesForceLocalManager();
	
	$arr_calls = array();
	//$agent_name = str_replace("client:", "", $arr_details[0]['call_from']);
	//$arr_calls['Agent__c'] = $agent_name;
	$arr_calls['Call_Duration__c'] = 10;
	//$arr_calls['Caller_s_Number__c'] = $arr_details[0]['call_from'];		
	$arr_calls['Caller_Disposition__c'] = 'Happy';
	$arr_calls['Call_Notes__c'] = 'TEST NOTES';
	$arr_calls['Call_Result__c'] = 'Availability Checked';		
	$arr_calls['Call_SID__c'] ='CA56f815d7834fc55916b05d5d22204ee2';
	$setdate = date('YYYY-MM-DD');
	$settime = date('H:i:s');
	$arr_calls['Date_Time_of_Call__c'] = date('Y-m-d H:i:s'); //date('YYYY-MM-DDThh:mm:ssZ ');				
	//$arr_calls['Call_Subject__c']=$_REQUEST['call_id'];
	$arr_calls['Call_Type__c']= 'Outgoing';			
	$arr_calls['Lead__c'] = '00Q5000000fxv11EAA';
	$arr_calls['Franchise_ID__c'] = '001';
	//$arr_calls['Recording_Link__c'] = $arr_details[0]['recording_url'];
	$arr_calls['Related_To__c'] = 'Lead';
	//updateData("insert into twilio_debug set message='inside loop' , time_stamp ='".date("d-m-Y H:i:s")."'"); 		 
	$call_response_array = add_calls($arr_calls); 
?>
