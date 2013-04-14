<html>
<body>
<?php
require_once "dbinfo.php";
   if (!empty($_POST['first']) &&
       !empty($_POST['last']) &&
       !empty($_POST['email']) &&
       !empty($_POST['userid']) &&
       !empty($_POST['password']))
     {
	$con = mysql_connect($dbhost, $dbuser, $dbpwd);
	
	if (! $con)
	  {
	    die ('Could not connect: ' . mysql_error());
	  }
		
	mysql_select_db($db, $con);
		


	$result = mysql_query("SELECT userid FROM users WHERE userid='$_POST[userid]'");
	$count = mysql_num_rows($result);
	//This use already exists
	if ($count > 0)
	  {
	    Header("Location: signup.php?error=User already exists.");
	    exit;

	  }
	$hash = hash("md5", $_POST['password']);
	$result = mysql_query("INSERT INTO users VALUES ('$_POST[userid]', '$hash', '$_POST[first]', '$_POST[last]', '$_POST[email]', 'user', '0');");
		
	Header("Location: login.php");
       exit;
     }
       
?>

    <center>
        <h1> Please fill out all information</h1>
        <form action='signup.php' method='POST'>
	  First Name: <input type='text' name='first'><br>
	  Last Name: <input type='text' name='last'><br>
	  Email: <input type='text' name='email'><br>
	  User Name: <input type='text' name='userid'><br>
	  Password: <input type='password' name='password'><br>
	  <input type='submit'>
        </form>
    </center>
</body>
</html>
