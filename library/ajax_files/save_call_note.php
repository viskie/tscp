<?
	session_start();
	set_time_limit(0);
	if($_SERVER['REQUEST_METHOD']==="POST"){	//print_r($_POST); exit;
		require_once('../REST/rest_functions.php');
		require_once('../DAL.php');
		
		// for adding record in activity
		$dataArray=array();
		/*$dataArray['Call_Notes__c'] = $_REQUEST['note'];
		$dataArray['Call_Type__c'] = 'Testing';
		$dataArray['Lead__c']="00Q5000000fxv11EAA";*/
		
		$dataArray['CallSID__c']=$_REQUEST['call_id'];
		$dataArray['Description']=$_REQUEST['note'];
		$dataArray['WhoId']=$_REQUEST['account_id'];
		$dataArray['ActivityDate']=date('Y-m-d');
		$dataArray['Status']='Completed';
		$dataArray['Subject']="Note";
		//$dataArray['Call_Result__c']= $_REQUEST['selresult'];
		//$dataArray['Call_Disposition__c']= $_REQUEST['seldisposition'];
		//header('Content-type: application/json');
		$resp_array= add_note($dataArray);
		
		
	}
	
?>