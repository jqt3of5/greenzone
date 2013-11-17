<?php
require_once "../login/dbinfo.php";

session_start();

$con = mysql_connect($dbhost, $dbuser, $dbpwd);
	
if (! $con)
  {
    die ('Could not connect: ' . mysql_error());
  }
		
mysql_select_db("accounts", $con);

$result = mysql_query("UPDATE users SET loggedin='0' WHERE userid='$_SESSION[userid]';");

$params = session_get_cookie_params();
setcookie(session_id(),"",0, $params['path'], $params['domain'], $params['secure'], isset($params['httponly']));
session_destroy();

Header("Location: login.php");
?>