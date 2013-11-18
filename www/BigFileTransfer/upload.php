<?php
//require_once "../login/accesscontrol.php";
if (!session_start())
  {
    echo "Fail!";
    exit;
  }
require_once "../login/dbinfo.php";

for ($i = 0; $i < count($_FILES["files"]["name"]); $i++)
{

    if ($_FILES["files"]["error"][$i] > 0)
    {
	echo "There was an error" . $_FILES["files"]["error"][$i] . "<br>";
    }
    else
    {
	$guid = uniqid();
	if (isset($_SESSION['userid']))
	{
	    move_uploaded_file($_FILES["files"]["tmp_name"][$i], "/home/$_SESSION[userid]/upload/" . $_FILES['files']['name'][$i]); 
	}
	else
	{
	    move_uploaded_file($_FILES["files"]["tmp_name"][$i], "/var/www/upload/".$guid);
	}

	//store the guid, file name, type, time stamp, and owner in the database
	//Print out a direct access link to the file

	$con = mysql_connect($dbhost, $dbuser, $dbpwd) or die("Connection Failed");
	mysql_select_db($db, $con);

	$result = mysql_query("INSERT INTO files VALUES ('$guid', '$_SESSION[userid]', '" . $_FILES["files"]["name"][$i] . "',0);");
	
	echo "<a href='download.php?guid=$guid'>" . $_FILES["files"]["name"][$i] . "</a><br>";
	
    }
}
?>
