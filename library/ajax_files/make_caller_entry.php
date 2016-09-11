<?php
	session_start();
	extract($_POST);
	//if($_SERVER['REQUEST_METHOD']==="POST"){
		require_once('../salesforceLocalObject.php');
		$salesforceLocalObject=new SalesForceLocalManager();
		
		$message = "dial_number2.php --- ";
		foreach($_REQUEST as $key => $value)
		{
			$message.= $key." => ".$value." || ";
		}
		updateData("insert into twilio_debug set message='make caller entry (top):".$message."' , time_stamp ='".date("d-m-Y H:i:s")."'");	
		if($_REQUEST['call_type'] && $_REQUEST['call_type'] == 'incoming')
		{	
			if($_REQUEST['account_id'] != "")
			{	
				$call_details=$salesforceLocalObject->getLatestCallFromNumber($_REQUEST['from']);
				$call_id=$call_details['call_id'];
				//echo "all right";exit;
				$call_variables=array();
				$call_variables['has_to_sync']='1';
				$call_variables['account_id']=$_REQUEST['account_id'];
				$call_variables['call_id']=$call_id;
				$salesforceLocalObject->updateCallVariablesById($call_variables);
				updateData("insert into twilio_debug set message='make caller entry (incoming - call id):".$call_id."' , time_stamp ='".date("d-m-Y H:i:s")."'");	
			}
		}
		else
		{
			$dataArray=array();
			$dataArray['account_id']=$_REQUEST['account_id'];
			if($_REQUEST['account_id']!='0')
			{
				$dataArray['has_to_sync']='1';
			}
			$dataArray['call_to']=$_REQUEST['to'];
			if(isset($_REQUEST['call_id']))
			{
				$dataArray['call_id']=$_REQUEST['call_id'];
				$dataArray['call_from']=$_REQUEST['from'];
			}
			$id=$salesforceLocalObject->insertCallVariables($dataArray);

			header('Content-type: application/json');
			echo '{"done":true,"id":"'.$id.'"}';
		}
		
	//}
?>