<?php

 if (!session_start())
   {
       echo "Fail!";
	   exit;
   }

require_once "dbinfo.php";

   if (!empty($_POST['userid']) && !empty($_POST['password']))
   {		//connect and check the db for this user
	$con = mysql_connect($dbhost, $dbuser, $dbpwd);
	if (! $con)
	  {
	    die ('Could not connect: ' . mysql_error());
	  }
		
	mysql_select_db($db, $con);
	
	$result = mysql_query("SELECT user, email, password FROM users WHERE user='$_POST[userid]';");
	
	$row = mysql_fetch_assoc($result);
	$hash = crypt($_POST['password'], $row['password']);		
			
	if ($hash == $row['password']){
	  $_SESSION['userid'] = $row['user'];
	  $_SESSION['email'] = $row['email'];
	  $url = "/index.php";
	}else {
	  $url = "/login/login.php?error='Wrong user or password'";
	}
   } else {
     $url = "/login/login.php?error='Must supply user and password'";
   }

Header("Location: $url");
exit;

?>
