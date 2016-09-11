<?
if (strpos($_SERVER["SCRIPT_NAME"],basename(__FILE__)) !== false)
{
	Header("location: index.php");
}
//buildSidebar();

include 'library/twilio/Twilio/Capability.php';
$twilio_creds=getTwlioCreds();
$token = new Services_Twilio_Capability($twilio_creds['sid'], $twilio_creds['auth_token']);
$token->allowClientOutgoing($twilio_creds['app_sid']);
$agent_name=$_SESSION['user_name'];
$token->allowClientIncoming($agent_name);
// @end snippet
//+16617480240 - Vishak sir skype
?>
		<!-- @start snippet -->
		<!-- <script type="text/javascript" src="//static.twilio.com/libs/twiliojs/1.1/twilio.min.js"></script> -->
        <script type="text/javascript" src="//static.twilio.com/libs/twiliojs/1.1/twilio.min.js"></script>        
		<script type="text/javascript">
		var num_reg_exp=/^\+{0,1}[1-9]{0,1}[0-9]{0,14}$/;
		var blinking_on,miss_call_check,latest_call_id=0, account_id=0, multiple_caller_response=null, is_on_call=false, is_on_hold=false, caller_name="", dialed_number=0, use_twilio_availability=true, agent_name='<?=$agent_name?>',agent_checking;
		
		$(document).ready(function(){
			Twilio.Device.setup("<?php echo $token->generateToken();?>");
		});	
		</script>
        <script type="application/javascript" language="javascript" src="JS/sidebar.js"></script>
        
			<ul>
				<li>
					<h3 class="dialer_title">Leads</h3>
					<ul>
						<li><a href="#" onclick="callSidebarPageAjax('show_all_leads.php','All Leads',null);">All Leads</a></li>
						<li><a href="#" onclick="callSidebarPageAjax('add_new_lead.php','Add Lead',null);">Add new Lead</a></li>
					</ul>
				</li>
				<li>
					<h3 class="dialer_title">Contacts</h3>
					<ul>
						<li><a href="#" onclick="callSidebarPageAjax('show_all_contacts.php','All Contacts',null);">All Contacts</a></li>
						<li><a href="#" onclick="callSidebarPageAjax('add_new_client.php','Add Contact',null);">Add new Contacts</a></li>
					</ul>
				</li>
				<li>
	                <h3 class="dialer_title">Agents</h3>
					<ul>
						<li><a href="#" onclick="callSidebarPageAjax('show_all_available_agents.php','Available Agents',null);">Available Agents</a></li>
					</ul>
				</li>
                <?php
				require_once('library/groupObject.php');
				$groupObject= new GroupManager();
				$allnos = $groupObject->getconnectedphonenos();
				?>
				<li>
					<h3 class="dialer_title">Dialer</h3>
                    <div class="sidebar_fromphn">
                    	<div>Select From Phone No :</div> 
                    	<div><?php //createComboBox('from_phoneno','incoming_no','incoming_no', $allnos,true); ?>
                        	<select name="from_phoneno" id="from_phoneno">
                            <?php
							for($i=0; $i<count($allnos); $i++)
							{
								?>
                                <option value="<?php echo $allnos[$i]['incoming_no']?>"><?php echo $allnos[$i]['incoming_no']?></option>
                                <?php 
							}
							?>
                            </select>
                        </div>
                    </div>
                    <div align="center" style="margin:5px 0;">
                        <!-- @start snippet -->
                        <input type="text" id="tocall" name="tocall" value="">
        <!--                <input type="button" id="call" value="Start Call"/>
                        <input type="button" id="hangup" value="Hangup Call" style="display:none;"/> -->
                        <div id="status">
                            Offline
                        </div>
                        <div id="dialpad">
                            <table>
                                <tr>
                                    <td><input type="button" value="" id="button1"></td>
                                    <td><input type="button" value="" id="button2"></td>
                                    <td><input type="button" value="" id="button3"></td>
                                    <td><input type="button" value="" id="buttondial" onclick="makeCall();"></td>
                                </tr>
                                <tr>
                                    <td><input type="button" value="" id="button4"></td>
                                    <td><input type="button" value="" id="button5"></td>
                                    <td><input type="button" value="" id="button6"></td>
                                    <td><input type="button" value="" id="buttonreject" onclick="resetBox();"></td>
                                </tr>
                                <tr>
                                    <td><input type="button" value="" id="button7"></td>
                                    <td><input type="button" value="" id="button8"></td>
                                    <td><input type="button" value="" id="button9"></td>
                                    <td><input type="button" value="" id="buttonmute" onclick="muteCall();"><input type="button" value="" id="buttonunmute" onclick="unmuteCall();" style="display:none;"></td>
                                </tr>
                                <tr>
                                    <td><input type="button" value="" id="buttonstar"></td>
                                    <td><input type="button" value="" id="button0"></td>
                                    <td><input type="button" value="" id="buttonhash"></td>
                                    <td><input type="button" value="" id="buttonplus"></td>
                                </tr>
                            </table>
                        </div>
                        <!-- @end snippet -->
                    </div>
				</li>
                <li id="note_container" style="display:none;text-align:center;">
                	<div id="loading" style="display:none" align="center"><img src="images/loading2.gif" alt="loading" /></div>
                    <h3 class="dialer_title">Note</h3>
                    <textarea rows="4"  id="on_call_note" maxlength="256" style="max-width:150px;"></textarea>
                    <input type="hidden" name="callid" id="callid" value="" />
                </li>                
                <li id="result_container" class="result_container" style="display:none;text-align:center;">
                    <h3 class="dialer_title">Call Result</h3>
                    <select name="selresult" id="selresult">
                    	<option value="">Select</option>
                   	  	<option value="Basic Information Given">Basic Information Given</option>
                        <option value="Prices Given">Prices Given</option>
                        <option value="Availability Checked">Availability Checked</option>
                        <option value="Prices & Availability">Prices & Availability</option>
                        <option value="Quote & Date Hold">Quote & Date Hold</option>
                        <option value="Booked Event - ND">Booked Event - ND</option>
                        <option value="Booked Event - D">Booked Event - D</option>
                        <option value="Payment Made">Payment Made</option>
                        <option value="Confirmed Call">Confirmed Call</option>
                        <option value="Next Up Call">Next Up Call</option>
                        <option value="Post Event Call">Post Event Call</option>
                        <option value="Answered Questions">Answered Questions</option>
                    </select>
                </li>
                <li id="disposition_container" class="disposition_container" style="display:none;text-align:center;">
                    <h3 class="dialer_title">Caller Disposition</h3>
                    <select name="seldisposition" id="seldisposition">
                    	<option value="">Select</option>
                    	<option value="Ecstatic">Ecstatic</option>
                        <option value="Happy">Happy</option>
                        <option value="Satisfied">Satisfied</option>
                        <option value="Fair">Fair</option>
                        <option value="Sad">Sad</option>
                        <option value="Angry">Angry</option>
                        <option value="Furious">Furious</option>
                    </select>                    
                </li>
                <li id="franchise_container" class="disposition_container" style="display:none;text-align:center;">
                    <h3 class="dialer_title">Franchise ID #</h3>
                    <select name="selfranchiseid" id="selfranchiseid">
                    	<option value="">Select</option>
                    	<?php
						for($i=1; $i<=25; $i++)
						{
						?>
                        <option value="<?php echo sprintf('%03u', $i);?>"><?php echo sprintf('%03u', $i);?></option>
                        <?php 
						}
						?>
                    </select>                    
                </li>                
                <li style="text-align:center"><input type="button" id="save_note" value="Save" style="margin-top:5px;"/></li>
               <!-- <li id="franchies_container" style="display:none;text-align:center;">
                    <h3 class="dialer_title">Franchisee</h3>
                    <textarea rows="4"  id="franchies" name="franchies" maxlength="256" style="max-width:150px;"></textarea>
                </li>-->
			</ul>
            <br />