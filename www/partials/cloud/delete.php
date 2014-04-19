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
    mysql_query("DELETE FROM bigFilesTable WHERE guid='$guid'");
    $path = "/home/$_SESSION[username]/uploads/$guid";
    unlink($path);
  }

mysql_close($con);
?>