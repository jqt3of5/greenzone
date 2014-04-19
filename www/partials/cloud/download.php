<?php require_once "../../login/accesscontrol.php";

$guid = $_GET['guid'];

if (!isset($guid))
  {
    exit;
  }

$con = mysql_connect($dbhost, $dbuser, $dbpwd) or die("Connection Failed");
mysql_select_db($db, $con);

$result = mysql_query("SELECT uid FROM users WHERE username='$_SESSION[username]'");
$uid = mysql_fetch_assoc($result)['uid'];

$result = mysql_query("SELECT filename, size FROM bigFilesTable WHERE guid='$guid' AND uid='$uid'");

if ($row = mysql_fetch_assoc($result))
  {
    $path = "/home/$_SESSION[username]/uploads/$guid";
    $fsize = $row['size'];
    if ($fd = fopen($path, "r"))
      {
	header('Content-type: application/octet-stream');
	header("Content-Disposition: attachment; filename=\"$row[filename]\"");
	header("Content-length: $fsize");
	header('Cache-control: no-cache, must-revalidate'); //use this to open files directly
	while(!feof($fd)) 
	  {
            $buffer = fread($fd, 2048);
            echo $buffer;
	  }
      }
    else
      {
	echo error_get_last();
      }
  }

mysql_close($con);
?>