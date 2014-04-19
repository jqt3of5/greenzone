<?php require_once "../../login/accesscontrol.php";

$con = mysql_connect($dbhost, $dbuser, $dbpwd) or die("Connection Failed");
mysql_select_db($db, $con);

$result = mysql_query("SELECT uid FROM users WHERE username='$_SESSION[username]'");
$uid = mysql_fetch_assoc($result)['uid'];

$result = mysql_query("SELECT guid, filename, size FROM bigFilesTable WHERE uid='$uid'");

$json = "[";
while ($row = mysql_fetch_assoc($result))
  {
    $json = $json . '{"filename": "'.$row['filename'].'", "size":"'.$row['size'].'", "guid":"'.$row['guid'].'", "type":"text"},';
  }

$json = trim($json, ",") . "]";

echo $json;
?>