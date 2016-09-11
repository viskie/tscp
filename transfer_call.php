<?php
	require_once('library/Config.php');
	extract($_REQUEST);
	header('Content-type: text/xml'); 
	$message = "transfer_call.php ---- ";
	foreach($_REQUEST as $key => $value)
	{
		$message.= $key." => ".$value." || ";
	}

	updateData("insert into twilio_debug set message='".$message."' , time_stamp ='".date("d-m-Y H:i:s")."'");
	
	if(isset($conf_for_make_call) && ($conf_for_make_call != ""))
	{
		?>
        <Response>
             <Dial>
                <Client>
                    <?php echo $conf_for_make_call; ?>
                </Client>  
            </Dial>
        </Response>
        <?php 
	}
	elseif(isset($conf_name) && ($conf_name != ""))
	{	
		updateData("UPDATE tbconference_ids set agent_callsid='".$CallSid."' WHERE conf_name = '".$conf_name."'");
		?>
        <Response>
            <Say voice="woman">Your call is being transfered!</Say>
            <Say voice="woman">Please wait for some time!</Say>
            <Dial>
                <Conference>
                    <?php echo $conf_name; ?>
                </Conference>  
            </Dial>
        </Response>
        <?php 
	}
		
?>
