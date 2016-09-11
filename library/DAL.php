<?
if (strpos($_SERVER["SCRIPT_NAME"],basename(__FILE__)) !== false)
{
	header("location: index.php");
	exit();
}


//function to retrive data from the database
		function getData($query) 
		{
			$query=trim($query); 
			$result = mysql_query($query) or die(mysql_error());
			$resArr = array();
			while($res = mysql_fetch_array($result,MYSQL_ASSOC)) 
			{
				$resArr[] = $res;
			}
			return $resArr;
		}
		
		
//function to retrive 1 row from the database
		function getRow($query) 
		{
			$query=trim($query); 
			$result = mysql_query($query) or die(mysql_error());
			return mysql_fetch_array($result,MYSQL_ASSOC);
		}
		
//function update database
		function updateData($query)
		{	$query=trim($query); 
			$result = mysql_query($query) or die(mysql_error());
			return $result;
		}
		
//Function to Get single value from the database
		function getOne($query)
		{	$query=trim($query); 
			$result = mysql_query($query) or die(mysql_error());
			$res = mysql_fetch_array($result);
			if($res)
				return $res[0];
			else
				return false;
		}
?>