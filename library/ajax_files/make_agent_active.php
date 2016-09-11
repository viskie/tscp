<?php
	session_start();
	include_once("../Config.php");
	extract($_POST);
	
	//$arr_details = getData("SELECT * FROM incoming_calls WHERE phone_number='".$phone_no."'"); 
	
	updateData("UPDATE incoming_calls set is_active=1 WHERE phone_number = '".$phone_no."'");
	
	//$twilio_creds = getTwlioCreds();
	//$client = new Services_Twilio($twilio_creds['sid'], $twilio_creds['auth_token']);
	
	
	//$show_data  = getOne("SELECT incoming_calls FROM is_active WHERE phone_number ='".$phone_no."'");
	//echo $show_data;
	
	
	
?>