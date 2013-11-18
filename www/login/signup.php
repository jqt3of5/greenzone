<html>
<body>
<?php
require_once "dbinfo.php";
   if (!empty($_POST['email']) &&
       !empty($_POST['userid']) &&
       !empty($_POST['password']))
     {
	$con = mysql_connect($dbhost, $dbuser, $dbpwd);
	
	if (!$con)
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
	$hash = crypt($_POST['password']);
        exec("sudo /usr/sbin/useradd -s '/bin/false' -m -p '$hash' $_POST[userid]");
        exec("sudo /bin/chown .www-data /home/$_POST[userid]");
        exec("sudo /bin/chmod g+w /home/$_POST[userid]");
        exec("sudo /bin/mkdir /home/$_POST[userid]/upload");
	$result = mysql_query("INSERT INTO users VALUES ('$_POST[userid]', '$_POST[email]', '$hash');");
	Header("Location: login.php");
       exit;
     }
?>

    <center>
        <h1> Please fill out all information</h1>
        <form action='signup.php' method='POST'>
	  Email: <input type='text' name='email'><br>
	  User Name: <input type='text' name='userid'><br>
	  Password: <input type='password' name='password'><br>
	  <input type='submit'>
        </form>
    </center>
</body>
</html>
