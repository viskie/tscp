<?php

	extract($_POST);
	$twilioCreds = getTwlioCreds();
	$salesforce_settings = getSalesforceSettings();
	
	// get all twilio phone numbers
	set_time_limit(0);
	include_once('library/twilio/Twilio.php');
	$client = new Services_Twilio($twilio_creds['sid'], $twilio_creds['auth_token']);
	require_once('library/groupObject.php');
	$groupObj= new GroupManager();
	
	/*$my_phone_numbers = $groupObj->getconnectedphonenos();
	$to = '+14247723149';
	if(isset($to) && multi_in_array($to,$my_phone_numbers))
	{
		$groupid = getOne('SELECT group_id FROM user_groups where incoming_no='.$to);
		echo $groupid;
		$agent=getOne('SELECT agent_name from available_agents where is_available=1 AND TIMESTAMPDIFF(SECOND,`last_online`,NOW())<5 AND group_id = '.$groupid.' order by `last_picked_call` ASC,`last_online` DESC');
		//$agent=getOne('SELECT agent_name from available_agents where is_available=1 AND TIMESTAMPDIFF(SECOND,`last_online`,NOW())<5 order by `last_picked_call` ASC,`last_online` DESC');
		echo $agent;
		exit;
	}*/
	
	if(isset($function) && ($function == 'updateSettings'))
	{
		
		$allnos = $groupObj->getallphonenos();
		$connected_nos = $groupObj->getconnectedphonenos();
		
		for($i=0; $i<count($allnos['all']); $i++)
		{
			if(multi_in_array($allnos['all'][$i],$chk_phn))
			{
				if(!multi_in_array($allnos['all'][$i],$allnos['connected']))
				{
					$number = $client->account->incoming_phone_numbers->get($allnos['sids'][$i]);
					$number->update(array(
								"VoiceApplicationSid" => $twilio_creds['app_sid']							
								));				
				}
			}
			else
			{
				if(multi_in_array($allnos['all'][$i],$allnos['connected']))
				{
					$number = $client->account->incoming_phone_numbers->get($allnos['sids'][$i]);
					$number->update(array(
								"VoiceApplicationSid" => ""						
								));				
				}
			}		
		}			
	}

	$allnos = array();
	$i=0;
	foreach ($client->account->incoming_phone_numbers as $number) 
	{	
		if(!in_array($number->phone_number, $arr_officeuseno))
		{
			$allnos[$i]['incoming_no'] = $number->phone_number;		 
			if($number->voice_application_sid == $twilio_creds['app_sid'])
			{
				$allnos[$i]['is_connected'] = 1;		 
			}
			else
				$allnos[$i]['is_connected'] = 0;
			$i++;
		}
	}	
?>

<div class="grid-24">
	<div class="widget">
		<div class="widget-content">
        		<div class="field-group">
                	<h4>Twilio Settings</h4>
                </div>
                <div class="field-group">
                    <div class="field_label">Account SID</div>
                    <div class="colon">:</div>
                    <div class="field_value"><input type="text" name="twilio_sid" id="twilio_sid" value="<?=$twilioCreds['sid']?>" style="width:300px"></div>
                    <div class="clr"></div>
                </div>
                <div class="field-group">
                    <div class="field_label">Auth Token</div>
                    <div class="colon">:</div>
                    <div class="field_value"><input type="text" name="twilio_auth_token" id="twilio_auth_token" value="<?=$twilioCreds['auth_token']?>"  style="width:300px"></div>
                    <div class="clr"></div>
                </div>
                <div class="field-group">
                    <div class="field_label">App ID</div>
                    <div class="colon">:</div>
                    <div class="field_value"><input type="text" name="twilio_app_sid" id="twilio_app_sid" value="<?=$twilioCreds['app_sid']?>"  style="width:300px"></div>
                    <div class="clr"></div>
                </div>
                <div id="loading" style="display:none" align="center"><img src="images/loading2.gif" alt="loading" /></div>
                <div class="phnno_section field-group">
                	<div class="phnlable"><h4>Twilio Phone Numbers Settings (Fields which are checked are already connected to application.)</h4></div>
                        <?php
						for($i=0; $i<count($allnos); $i++)
						{
						?>
                        <div class="field-group">
                        	<?php
							$check = ''; 
							if($allnos[$i]['is_connected'] == 1)
								$check = "checked='checked'";	
							?>
                            <div class="field_chkphn"><input type="checkbox" name="chk_phn[]" id="chk_phn[]" <?php echo $check; ?> value="<?php echo $allnos[$i]['incoming_no']; ?>"/></div>
                            <div><?php echo $allnos[$i]['incoming_no']; ?></div>
                            <div class="clr"></div>
                    	</div>
                        <?php
						}
						?>
               		<!--<div><input type="button" name="phnsubmit" id="phnsubmit" value="Connect" onclick="connect_phone()" class="btn"/></div>-->
                </div>                
                <br />
                
                <div class="field-group">
                	<h4>Salesforce Settings</h4>
                </div>
                <div class="field-group">
                    <div class="field_label">User Name</div>
                    <div class="colon">:</div>
                    <div class="field_value"><input type="text" name="salesforce_uname" id="salesforce_uname" value="<?=$salesforce_settings['uname']?>"  style="width:300px"></div>
                    <div class="clr"></div>
                </div>
                <div class="field-group">
                    <div class="field_label">Password</div>
                    <div class="colon">:</div>
                    <div class="field_value"><input type="text" name="salesforce_pass" id="salesforce_pass" value="<?=$salesforce_settings['pass']?>"  style="width:300px"></div>
                    <div class="clr"></div>
                </div>
                <div class="field-group">
                    <div class="field_label">Security Token</div>
                    <div class="colon">:</div>
                    <div class="field_value"><input type="text" name="salesforce_security_token" id="salesforce_security_token" value="<?=$salesforce_settings['security_token']?>"  style="width:300px"></div>
                    <div class="clr"></div>
                </div>
				<div class="field-group">
                    <div class="field_label">Client ID</div>
                    <div class="colon">:</div>
                    <div class="field_value"><input type="text" name="salesforce_client_id" id="salesforce_client_id" value="<?=$salesforce_settings['client_id']?>"  style="width:300px"></div>
                    <div class="clr"></div>
                </div>
				<div class="field-group">
                    <div class="field_label">Client Secret</div>
                    <div class="colon">:</div>
                    <div class="field_value"><input type="text" name="salesforce_client_secret" id="salesforce_client_secret" value="<?=$salesforce_settings['client_secret']?>"  style="width:300px"></div>
                    <div class="clr"></div>
                </div>
                <br /><br />
                <div id="addUser" class="actions">
                    <input type="button" class="btn" value=" Update Settings " onClick="updateSettings('settings.php','updateSettings')">
                </div>
        </div><!-- @end widget-content -->
    </div><!-- @end widget -->
</div><!-- @end grid-24 -->