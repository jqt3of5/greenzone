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
	
	$result = mysql_query("SELECT userid, email, password FROM users WHERE userid='$_POST[user]';");
	
	$row = mysql_fetch_assoc($result);
	$hash = crypt($_POST['pwd'], $row['password']);		
			
	if ($hash == $row['password']){
	  $_SESSION['userid'] = $row['userid'];
	  $_SESSION['email'] = $row['email'];
	  $url = "/index.php";
	}else {
	  $url = "/login/login.php?error='Wrong user or password'";
	}
   } else {
     $url = "/login/login.php?error='Must supplie user and password'";
   }

Header("Location: $url");
exit;

?>
