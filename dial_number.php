<?php
include_once("library/Config.php");
include_once('library/twilio/Twilio.php');
include_once("library/commonFunctions.php");
//include_once("library/salesforceLocalObject.php");
//$salesforceLocalObject=new SalesForceLocalManager();
//$twilio_variables=$salesforceLocalObject->getTwillioIncomingVariables();
//$salesforceLocalObject->insertCallVariables($twilio_variables);

header('Content-type: text/xml');

$message = "dial_number.php --- ";
$number= "";
foreach($_REQUEST as $key => $value)
{
	$message.= $key." => ".$value." || ";
}
// 
updateData("insert into twilio_debug set message='".$message."' , time_stamp ='".date("d-m-Y H:i:s")."'");
require_once('library/groupObject.php');
require_once('library/salesforceLocalObject.php');

//$hold_seperate_number = $arr_officeuseno[0];


$groupObj= new GroupManager();
$my_phone_numbers = $groupObj->getconnectedphonenos();


if($_REQUEST['From']==$hold_seperate_number) // comes in for call hold condition
{
	$conf_name=getOne("SELECT `phone_number` from `calls_on_hold` where `call_from`='".$hold_seperate_number."' AND call_to='".$_REQUEST['To']."' AND is_active=1");
	updateData("UPDATE calls_on_hold set is_active='2',dialed_call_sid='".$_REQUEST['CallSid']."' WHERE `phone_number`='".$conf_name."'");
	$conf_name=substr($conf_name,-10);
?>
	<Response>
		<Dial>
			<Conference beep='false'><?php echo $conf_name; ?></Conference>
		</Dial>
	</Response>
<?php
	$twilio_creds = getTwlioCreds();
	$client = new Services_Twilio($twilio_creds['sid'], $twilio_creds['auth_token']);
	foreach ($client->account->conferences->getIterator(0, 50, array("Status" => "in-progress","DateCreated" => date('Y-m-d'))) as $conference){
		if($conference->friendly_name==$conf_name){
			$agent_call_sid=getOne("Select agent_callsid from tbconference_ids where conf_name='".$conf_name."'");
			$participant=$conference->participants->get($agent_call_sid);
			$participant->update(array("Muted" => "True"));
		}
	}
}else if(isset($_REQUEST['transfering_to']) && ($_REQUEST['transfering_to'] != "")) // comes in for call tranfer condition's make call option
{
	$conf_name = substr($_REQUEST['conf_name_no'],-10); 
	$str = "UPDATE tbconference_ids set transfer_call_sid=\'".$_REQUEST['CallSid']."\' WHERE conf_name = \'".$conf_name."\'";
	//updateData("insert into twilio_debug set message=' in make call loop : ".$str."----".$message."' , time_stamp ='".date("d-m-Y H:i:s")."'");
	//updateData("UPDATE tbconference_ids set transfer_call_sid='CA7ee82913640d0230ace69a20bdd46375' WHERE `conf_name`='3023640145'");
	updateData("UPDATE tbconference_ids set transfer_call_sid='".$_REQUEST['CallSid']."' WHERE `conf_name`='".trim($conf_name)."'");
	
	updateData("insert into twilio_debug set message=' in make call loop : ".$str."###".$conf_name.'--'.$message."' , time_stamp ='".date("d-m-Y H:i:s")."'");
	if($_REQUEST['agentname'] != "")	// for call to twilio other agent
	{
	?>
        <Response>
             <Dial>
                <Client>
                    <?php echo $_REQUEST['agentname']; ?>
                </Client>  
            </Dial>
        </Response>
    <?php
	}
	else
	{
	?>
        <Response>
             <Dial>
                <Number>
                    <?php echo $_REQUEST['transfering_to']; ?>
                </Number>  
            </Dial>
        </Response>
    <?php	
	}
}
else if(isset($_REQUEST['To']) && multi_in_array($_REQUEST['To'],$my_phone_numbers)) // comes in for incoming calls
{
	$number=$_REQUEST['From'];
	$number_array=array($number);
	array_push($number_array,'('.substr($number,-10,3).') '.substr($number,-7,3).'-'.substr($number,-4)); //(862) 220-8402
	array_push($number_array,'('.substr($number,-10,3).')'.substr($number,-7,3).'-'.substr($number,-4)); //(862)220-8402
	array_push($number_array,'('.substr($number,-10,3).')'.substr($number,-7,3).' '.substr($number,-4)); //(862)220 8402
	array_push($number_array,'('.substr($number,-10,3).') '.substr($number,-7,3).' '.substr($number,-4)); //(862) 220 8402
	array_push($number_array,'('.substr($number,-10,3).') '.substr($number,-7,3).substr($number,-4)); //(862) 2208402
	array_push($number_array,substr($number,-10,3).'-'.substr($number,-7,3).'-'.substr($number,-4)); //862-220-8402
	array_push($number_array,substr($number,-10,3).'-'.substr($number,-7,3).substr($number,-4)); //862-2208402
	array_push($number_array,substr($number,-10,3).'-'.substr($number,-7,3).' '.substr($number,-4)); //862-220 8402
	array_push($number_array,substr($number,-10,3).' '.substr($number,-7,3).' '.substr($number,-4)); //862 220 8402
	array_push($number_array,substr($number,-10,3).substr($number,-7,3).'-'.substr($number,-4)); //862220-8402
	array_push($number_array,substr($number,-10,3).' '.substr($number,-7,3).substr($number,-4)); //862 2208402
	array_push($number_array,substr($number,-10,3).' '.substr($number,-7,3).'-'.substr($number,-4)); //862 220-8402
	if(strlen($number)>10){
		array_push($number_array,substr($number,-11,4).'-'.substr($number,-7,3).'-'.substr($number,-4)); //7862-220-8402
		array_push($number_array,substr($number,-10));
	}
	
	$query="SELECT CONCAT(FirstName,' ',LastName) as name, Id from salesforce_leads where Phone!='' AND (";
	foreach($number_array as $number){
		$query.="Phone = '".$number."' OR ";
	}
	$query.="Phone like '%".substr($number_array[0],-10)."%') order by `Id` DESC";
	$caller=getRow($query);

	$salesforceLocalObject=new SalesForceLocalManager();
	$arr_salesforce = array();
	$arr_salesforce['call_to']= $_REQUEST['To'];
	$arr_salesforce['call_from']= $_REQUEST['From'];			
	$arr_salesforce['call_sid']= $_REQUEST['CallSid'];			
	$id=$salesforceLocalObject->insertCallVariables($arr_salesforce);
	$dialurl = 'update_caller_entry.php?incoming_call_log_id='.$id;
	//updateData("insert into twilio_debug set message='incoming call : ".$message."' , time_stamp ='".date("d-m-Y H:i:s")."'");
	
	?> 
	<Response>
        <Say voice="woman">Hello, <?= $caller['name']?$caller['name']:"" ?></Say>
        <Say voice="woman">Our agent will talk to you in a minute or two. Please Wait!</Say>
		<Dial action="<?php echo $dialurl; ?>" record="true" method="GET"> 
			<Conference endConferenceOnExit="true"><?=substr($_REQUEST['From'],-10)?></Conference>
		</Dial>
	</Response>
	<?
	updateData("Delete from incoming_calls where phone_number like '%".substr($_REQUEST['From'],-10)."%'");
	updateData("insert into incoming_calls set phone_number='".$_REQUEST['From']."', to_number='".$_REQUEST['To']."', call_sid='".$_REQUEST['CallSid']."',incoming_time='".date('Y-m-d H:i:s')."'");
}
else if(isset($_REQUEST['incoming_connection_num']) && $_REQUEST['incoming_connection_num']!=='') // comes in for inserting agent into conference while incoming call and direct call transfer process
{
	$conf_name = substr(htmlspecialchars($_REQUEST['incoming_connection_num']), -10);
	if(!(isset($from) && ($from == 'add_to_conference'))) // for call transfer process
		updateData("UPDATE incoming_calls set is_active='3' WHERE phone_number like '%".$conf_name."%'");	
?>
	<Response>
		<Dial>
			<Conference startConferenceOnEnter="true"><?php echo $conf_name; ?></Conference>
		</Dial>
	</Response>
<?php 
	$arr_details = getData("SELECT * FROM tbconference_ids WHERE conf_name='".$conf_name."'");
	if(count($arr_details) > 0)
		updateData("UPDATE tbconference_ids set agent_callsid='".$_REQUEST['CallSid']."', insert_date ='".date("Y-m-d H:i:s")."' WHERE `conf_name`='".trim($conf_name)."'");
	else
		updateData("insert into tbconference_ids set conf_name='".$conf_name."' , agent_callsid='".$_REQUEST['CallSid']."', insert_date ='".date("Y-m-d H:i:s")."'");
	//updateData("DELETE FROM tbconference_ids WHERE conf_name ='".$conf_name."'");
	
} 
else if(isset($_REQUEST['tocall']) && $_REQUEST['tocall']!=='')	// comes in for outgoing calls
{
	$number=$_REQUEST['tocall'];		
	$twilio_creds = getTwlioCreds();
	$client = new Services_Twilio($twilio_creds['sid'], $twilio_creds['auth_token']);
	if(isset($_REQUEST['call_log_id']))
		$url =  HTTP_ROOT.'dial_number2.php?call_log_id='.$_REQUEST['call_log_id'];
	else
		$url =  HTTP_ROOT.'dial_number2.php';
	
	$tonum = substr(htmlspecialchars($number), -10);
		
	$call = $client->account->calls->create(
					  $_REQUEST["from_phoneno"], // From this number // '+18553634827'
					  $number, // Call this number
					  $url,	
					  array(
					  	'StatusCallback' => HTTP_ROOT.'dial_number2.php?check_status=true&conf_name='.$tonum,	//.'&date='.date('YYYY-MM-DD')
						"Method" => "POST"
					  )
					);
	updateData("DELETE FROM tbconference_ids WHERE conf_name ='".$tonum."'");
	updateData("insert into tbconference_ids set conf_name='".$tonum."' , conference_sid='' , agent_callsid='".$_REQUEST['CallSid']."', insert_date ='".date("Y-m-d H:i:s")."'");
	
	
?>
<Response>	
    <Dial action="dial_number2.php?agentconfname=<?php echo $tonum; ?>" method="GET"> 
    	<Conference>
			 <?php echo $tonum; ?>
        </Conference>    	       
    </Dial>
</Response>
<?php
/*
<Response>	
    <Dial action="update_caller_entry.php< ?php if(isset($_REQUEST['call_log_id'])){echo "?call_log_id=".$_REQUEST['call_log_id'];} ?>" method="GET" record="true" callerId="< ?php echo $_REQUEST["from_phoneno"]; ?>"> 
    	<Number url="make_Conference.php"> 
			 < ?php echo htmlspecialchars($_REQUEST["tocall"]); ?>
        </Number>    	       
    </Dial>
</Response>*/
?>
<?php 
}
updateData("Delete from active_calls where phone_number like '%".substr($number,-10)."%'");
updateData("insert into active_calls set call_sid='".$_REQUEST['CallSid']."', phone_number='".$number."', is_active='1'");
?> 