<?
	session_start();
	set_time_limit(0);
	require_once('../Config.php');
	extract($_REQUEST);
	if(isset($from) && ($from == 'alert_dropdown'))
	{
		$agent_list = getData("SELECT GRP.group_id, is_active, incoming_no, agent_name, id as userid
						FROM (SELECT group_id, is_active, incoming_no FROM user_groups WHERE `is_active` =1 AND `incoming_no` != '') AS GRP
						LEFT JOIN (
						SELECT id, agent_name, group_id, is_available
						FROM available_agents where 1
						) AS USER ON GRP.`group_id` = USER.`group_id`
						WHERE GRP.`is_active` =1 AND USER.`is_available` = 1");
		$str = "<div id='agent_dropdown' style='padding:0px'>
					<select name='selagent' id='selagent' onchange='show_phnbox(this.value)'>
						<option value=''>Select</option>";
						for($i=0; $i<count($agent_list); $i++)
						{
							if($agent_list[$i]['agent_name'] != $_SESSION['user_name'])
								$str .= "<option value='".$agent_list[$i]['incoming_no']."' id='sel".$agent_list[$i]['agent_name']."'>".$agent_list[$i]['agent_name']."</option>";
						}
				$str .= "<option value='other'>Other</option>
					</select>
				</div>";
		echo $str;
	}
	else
	{
		header('Content-type: application/json');
		$result=getData("Select * from available_agents order by id");
		echo json_encode($result);
	}
?>



