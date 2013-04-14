<?php

 if (!session_start())
   {
       echo "Fail!";
	   exit;
   }

require_once "dbinfo.php";

   if (!empty($_POST['user']) && !empty($_POST['pwd']))
   {		//connect and check the db for this user
	
	$con = mysql_connect($dbhost, $dbuser, $dbpwd);
	
	if (! $con)
	  {
	    die ('Could not connect: ' . mysql_error());
	  }
		
	mysql_select_db($db, $con);
		
	$hash = hash("md5", $_POST['pwd']);
	$result = mysql_query("SELECT userid, email, firstName, lastName, role FROM users WHERE userid='$_POST[user]' AND password='$hash';");
		
	$count = mysql_num_rows($result);
		
	if ($count == 1){
	  $row = mysql_fetch_assoc($result);
	  $_SESSION['userid'] = $row['userid'];
	  $_SESSION['email'] = $row['email'];
	  $_SESSION['role'] = $row['role'];
	  $result = mysql_query("UPDATE users SET loggedin='1' WHERE userid='$_SESSION[userid]'");
	  $url = "/index.php";
	}else {
	  $url = "/login.php?error='Wrong user or password'";
	}
		
		
   } else {
     $url = "/login.php?error='Must supplie user and password'";
   }

Header("Location: $url");
exit;

?>
