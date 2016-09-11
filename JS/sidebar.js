// JavaScript Document
var num_reg_exp=/^\+{0,1}[1-9]{0,1}[0-9]{0,14}$/;
		var blinking_on,miss_call_check,latest_call_id=0, account_id=0, multiple_caller_response=null, is_on_call=false, is_on_hold=false, caller_name="", dialed_number='', use_twilio_availability=true,agent_checking,agent_checking,latest_agent_ajax_request=0,latest_agent_ajax_state=true,hide_incoming_timer=null,call_type=null,get_agent,check_disconnect='false', pickcall='false';
		
		$(document).ready(function(){
			
			$("#buttonClose").click(function () 
			{
				console.log("buttonclose click");
				
				$("#hider").fadeOut("slow");
				$('#popup_box').fadeOut("slow");
				var res = $("#selagent option:selected").text();
				$("#callpick_status").val("transfered_call");
				//var res = $("#selagent").val();
				
				if(res == 'Other')
				{
					res = $("#txtphono").val();
					res=stripNonNumericChars(res);
					if( (isNaN(res))  && (res.length==10))
					{
						res='1'+res
					}
					else
					{
						alert("Please enter valid phone number!");
						return false;
					}
				}
				if(res)
				{
					if(res!="") 
						{							
							var number='';
							number=stripNonNumericChars(dialed_number); 
							if(number == '')
							{
								number = $("#from_for_incoming").val();								
							}
							$.ajax({
								url:'call_transfer.php',
								type:'POST',
								data:'transfering_to='+res+'&call_to_transfer='+number,
								beforeSend:function(){
									$('#transfer_btn').val('Transferring...');
								},
								success:function(resp){
									$('#transfer_btn').val('Transfer Call');
									$("#callpick_status").val("transfered_call");
									$("#check_transfer").val('');
									hideIncomingCall();	
									if((typeof(connection1) != 'undefined') && (connection1))
									{
										connection1.disconnect();
										connection1.cancel();
									}
									Twilio.Device.disconnectAll();									
								},
								error: function(result) 
								{
									$('#transfer_btn').val('Transfer Call');																	
								}
							});
						}
						else 
						{
								alert("Please enter valid phone number!");
								return false;
						}
					}
					else
					{
						alert("Please enter or select phone number to transfer call");
					}
					$("#selagent").val('');
					$("#txtphono").val('');
				});
			
				
			connection=null;
			connection1=null;
			$("#call").click(function() {
				use_twilio_availability=false;
				changeAgentStatus(false);
				$('.switch_container li.on').removeClass('on');
				$("#switch_off").parent().addClass('on');
				
				params = { "tocall" : $('#tocall').val()};
				connection = Twilio.Device.connect(params);
				connection.disconnect(function(conn) {	
				  rejectCall();
				});
			});
			$("#hangup").click(function() {  
				Twilio.Device.disconnectAll();
			});

			Twilio.Device.ready(function (device) {
				if(use_twilio_availability){
					$('#status').text('Ready to start call');
					changeAgentStatus(true);
				}
			});

			Twilio.Device.incoming(function (conn) {
				console.log("twilio incoming");
				use_twilio_availability=false;
				changeAgentStatus(false);
				$('.switch_container li.on').removeClass('on');
				$("#switch_off").parent().addClass('on');
				connection=conn;
				
				if(conn.parameters.From.indexOf('client:')!=-1){ 
					showIncomingCall("Agent",conn.parameters.From);
					//$("#check_transfer").val('done');
				}
				else
				{
					showIncomingCall("Transferred Call",conn.parameters.From);
				}
				//connection.
				/*if (confirm('' + conn.parameters.From + '?')){
					connection=conn;
				    conn.accept();
				}*/
			});

			Twilio.Device.offline(function (device) {
				$('#status').text('Offline');
				changeAgentStatus(false);
			});

			Twilio.Device.error(function (error) {
				console.log("twilio error");
				console.log(error);
				if(error.message=="This AccessToken is no longer valid"){
					refreshCapabilityToken();
				}else{
					//Object {message: "User denied access to microphone.", code: 31208}
					$('#status').text(error.message);
				}
			});

			Twilio.Device.connect(function (conn) {
				console.log("twilio.device.connect");				
				use_twilio_availability=false;
				var check_call = $("#check_transfer").val();
				if(check_call != 'done')
					changeAgentStatus(false);
				$("#incoming_call_alert .buttonpick").hide();
				
				$("#incoming_call_alert .buttonpick").css('background','');
				$("#incoming_call_alert .buttonpick").css('cursor','');
				$("#incoming_call_alert .buttonpick").removeAttr('disabled');
				$('.switch_container li.on').removeClass('on');
				$("#switch_off").parent().addClass('on');
				//latest_call_id='123';
				/*if (typeof conn.parameters.CallSid !== "undefined"){	alert(conn.parameters.CallSid);  
					make_caller_entry(conn.parameters.CallSid,conn.parameters.From,conn.parameters.To);
					latest_call_id=conn.parameters.CallSid;
				}*/
				latest_call_id=conn.parameters.CallSid;
				$('#status').text("Successfully established call");
				$("#incoming_call_alert .message").html("IN Call with: ");
				clearTimeout(miss_call_check);
				clearTimeout(blinking_on);
				//toggleCallStatus();
				//$("#on_call_note").attr('readonly','readonly');
				//$("#note_container").fadeOut('slow');
			});

			Twilio.Device.disconnect(function (conn) {	//alert("in disconnect method");
				console.log("twilio.device.disconnect");
				console.log("From = "+conn.parameters.From+" To = "+conn.parameters.To); 
				$('#status').text("Call ended");
				clearTimeout(blinking_on);
				clearTimeout(miss_call_check);
				callpick_status = $("#callpick_status").val(); 
				/*if(callpick_status == "transfered_call")
				{
					$("#check_transfer").val("");
					number = '';
					number=stripNonNumericChars(dialed_number);   
					if(number == '' || number == 0)
					{
						number = $("#from_for_incoming").val();								
					}	
					//end_internal_call(number);				
				}*/
				console.log("twilio.device.disconnect - before rejectcall");
				
				if((typeof(conn.parameters.From) != 'undefined' && typeof(conn.parameters.To) != 'undefined') && conn.parameters.From.indexOf('client:')!=-1 && conn.parameters.To.indexOf('client:')!=-1){
					console.log("twilio.device.disconnect - in if");
					hideIncomingCall();
				}
				else
				{
					console.log("twilio.device.disconnect - in else");
					rejectCall('disconnect'); 
				}
				console.log("twilio.device.disconnect - end");
			});
			
			function toggleCallStatus(){
				$('#call').toggle();
				$('#hangup').toggle();
				$('#dialpad').slideToggle();
			}

			$.each(['0','1','2','3','4','5','6','7','8','9','star','hash','plus'], function(index, value) { 
		    	$('#button' + value).click(function(){ 
					if(connection) {
						if (value=='star')
							connection.sendDigits('*')
						else if (value=='hash')
							connection.sendDigits('#')
						else if(value != 'plus')
							connection.sendDigits(value)
						return false;
					}else{
						var new_val="";
						if (value=='star')
							new_val=$("#tocall").val()+"*";
						else if (value=='hash')
							new_val=$("#tocall").val()+"#";
						else if (value=='plus')
							new_val=$("#tocall").val()+"+";
						else
							new_val=$("#tocall").val()+""+value;
							
						if(num_reg_exp.test(new_val)){
							$("#tocall").val(new_val);
						}
					}
				});
			});

			$("#tocall").keypress(function(e) {
				var key_code;
				if(e.keyCode){
					key_code=e.keyCode;
				}else{
					key_code=e.which;
				}
				
				if(key_code!=8 && key_code!=37 && key_code!=39 && key_code!=43 && !(key_code>=48 && key_code<=57)){
					return false;
				}
				//return false;
            });
			$("#save_note").click(function(){
				if(account_id!==0){
					
					var note=$.trim($("#on_call_note").val());
					//var selresult = $("#selresult").val();
					//var seldisposition = $("#seldisposition").val();
					//var selfranchiseid = $("#selfranchiseid").val();
					//var from_phoneno = $("#from_phoneno").val();
					if(note!=''){
						note=urlEncode(note);

						if(navigator.onLine){
							$.ajax({
								url:"library/ajax_files/save_call_note.php",
								type:'POST',
								data: {
										call_id : latest_call_id,
										note	: note,
										account_id	: account_id,
										//selresult	: selresult,
										//seldisposition	: seldisposition,
										//selfranchiseid	: selfranchiseid,
										//from_phoneno	: from_phoneno
									 },								
								beforeSend:function(){
									 $("#save_note").val("saving...");
									 $("#save_note").attr('disabled','disabled');
									 $("#loading").show();
									 $('#mainForm').css('opacity', 0.5);
									 $("#loading").css('opacity', 1);
								},
								success:function(resp){
									//alert(resp);
									$("#on_call_note").val("");
									$("#selresult").val("");
									$("#seldisposition").val("");
									$("#save_note").val("Save Note");
									$("#save_note").removeAttr('disabled');
									$("#loading").hide();
									$('#mainForm').css('opacity', 1);									
								}
							});
						}else{
							alert("Please Check your internet connection!");
						}
					}
				}
			});
			
			// Change Switch
			$("ul.switch_container li").click(function(){
				$("ul.switch_container li").removeClass("on");
				$(this).addClass("on");
				if($(this).find('a').attr('id')==="switch_on"){
					use_twilio_availability=true;
					if(Twilio.Device.status()=="ready"){
						$('#status').text('Ready to start call');
						changeAgentStatus(true);
					}
				}else{
					use_twilio_availability=false;
					changeAgentStatus(false);
					$("#status").text('Offline');
				}
				return false;
			});
			
			// for playing music for incoming call
			$("#jquery_jplayer_1").jPlayer( {
				ready: function () {
				$(this).jPlayer("setMedia", {
				mp3: "incoming.mp3" // Defines the m4v url									
				}); // Attempts to Auto-Play the media
				},
				ended: function() { // The $.jPlayer.event.ended event
						$(this).jPlayer("play"); // Repeat the media
						},
				solution: "flash, html", // Flash with an HTML5 fallback.
				supplied: "mp3",
				swfPath: "Jplayer.swf"				
			});		
			//////////////////////////////////
			
			$("#switch_on").parent().addClass('on');
			setInterval('markAvaillability()',5000);
			setInterval('refreshCapabilityToken();',900000);//every 15 min				
			
		}); // document.ready
		
		function refreshCapabilityToken(){
			if(navigator.onLine){
				$.ajax({
					url:"library/ajax_files/refresh_twilio_token.php",
					type:"POST",
					beforeLoad:function(){
						$('#status').text("Refreshing Token Please Wait...");
					},
					success:function(token){
						if(token){
							Twilio.Device.setup(token);
						}
					}		
				});
			}else{
				alert("Please Check your internet connection!");
			}
		}
		function blinkCallAlert(){
			$("#incoming_call_alert .blinking").fadeToggle('fast');
			//$("#incoming_call_alert .blinking").animate({borderColor:#FFB649},'fast');
		}
		function pickCall(number){	
			$("#incoming_call_alert .buttonpick").css('background','url(images/btn_loading.gif) 3px 2px no-repeat');
			$("#incoming_call_alert .buttonpick").css('cursor','wait');
			$("#incoming_call_alert .buttonpick").attr('disabled','disabled');
			clearTimeout(blinking_on);
			clearTimeout(hide_incoming_timer);
			$("#dialpad #buttonreject,#popup_buttonreject").attr('onclick','rejectCall();');
			
			$("#incoming_call_alert .message").html("IN Call with: ");
			//$("#callpick_status").val("afterpick");
			pickcall = true;
			stopmp3();
			//$("#dialpad #buttonreject").attr('onclick','rejectCall("afterpick");');
			//$("#incoming_call_alert #popup_buttonreject").attr('onclick','rejectCall("afterpick");');
			
			//alert(account_id);
			//alert(account_id); return false;
			//account_id ='00Q5000000fxv11EAA';
			if(account_id!=''){
						$.ajax({
							url:"library/ajax_files/make_caller_entry.php",
							data:{
									from : number,
									account_id	: account_id,
									call_type	: 'incoming'
								},						
							type:'POST'
						});	
					}
		
			$("#note_container").show();
			$("#result_container").show();
			$("#disposition_container").show();
			$("#franchise_container").show();
			$("#save_note").hide();	
			var params= { "incoming_connection_num" : number};
			connection = Twilio.Device.connect(params);
			connection.disconnect(function(conn) {
				  rejectCall();
				});	
						
		}
		function muteCall(){
			if(connection){
				connection.mute();
				$("#incoming_call_alert .message").html("IN Call with(muted): ");
				$("#buttonmute").hide();
				$("#buttonunmute").show();
			}
		}
		function unmuteCall(){
			if(connection){
				connection.unmute();
				$("#incoming_call_alert .message").html("IN Call with: ");
				$("#buttonunmute").hide();
				$("#buttonmute").show();
			}
		}
		function rejectCall(from, number)
		{ 
			stopmp3();
			var check_call = $("#check_transfer").val();
			console.log("rejectCall outside check_call="+check_call);
			if(check_call != 'done')
			{	
				console.log("In reject Call check_call="+check_call);
				from = from || ''; 
				if(from = '')
				{
					from = $("#callpick_status").val();
				}
				number = number || '';
				
				console.log("In reject Call check_call inside if ");
				$("#incoming_call_alert .message").html("Call ended: ");
				clearTimeout(blinking_on);
				clearTimeout(miss_call_check);					
				setTimeout("$('#incoming_call_alert').slideUp('slow');",1000);
				$("#dialpad #buttondial").attr('onclick','makeCall();');
				$("#dialpad #buttonreject").attr('onclick','resetBox();');			
				if(call_type == 'incoming')
				{
					var phone_no = $("#from_for_incoming").val();
				}
				else if(call_type =='outgoing')
				{
					var phone_no = $("#tocall").val();					
				}
				$.ajax({
					url:"library/ajax_files/end_conference.php",
					data:{
							number	 		: phone_no							
						},
					type:"POST",
					success:function(){
					
					}
				});	
				/*if(from =='afterpick' || from =='afterpick_outgoing' || from == "transfered_call" || from == "end_internal_call" || from == 'disconnect') 
				{	
					if(from =='afterpick')
					{
						var phone_no = $("#from_for_incoming").val();
					}
					else if(from =='afterpick_outgoing')
					{
						var phone_no = $("#tocall").val();					
					}
					$.ajax({
						url:"library/ajax_files/end_conference.php",
						data:{
								number	 		: phone_no							
							},
						type:"POST",
						success:function(){
						
						}
					});	
				}*/
				/*if(from == "end_internal_call")
				{
					$("#check_transfer").val("");
				}
				else
				{*/
				//if(connection || from == 'disconnect' || from =='afterpick' || from =='afterpick_outgoing' || from == "transfered_call" || from != "end_internal_call" || from == 'disconnect')
				//{					
					
				if(pickcall == true)
				{	
					pickcall = false;
					var note = $("#on_call_note").val();				
					var fromno = $("#from_for_incoming").val(); //from_phoneno
					/*var calltype = 'outgoing';
					if(from == 'afterpick')
					{
						fromno = $("#from_for_incoming").val();
						calltype = 'incoming';					
					}*/
					var to = $("#tocall").val();
					var result = $("#selresult").val();
					var disposition = $("#seldisposition").val();
					var selfranchiseid = $("#selfranchiseid").val();
					var callid = $("#callid").val();
					$.ajax({
						url:"library/ajax_files/save_sidebar_note.php",
						data:{
								note 		: note,
								result		: result,
								disposition	: disposition,
								selfranchiseid	: selfranchiseid,
								from		: fromno,
								to			: to,
								callid		: callid,
								calltype	: call_type		
							},
						type:"POST",
						success:function(data){
							 $("#on_call_note").val('');
							 $("#selresult").val('');
							 $("#seldisposition").val('');
							 $("#callid").val('');
							 $("#from_for_incoming").val('');
							 $("#check_transfer").val('');
							 $("#callpick_status").val('')
							 $("#tocall").val('');
							 return false;
						}
					});	
					call_type=null;
				}
				dialed_number = '';			
				$("#save_note").show();
				$("#result_container").hide();
				$("#disposition_container").hide();
				$("#franchise_container").hide();
				$("#resume_button").hide();	
				if(pickcall == true && call_type == 'incoming')
				{
					$("#note_container").hide();						
				}
				
				//}								
				if(connection){
					connection.cancel();
					connection=null;			
				}
				Twilio.Device.disconnectAll(); 
				latest_call_id = '';
			}
			else
			{
				console.log("rejectcall else for end internal call");
				check_endcall = $("#callpick_status").val();
				//$("#callpick_status").val("transfered_call");  
				console.log("rejectCall else"+check_endcall);
				if(check_endcall == "end_internal_call")
				{
					//console.log("rejectCall123 endcall if "+check_endcall);
					$("#check_transfer").val("");
					//rejectCall();
				}				
			}
		}
		function makeCall(){
			if($("#tocall").val()!="" && num_reg_exp.test($('#tocall').val())){
				$("#dialpad #buttondial").attr('onclick','return false;');
				$("#dialpad #buttonreject").attr('onclick','rejectCall();');
				dialed_number=$('#tocall').val();
				
				use_twilio_availability=false;
				changeAgentStatus(false);
				$('.switch_container li.on').removeClass('on');
				$("#switch_off").parent().addClass('on');
				
				params = { "tocall" : $('#tocall').val(), "from_phoneno" : $('#from_phoneno').val()};
				connection = Twilio.Device.connect(params);
				//getDialedCallSid(dialed_number);
				showOutgoingCall('No Name',dialed_number);
				connection.disconnect(function(conn) {
					rejectCall();
				});
			}
		}
		function resetBox(){
			$("#tocall").val('');
			if(connection){
				connection=null;
			}
		}
		function getNameOfCaller(number){
			if(navigator.onLine){
				$.ajax({
					url:'library/ajax_files/get_salesforce_details_of_number.php',
					type:'POST',
					data:'number='+number,
					beforeSend:function(){
						$("#multiple_callers_div").hide();
						blinking_on=setInterval('blinkCallAlert()',500);
						miss_call_check=setInterval('checkRejection()',500);
						showIncomingCall("Fetching Details",number);
						$("#incoming_call_alert .phone_number").addClass('loading');
					},
					success:function(resp){
						$("#incoming_call_alert .phone_number").removeClass('loading');
						if(connection && connection.status()=="pending"){
							if(resp.lead_details.done){
								if(resp.lead_details.totalSize==1){
									account_id=resp.lead_details.records[0].Id;
									caller_name=resp.lead_details.records[0].Name;
									showIncomingCall(resp.lead_details.records[0].Name,number);
									var json_obj={lead_details:resp.lead_details.records[0],all_tasks:resp.all_tasks};
									setLeadDefaultDetails(json_obj);
								}else if(resp.lead_details.totalSize>1){
									multiple_caller_response=resp;
									var all_callers="";
									for(var i=0;i<resp.lead_details.totalSize;i++){
										all_callers+="<a href='#' class='multiple_callers_number' onclick='selectTheCaller(\""+i+"\",\""+number+"\")'>"+resp.lead_details.records[i].Name+"</a>";
									}
									$("#multiple_callers_div").html(all_callers);
									$("#incoming_call_alert .phone_number").html('Choose Caller'+"("+number+")");
									$("#multiple_callers_div").slideDown();
								}else{
									showIncomingCall("Unknown",number);
									caller_name="Unknown";
									callSidebarPageAjax('add_new_lead.php','Add Lead',"$('#Phone').val(param1);",number);
								}
							}else if(resp[0].errorCode=="INVALID_SESSION_ID"){
								alert("Your Session has been expired!\nPlease Login Again to Continue");
								window.location="index.php";
							}else{
								alert("Error Fetching details. Please Contact Administrator!\nError Code:"+resp[0].message);
							}
						}else{//if(connection && connection.status()=="pending"){
							if(resp.lead_details.done){
								if(resp.lead_details.totalSize>=1){
									account_id=resp.lead_details.records[0].Id;
									$("#incoming_call_alert .phone_number").html(resp.lead_details.records[0].Name+"("+number+")");
									var json_obj={lead_details:resp.lead_details.records[0],all_tasks:resp.all_tasks};
									setLeadDefaultDetails(json_obj);
								}else{
									$("#incoming_call_alert .phone_number").html("Unknown"+"("+number+")");
									callSidebarPageAjax('add_new_lead.php','Add Lead',"$('#Phone').val(param1);",number);
								}
							}
							gotMissedCall();
						}
					}
				});
			}else{
				alert("Please Check your internet connection!");
			}				
		}
		function selectTheCaller(index,number){
			account_id=multiple_caller_response.lead_details.records[index].Id;
			showIncomingCall(multiple_caller_response.lead_details.records[index].Name,number);
			var json_obj={lead_details:multiple_caller_response.lead_details.records[index],all_tasks:multiple_caller_response.all_tasks[index]};
			caller_name=multiple_caller_response.lead_details.records[index].Name;
			setLeadDefaultDetails(json_obj);
			multiple_caller_response=null;
			$("#multiple_callers_div").slideUp();
		}
		function checkRejection(){
			if(connection){
				if(connection.status()=="closed"){
					gotMissedCall();	
				}
			}
		}
		function gotMissedCall(){
			$("#incoming_call_alert .message").html("Call Missed: ");
			clearTimeout(blinking_on);
			clearTimeout(miss_call_check);
			$("#dialpad #buttondial").attr('onclick','makeCall();');
			$("#dialpad #buttonreject").attr('onclick','resetBox();');
			$("#multiple_callers_div").slideUp();
			Twilio.Device.disconnectAll();
			if(connection){
				connection.cancel();
				connection=null;
			}
		}
		function showIncomingCall(name,number){
			$("#incoming_call_alert .message").html("INCOMING CALL FROM: ");
			$("#incoming_call_alert .phone_number").html(name+"("+number+")");
			$("#incoming_call_alert .buttonpick").show();
			$("#incoming_call_alert").slideDown();
			call_type='incoming';
			
			if(name == 'Agent' || name == "Transferred Call")
			{
				$("#dialpad #buttondial,#incoming_call_alert .buttonpick").attr('onclick',"connection.accept()");
				$("#dialpad #buttonreject,#popup_buttonreject").attr('onclick','rejectCall();');
				//$("#check_transfer").val('done');				
			}
			else
			{
				$("#dialpad #buttondial,#incoming_call_alert .buttonpick").attr('onclick','pickCall("'+number+'");');
				$("#dialpad #buttonreject,#popup_buttonreject").attr('onclick','hideCall()');
			}
			
			//$("#dialpad #buttonreject").attr('onclick','rejectCall();');
			
			//$("#incoming_call_alert #popup_buttonreject").attr('onclick','hideCall()');
			$("#main_content_div").fadeOut('slow',function(){
			$("#add_lead_form").fadeIn('fast');
			});
		}
		function hideCall() 
		{			
			clearTimeout(hide_incoming_timer);
			var phone_no = $("#from_for_incoming").val();
			$.ajax({
					url:"library/ajax_files/make_agent_active.php",
					data:{
							phone_no		: phone_no
						},
					type:"POST",
					success:function(data){
						hideIncomingCall();						
					}
				});							
		}
		function showOutgoingCall(name,number){
			caller_name=name;
			$("#incoming_call_alert .message").html("CALLING TO: ");
			$("#incoming_call_alert .phone_number").html(name+"("+number+")");
			$("#incoming_call_alert .buttonpick").hide();
			$("#incoming_call_alert").slideDown();
			$("#dialpad #buttondial").attr('onclick','return false;');
			//$("#dialpad #buttonreject").attr('onclick','rejectCall("afterpick_outgoing")');
			//$("#incoming_call_alert #popup_buttonreject").attr('onclick','rejectCall("afterpick_outgoing")');
			//$("#callpick_status").val("afterpick_outgoing");
			pickcall = true;
			call_type='outgoing';
/*			$("#main_content_div").fadeOut('slow',function(){
				$("#add_lead_form").fadeIn('fast');
			});*/
		}
		function setLeadDefaultDetails(json_obj){
			callSidebarPageAjax('add_new_lead.php',"Lead Details","setDefaultDetails(param1,'lead');",json_obj);
		}
		function setContactDefaultDetails(json_obj){
			callSidebarPageAjax('add_new_client.php',"Contact Details","setDefaultDetails(param1,'contact');",json_obj);
		}
		function setDefaultDetails(json_obj,type){
			var main_json_object=json_obj;
			if(type=="lead"){
				main_json_object=json_obj.lead_details;
			}else if(type=="contact"){
				main_json_object=json_obj.contact_details;
			}
			for(prop in main_json_object){
				$('.'+prop+'_label .value_label').html(main_json_object[prop]);
			}
			for(prop in main_json_object){
				if($('#'+prop).hasClass('date_picker_alt')){
					setDatepickerDate('.date_picker',main_json_object[prop],'.date_picker_alt');
				}else if($('#'+prop).is('input[type="text"]')){
					$('#'+prop).val(main_json_object[prop]);
				}else if($('#'+prop).is('textarea')){
					$('#'+prop).html(main_json_object[prop]);
				}else if($('#'+prop).is('select')){
					$('#'+prop+' option[value="'+main_json_object[prop]+'"]').attr('selected','selected');
				}
			}
			fillNonTexts(json_obj,type);
/*			$('.inputbox').fadeOut('fast',function(){
				$('.labelbox').fadeIn('fast');
			});*/
		}
		function fillNonTexts(json_obj,type){
			$("#note_container").fadeIn('fast');
			//$("#result_container").fadeIn('slow');
			//$("#disposition_container").fadeIn('slow');
			//$("#franchise_container").fadeIn('slow');
			
			if(type=="lead"){
				var save_onclick_function=$("#save_lead").attr('onclick');
				$("#save_lead").attr('onclick',save_onclick_function.replace('add','update'));
				$("#lead_id").val(json_obj.lead_details['Id']);
				$("#lead_form_call").attr('onclick','call_lead("'+json_obj.lead_details['Name']+'","'+json_obj.lead_details['Phone']+'","'+json_obj.lead_details['Id']+'")');

				account_id=json_obj.lead_details['Id'];
				//$("#on_call_note").removeAttr('readonly');

				var table_html= "<table class='table table-bordered table-striped display all_notes_table'><thead><tr><th style='width:40px;'>Sr. No.</th><th>Record URL</th><th>Description</th></thead><tbody>";
				if(json_obj.all_tasks.totalSize>=1){
					for(i=0;i<json_obj.all_tasks.totalSize;i++){
						table_html+="<tr><td style='width:40px;'>"+(i+1)+"</td><td>"+((json_obj.all_tasks.records[i].RecordingURL__c==null)?put_in_center("-"):"<a href="+json_obj.all_tasks.records[i].RecordingURL__c+" target='_blank'>"+json_obj.all_tasks.records[i].RecordingURL__c+"</a>")+"</td><td>"+((json_obj.all_tasks.records[i].Description==null)?put_in_center("-"):json_obj.all_tasks.records[i].Description)+"</td></tr>";
					}
				}
				table_html+="</tbody> </table>";

				$("#all_notes").html(table_html);
				convertToDataTables('.all_notes_table',{"iDisplayLength":10,"aLengthMenu":[[10, 20, 50, 100, -1], [10, 20, 50, 100, "All"]]});
			}else if(type=="contact"){
				var save_onclick_function=$("#save_contact").attr('onclick');
				$("#save_contact").attr('onclick',save_onclick_function.replace('add','update'));
				$("#contact_id").val(json_obj.contact_details['Id']);
				account_id=json_obj.contact_details['Id'];
				$("#contact_form_call").attr('onclick','call_lead("'+json_obj.contact_details['Phone']+'")');

				var table_html= "<table class='table table-bordered table-striped display all_notes_table'><thead><tr><th style='width:40px;'>Sr. No.</th><th>Record URL</th><th>Description</th></thead><tbody>";
				if(json_obj.all_tasks.totalSize>=1){
					for(i=0;i<json_obj.all_tasks.totalSize;i++){
						table_html+="<tr><td style='width:40px;'>"+(i+1)+"</td><td>"+((json_obj.all_tasks.records[i].RecordingURL__c==null)?put_in_center("-"):"<a href="+json_obj.all_tasks.records[i].RecordingURL__c+" target='_blank'>"+json_obj.all_tasks.records[i].RecordingURL__c+"</a>")+"</td><td>"+((json_obj.all_tasks.records[i].Description==null)?put_in_center("-"):json_obj.all_tasks.records[i].Description)+"</td></tr>";
					}
				}
				table_html+="</tbody> </table>";

				$("#all_notes").html(table_html);
				convertToDataTables('.all_notes_table',{"iDisplayLength":10,"aLengthMenu":[[10, 20, 50, 100, -1], [10, 20, 50, 100, "All"]]});
			}
		}
		function call_lead(name,number,account_id){	
			if(number=='null' || number==''){
				alert("No number to call!");
			}else{
				number=stripNonNumericChars(number);
				if(number.length>=10){
					number='+1'+number.substr(number.length-10);
				}
				var from_phoneno = $("#from_phoneno").val();
				var note=$.trim($("#on_call_note").val());
				var selresult = $("#selresult").val();
				var seldisposition = $("#seldisposition").val();
				var selfranchiseid = $("#selfranchiseid").val();
				if(navigator.onLine){
					$.ajax({
						url:"library/ajax_files/make_caller_entry.php",
						data:{
								to : number,
								note	: note,
								account_id	: account_id,
								selresult	: selresult,
								seldisposition	: seldisposition,
								selfranchiseid	: selfranchiseid,
								from_phoneno	: from_phoneno
							},						
						type:'POST',
						async:false,
						success:function(resp){
							if(resp.done){	
								$("#callid").val(resp.id);
								$("#tocall").val(number);
								doCall(number,resp.id);
								showOutgoingCall(name,number);
							}
						}
					});
				}else{
					alert("Please Check your internet connection!");
				}
			}
		}
		function doCall(number,call_log_id){	
			use_twilio_availability=false;
			changeAgentStatus(false);
			$('.switch_container li.on').removeClass('on');
			$("#switch_off").parent().addClass('on');
			var from_phoneno = $("#from_phoneno").val();
			$("#note_container").show();
			$("#result_container").show();
			$("#disposition_container").show();
			$("#franchise_container").show();
			$("#save_note").hide();
			//var call_no = $("#callid").val();
			params = { "tocall" : number, "call_log_id": call_log_id, "from_phoneno" : from_phoneno};
			dialed_number=number;
			connection = Twilio.Device.connect(params);
			//getDialedCallSid(dialed_number);
			connection.disconnect(function(conn) {
			  //rejectCall();
			  //rejectCall('afterpick',dialed_number);
			})
		}
		function stripNonNumericChars(pstrSource){ 
			var m_strOut = new String(pstrSource); 
			m_strOut = m_strOut.replace(/[^0-9]/g, ''); 
		
			return m_strOut; 
		}
		function callSidebarPageAjax(page_name,title,successCallback,param1){
			clearInterval(agent_checking);
			latest_call_id="Non call note";
			//$("#on_call_note").attr('readonly','readonly');
			$("#note_container").fadeOut('slow');
			$("#result_container").fadeOut('slow');
			$("#disposition_container").fadeOut('slow');
			//account_id=0;
			if(navigator.onLine){
				$.ajax({
					url:page_name,
					success:function(resp){
						$("#main_content_div").fadeOut('slow',function(){
							setPageTitle(title);
							$("#sidebar_content_div").fadeIn();
							$('#sidebar_content_div').html(resp);
							convertToDatePicker(".date_picker");
							if(successCallback!==null){
								eval(successCallback);
							}
						});
					}
				});
			}else{
				alert("Please Check your internet connection!");
			}
		}
		
		function hideSidebarContent(){
			$("#sidebar_content_div").fadeOut('slow',function(){
				$("#main_content_div").fadeIn();
			});
		}
		function make_caller_entry(call_id,from,to){
			if(navigator.onLine){
				$.ajax({
					url:"library/ajax_files/make_caller_entry.php",
					type:"POST",
					data:"call_id="+call_id+"&account_id="+account_id+"&from="+from+"&to="+to,
					success:function(resp){
						$("#callid").val(resp.id);
					}
				});
			}else{
				alert("Please Check your internet connection!");
			}
		}
	
		function putCallOnHold(from){
			console.log("put call on hold"+from);
			from = from || '';
			if(connection !== null && (connection.status()=="pending" || connection.status()=="open" || connection.status()=="connecting")){
				var number='';
				if(call_type=='incoming'){
					number=$("#from_for_incoming").val();
				}else if(call_type=='outgoing'){
					number=$("#tocall").val();
				}else{
						alert("Called hold on no call");
						return false;
					
				}
				if(navigator.onLine){
					$.ajax({
						url:"call_hold.php",
						type:"POST",
						data:"call_to_put_on_hold="+number,
						beforeSend:function(){
							if(from != 'makecall')
							{
								$("#hold_btn").val("Please Wait...");
							}														
						},
						success:function(resp){
							if(from != 'makecall')
							{
								$("#hold_btn").val("Hold call");
								$("#resume_btn").val("Resume Call with "+caller_name);
								$("#incoming_call_alert,#multiple_callers_div").hide();
								$("#resume_button").show();								
							}
							is_on_hold=true;
						},
						error: function(result) 
							{
								$("#hold_btn").val("Hold call"); 
							}	
					});
				}else{
					alert("Please Check your internet connection!");
				}
			}
		}
		function resumeCall(from){
			console.log("resume call"+from);
			from = from || '';
			if(navigator.onLine){
				var number='';
				if(call_type=='incoming'){
					number=$("#from_for_incoming").val();
				}else if(call_type=='outgoing'){
					number=$("#tocall").val();
				}else{
					if(from != 'makecall')
					{
						alert("Called Resume on no call");
						return false;
					}					
				}
				
				$.ajax({
					url:"call_resume.php", 
					data:{
							phone_number : number,
							from		 :	from
						},	//"phone_number="+number,
					type:"POST",
					beforeSend:function(){
							if(from != 'makecall')
							{
								$("#resume_btn").val("Please Wait...");
							}	
							else
							{
								$('#btnmakecall').val('Resuming...');
							}
						}, 
					success:function(resp){
						if(from != 'makecall')
						{
							$("#resume_btn").val("Resume");
							$("#resume_button").hide();
							$("#incoming_call_alert").show();  
						}
					}	
				});
			}else{
				alert("Please Check your internet connection!");
			}
		}
		function getDialedCallSid(number){
			if(connection !== null && (connection.status()=="pending" || connection.status()=="open" || connection.status()=="connecting")){
				number=stripNonNumericChars(number);

				if(navigator.onLine){
					$.ajax({
						url:'get_dialed_call_sid.php',
						data:'number='+number,
						type:'POST',
						beforeSend:function(){
							$("#hold_btn,#transfer_btn").attr('onclick','return false;');
							$("#hold_btn,#transfer_btn").addClass('loading_btn loading');
						},
						success:function(resp){
							if(resp==='recall' || resp==='MySQL server has gone away'){
								getDialedCallSid(number);
							}else{
								try{
									resp=JSON.parse(resp);
									if(resp.got_it){
										latest_call_id=resp.call_sid;
										got_dialed_call_sid_callback();
									}
								}catch(e){
									alert("Error fetching Call SID! Contact Administrator.");
								}
							}
						}
					});
				}else{
					alert("Please Check your internet connection!");
				}
			}
		}
		function got_dialed_call_sid_callback(){
			$("#hold_btn").attr('onclick','putCallOnHold();');
			$("#transfer_btn").attr('onclick','transferCall();');
			$("#hold_btn,#transfer_btn").removeClass('loading_btn loading');
		}
		function changeAgentStatus(isAvailable){
			if(latest_agent_ajax_request!=0){
				if(latest_agent_ajax_state===isAvailable && parseInt(latest_agent_ajax_request.readyState)!==4){
					return false;
				}
				latest_agent_ajax_request.abort();
			}
			latest_agent_ajax_state=isAvailable;
			if(navigator.onLine){
				latest_agent_ajax_request=$.ajax({
													url:'library/ajax_files/change_agent_status.php',
													type:"POST",
													data:"is_available="+isAvailable,
													beforeSend:function(){ 
														var test = $("#callpick_status").val();
														if(connection==null && connection1==null && ($("#callpick_status").val() != "transfered_call") && ($("#check_transfer").val() != "done") && ($("#check_transfer").val() != "end_internal_call"))
														{															
															$("#incoming_call_alert,#multiple_callers_div").hide();
														}
													},
													success:function(resp){
														if(isAvailable){
															if(resp!=""){
																try{
																	resp=JSON.parse(resp);
																	if(resp.success){
																		use_twilio_availability=false;
																		$('.switch_container li.on').removeClass('on');
																		$("#switch_off").parent().addClass('on');
																		$("#status").html('Processing...');
																		$("#from_for_incoming").val(resp.phone_number);
																		hide_incoming_timer=setTimeout("hideIncomingCall();",14000);
																		if(resp.lead_details.totalSize==1){
																			account_id=resp.lead_details.records[0].Id;
																			caller_name=resp.lead_details.records[0].Name;
																			showIncomingCall(resp.lead_details.records[0].Name,resp.phone_number);
																			var json_obj={lead_details:resp.lead_details.records[0],all_tasks:resp.all_tasks[0]};
																			setLeadDefaultDetails(json_obj);
																		}else if(resp.lead_details.totalSize>1){
																			multiple_caller_response=resp;
																			var all_callers="";
																			for(var i=0;i<resp.lead_details.totalSize;i++){
																				all_callers+="<a href='#' class='multiple_callers_number' onclick='selectTheCaller(\""+i+"\",\""+resp.phone_number+"\")'>"+resp.lead_details.records[i].Name+"</a>";
																			}
																			$("#multiple_callers_div").html(all_callers);
																			$("#incoming_call_alert .phone_number").html('Choose Caller'+"("+resp.phone_number+")");
																			$("#incoming_call_alert").slideDown();
																			$("#multiple_callers_div").slideDown();
																		}else{
																			showIncomingCall("Unknown",resp.phone_number);
																			playmp3();
																			account_id= '';
																			caller_name="Unknown";
																		}
																	}
																}catch(e){
																	alert("Error in incoming/outgoing connection!\nContact Administrator.");
																	window.location="index.php";
																}
																$('.availability_false').removeClass('availability_false').addClass('availability_idle');
																$('.availability_true').removeClass('availability_true').addClass('availability_idle');
															}else{
																$('.availability_false').removeClass('availability_false').addClass('availability_true');
																$('.availability_idle').removeClass('availability_idle').addClass('availability_true');
															}
														}else{
															$('.availability_true').removeClass('availability_true').addClass('availability_false');
															$('.availability_idle').removeClass('availability_idle').addClass('availability_false');
														}
													}
												});
			}else{
				alert("Please Check your internet connection!");
			}
		}
		function markAvaillability(){
			if(use_twilio_availability){
				if(Twilio.Device.status()=="ready"){
					changeAgentStatus(true);
				}
			}
		}
		function transferCall(){
			console.log("Transfer call");	
			get_available_agents();
			$('#divphone').hide();
			$('#popup_box').fadeIn("fast");
			if(latest_call_id!="Non call note" && latest_call_id!=0){				
				if(connection && (connection.status()=="pending" || connection.status()=="open" || connection.status()=="connecting"))
				{
					var agentname = $("#agent_name").val();
					$("#sel"+agentname).hide();					
				}
			}
		}
		
		function hideIncomingCall(){
			stopmp3();
			$("#incoming_call_alert,#multiple_callers_div").slideUp();
			current_status=0;
			$("#switch_off").click();
			
		}
		function make_agent_call()
		{
			console.log("make call agent - start");
			var res = $("#selagent").val();
			var agentname = '';
			$("#callpick_status").val("transfered_call"); 
			if(res == 'other')
			{
				res = $("#txtphono").val();
				res=stripNonNumericChars(res);
				if(res!="" && num_reg_exp.test(res) && res.length>=10)
				{
					if(res.length==10){
						res='1'+res
					}
				}
				else
				{
					alert("Please enter valid phone number to transfer!");
					return false;
				}
			}
			else
			{
				agentname = $("#selagent option:selected").text();
			}
			if(res)
			{				
				var number='';
				number=stripNonNumericChars(dialed_number);  
				if(number == '' || number == 0)
				{
					number = $("#from_for_incoming").val();								
				}				
				$("#check_transfer").val('done');
				putCallOnHold('makecall');
				
				/*if(connection){
					connection.cancel();
				}
				Twilio.Device.disconnectAll();
				connection = null;
				var params= { "transfering_to" : res, "agentname" : agentname, "conf_name_no" : number}; 
				connection1 = Twilio.Device.connect(params);
				console.log("success.calltransfer="+connection1);
				$('#btnmakecall').val('End Call');
				$("#btnmakecall").attr('onclick','end_internal_call("'+number+'");');	
				connection1.disconnect(function(conn) { 
					  console.log("connection1.disconnect="+connection1);						 
					  end_internal_call(number);
				});	*/	
						
				$.ajax({ 
					url:'call_transfer.php',
					type:'POST', 
					data:{
							from				:	"make_call",
							transfering_to		:	res,
							call_to_transfer	:	number
						},				
					beforeSend:function(){
						$('#btnmakecall').val('Making Call...'); 
						$("#btnmakecall").attr('onclick','return false');
					},
					success:function(resp){	
						if(connection){
							connection.disconnect();
							connection.cancel();
						}
						Twilio.Device.disconnectAll();
						connection = null;
						var params= { "transfering_to" : res, "agentname" : agentname, "conf_name_no" : number}; 
						connection1 = Twilio.Device.connect(params);
						console.log("success.calltransfer="+connection1);
						$('#btnmakecall').val('End Call');
						$("#btnmakecall").attr('onclick','end_internal_call("'+number+'");');	
						connection1.disconnect(function(conn) { 
							  console.log("connection1.disconnect="+connection1);
							  if(check_disconnect == false)						 
							  { end_internal_call(number); }
						});		
					},
					error: function(result) 
					{
						$('#btnmakecall').val('Make Call');
						$("#btnmakecall").attr('onclick','make_agent_call();');	
					}
				});			
			}
		}
		function end_internal_call(conf_name_no)
		{
			$("#callpick_status").val("end_internal_call");
			$("#check_transfer").val('done'); 
			check_disconnect = true;
			console.log("end internal call");
			//console.trace();
			if(connection1){
				connection1.disconnect();
				if(connection1)
					connection1.cancel();
			}
			connection1 = null;
			Twilio.Device.disconnectAll();
			
			var params = { "incoming_connection_num" : conf_name_no, "from"	: 'add_to_conference'};
			connection = Twilio.Device.connect(params);
			resumeCall('makecall');
			$("#selagent").val('');
			$("#txtphono").val('');
			
			$('#btnmakecall').val('Make Call');
			$("#btnmakecall").attr('onclick','make_agent_call();');
			
			/*$.ajax({
					url:'library/ajax_files/end_conference.php',
					type:'POST',
					data:{
							from			: 	"end_call",
							conf_name_no	:	conf_name_no
							//callsid		:	callsid						
						},				
					success:function()
					{	
						var params= { "incoming_connection_num" : conf_name_no, "from"	: 'add_to_conference'};
						connection = Twilio.Device.connect(params);
						resumeCall('makecall');
						$("#selagent").val('');
						$("#txtphono").val('');
						$("#check_transfer").val('');
						$('#btnmakecall').val('Make Call');
						$("#btnmakecall").attr('onclick','make_agent_call();');
					}
				});*/
				
		}
		function get_available_agents()
		{
			$.ajax({
				url:'library/ajax_files/get_all_available_agents.php',
				type:'POST',
				data:{
						from	: 	"alert_dropdown",										
					},				
				success:function(new_html)
				{	
					$("#agent_dropdown").html(new_html);
				}
			});
		}
		// for playing music
		function playmp3()
		{
			$("#jquery_jplayer_1").jPlayer("play");
		}		
		function stopmp3()
		{
			$("#jquery_jplayer_1").jPlayer("stop");
		}