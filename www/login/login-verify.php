<?php

 if (!session_start())
   {
       echo "Fail!";
	   exit;
   }

require_once "dbinfo.php";

   if (!empty($_POST['username']) && !empty($_POST['password']))
   {		//connect and check the db for this user
	$con = mysql_connect($dbhost, $dbuser, $dbpwd);
	if (! $con)
	  {
	    die ('Could not connect: ' . mysql_error());
	  }
		
	mysql_select_db($db, $con);
	
	$result = mysql_query("SELECT username, password FROM users WHERE username='$_POST[username]';");
	
	
	$row = mysql_fetch_assoc($result);
	$hash = crypt($_POST['password'], $row['password']);		
			
	if ($hash == $row['password']){
	  $_SESSION['username'] = $row['username'];

	  $result = mysql_query("SELECT name FROM grouplist NATURAL JOIN groups WHERE username='$_POST[username]';");	
	  $_SESSION['groups'] = array();
	  while ($groupRow = mysql_fetch_array($result))
	  {	  
	      $_SESSION['groups'][] = $groupRow[0];
	  }
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
