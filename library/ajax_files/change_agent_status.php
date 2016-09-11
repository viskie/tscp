<?php
	session_start();
	if($_SERVER['REQUEST_METHOD']==="POST"){
		require_once('../Config.php');
		$is_available=$_REQUEST['is_available'];
		$now='';
		if($is_available){
			$now=',last_online=NOW()';
		}
		$agent_name=$_SESSION['user_name'];
		$agent_count=getOne("Select count(`agent_name`) from available_agents where agent_name='".$agent_name."'");
		
		if(intval($agent_count)===1)
		{
			updateData("update available_agents set is_available={$is_available}{$now} where agent_name='{$agent_name}'");
		}
		else
		{
			$groupid = $_SESSION['user_group'];
			updateData("Delete from available_agents where agent_name='{$agent_name}'");
			updateData("insert into available_agents set is_available={$is_available}, agent_name='{$agent_name}'{$now}, group_id=".$groupid);
		}
		$phone_number='';
		if($is_available=="true"){
			require_once('../twilio/Twilio.php');
			require_once('../commonFunctions.php');
			$twilio_creds=getTwlioCreds();
			$client = new Services_Twilio($twilio_creds['sid'], $twilio_creds['auth_token']);
			
			$no_incoming=FALSE;
			$call_sid='';
			$incoming_number=getOne("SELECT incoming_no FROM user_groups WHERE group_id='".$_SESSION['user_group']."'");
			$query="SELECT * from `incoming_calls` where to_number='".$incoming_number."' AND ((is_active=2 AND TIMESTAMPDIFF(SECOND,`last_assigned_time`,'".date("Y-m-d H:i:s")."')>15) OR is_active=1) order by incoming_time ASC";
			$call_details=getRow($query);
			if($call_details){
				updateData("UPDATE incoming_calls set is_active=2, last_assigned_time='".date("Y-m-d H:i:s")."' where call_sid='".$call_sid."'");
				$call_sid=$call_details['call_sid'];
				$phone_number=$call_details['phone_number'];
				
				$call = $client->account->calls->get($call_sid);
				while($call->status=="completed" || $call->status=="canceled"){
					updateData("DELETE FROM `incoming_calls` WHERE `call_sid` = '{$call_sid}'");
					$call_details=getRow($query);
					$call_sid=$call_details['call_sid'];
					$phone_number=$call_details['phone_number'];
					if(!$call_sid){
						$no_incoming=true;
						break;
					}else{
						$call = $client->account->calls->get($call_sid);
					}
				}
			}else{
				$no_incoming=true;
			}
			
			if(!$no_incoming){
				require_once('../REST/rest_functions.php');
				$lead_details=NULL;
				$totalSize=0;
				$response=array();
				$number=substr($phone_number,-10);
				
				$phone_number_array=array($phone_number);
				array_push($phone_number_array,'('.substr($phone_number,-10,3).') '.substr($phone_number,-7,3).'-'.substr($phone_number,-4)); //(862) 220-8402
				array_push($phone_number_array,'('.substr($phone_number,-10,3).')'.substr($phone_number,-7,3).'-'.substr($phone_number,-4)); //(862)220-8402
				array_push($phone_number_array,'('.substr($phone_number,-10,3).')'.substr($phone_number,-7,3).' '.substr($phone_number,-4)); //(862)220 8402
				array_push($phone_number_array,'('.substr($phone_number,-10,3).') '.substr($phone_number,-7,3).' '.substr($phone_number,-4)); //(862) 220 8402
				array_push($phone_number_array,'('.substr($phone_number,-10,3).') '.substr($phone_number,-7,3).substr($phone_number,-4)); //(862) 2208402
				array_push($phone_number_array,substr($phone_number,-10,3).'-'.substr($phone_number,-7,3).'-'.substr($phone_number,-4)); //862-220-8402
				array_push($phone_number_array,substr($phone_number,-10,3).'-'.substr($phone_number,-7,3).substr($phone_number,-4)); //862-2208402
				array_push($phone_number_array,substr($phone_number,-10,3).'-'.substr($phone_number,-7,3).' '.substr($phone_number,-4)); //862-220 8402
				array_push($phone_number_array,substr($phone_number,-10,3).' '.substr($phone_number,-7,3).' '.substr($phone_number,-4)); //862 220 8402
				array_push($phone_number_array,substr($phone_number,-10,3).substr($phone_number,-7,3).'-'.substr($phone_number,-4)); //862220-8402
				array_push($phone_number_array,substr($phone_number,-10,3).' '.substr($phone_number,-7,3).substr($phone_number,-4)); //862 2208402
				array_push($phone_number_array,substr($phone_number,-10,3).' '.substr($phone_number,-7,3).'-'.substr($phone_number,-4)); //862 220-8402
				if(strlen($phone_number)>10){
					array_push($phone_number_array,substr($phone_number,-11,4).'-'.substr($phone_number,-7,3).'-'.substr($phone_number,-4)); //7862-220-8402
					array_push($phone_number_array,substr($phone_number,-10));
				}
				
				$lead_details=get_name_of_number_leads($phone_number_array);
				$lead_decoded=json_decode($lead_details,true);				
				
				//$lead_details_array=array('done'=>true,'totalSize'=>0,'records'=>array());
				$notes=array();
				
				$response['lead_details']=$lead_decoded;
				$response['all_tasks']=$notes;
				$response['phone_number']=$phone_number;
				$response['success']=TRUE;

				if($lead_decoded){
					foreach($lead_decoded['records'] as $lead){
						$notes[]=json_decode(get_tasks_of($lead['Id']));
					}
//					$lead_details_array=array('done'=>true,'totalSize'=>count($lead_details),'records'=>$lead_details);
		
					$response['lead_details']=$lead_decoded;
					$response['all_tasks']=$notes;
				}
				echo json_encode($response);exit;
			}
		}
	}
	
?>