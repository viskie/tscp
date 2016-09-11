<?php
session_start();
include 'library/twilio/Twilio.php';
require_once('library/Config.php');
require_once('library/commonFunctions.php');
require_once('library/groupObject.php');
extract($_REQUEST);
	$message = "call_transfer.php ---- ";
	foreach($_REQUEST as $key => $value)
	{
		$message.= $key." => ".$value." || ";
	}
updateData("insert into twilio_debug set message='".$message."' , time_stamp ='".date("d-m-Y H:i:s")."'");

$twilio_creds=getTwlioCreds();
$client = new Services_Twilio($twilio_creds['sid'], $twilio_creds['auth_token']);

$conf_name = substr($call_to_transfer,-10);
$query = "SELECT agent_callsid from tbconference_ids where conf_name ='".$conf_name."'";
$call_sid=getOne($query);

//updateData("insert into twilio_debug set message=' before group man' , time_stamp ='".date("d-m-Y H:i:s")."'"); 	

$groupObj= new GroupManager();
$my_phone_numbers = $groupObj->getconnectedphonenos();	
$from_phno = $my_phone_numbers[1]['incoming_no'];

//updateData("insert into twilio_debug set message=' before if' , time_stamp ='".date("d-m-Y H:i:s")."'"); 	
if(isset($from) && ($from == 'make_call'))
{
//updateData("insert into twilio_debug set message=' in if' , time_stamp ='".date("d-m-Y H:i:s")."'");
	foreach ($client->account->conferences->getIterator(0, 50, array(
																"Status" => "in-progress",
																"DateCreated" => date('Y-m-d')
																)) as $conference
																) 
	{
		if($conference->friendly_name == $conf_name)
		{	
			$conference->participants->delete($call_sid);
			//updateData("insert into twilio_debug set message='if from delete call:".$call_sid."' , time_stamp ='".date("d-m-Y H:i:s")."'");
			break;
		}
		
	}
	/*$call = $client->account->calls->create( 
					  '+18553634827',  //$from_phno, // From this number // '+18553634827'
					  "client:agent2", //$transfering_to, //$transfering_to, // Call this number
					  HTTP_ROOT.'transfer_call.php?conf_for_make_call=agent2' //.$conf_name
					);
	echo '{"conf_name":"'.$conf_name.'","call_sid":"'.$call->sid.'"}';*/
	exit;				
}
else
{
	
	foreach ($client->account->conferences->getIterator(0, 50, array(
																"Status" => "in-progress",
																"DateCreated" => date('Y-m-d')
																)) as $conference
																) 
	{
		updateData("insert into twilio_debug set message='conf_name=".$conference->friendly_name."' , time_stamp ='".date("d-m-Y H:i:s")."'");
		if($conference->friendly_name == $conf_name)
		{	
			updateData("insert into twilio_debug set message='else from delete call:".$call_sid."' , time_stamp ='".date("d-m-Y H:i:s")."'");
			$conference->participants->delete($call_sid);
			break;
		}
		
	}
	
	if(strlen($to)>10){
		$to = substr($transfering_to,-10);
	}
	if(is_numeric($to))
		$to = $transfering_to;
	else
		$to = "client:".$transfering_to;
		
	updateData("insert into twilio_debug set message='else after to:".$to."' , time_stamp ='".date("d-m-Y H:i:s")."'");

	$call = $client->account->calls->create(
			  $from_phno, //$call_to_transfer, // From this number // '+18553634827'
			  $to, // Call this number
			  HTTP_ROOT.'transfer_call.php?conf_name='.$conf_name,	//'http://vishak.com/tscp/conference_calls/conf_call_init?confname='.$room_id
			  array(
					  	'StatusCallback' => HTTP_ROOT.'dial_number2.php?check_status=true&conf_name='.$conf_name,	//.'&date='.date('YYYY-MM-DD')
						"Method" => "POST"
					  )
			);
	updateData("insert into twilio_debug set message=' call_transfer.php (in else) ".$to.'--'.$conf_name."' , time_stamp ='".date("d-m-Y H:i:s")."'"); 	
}
//$conference_id = getOne("SELECT conference_sid FROM tbconference_ids WHERE agent_callsid =".$call_sid);
//$client->account->conferences->get($conference_id)->participants->delete($call_sid);