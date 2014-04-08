<html>
<body>
<?php
require_once "dbinfo.php";
   if (!empty($_POST['username']) &&
       !empty($_POST['password']) &&
       !empty($_POST['retypedPassword']))
     {
        if ($_POST['password'] != $_POST['retypedPassword'])
        {
            Header("Location: signup.php?error=Passwords don\'t match");
            exit;
        }
	$con = mysql_connect($dbhost, $dbuser, $dbpwd);
	
	if (!$con)
	  {
	    die ('Could not connect: ' . mysql_error());
	  }
		
	mysql_select_db($db, $con);

	$result = mysql_query("SELECT username FROM users WHERE username='$_POST[username]'");
	$count = mysql_num_rows($result);
	//This use already exists
	if ($count > 0)
	  {
	    Header("Location: signup.php?error=User already exists.");
	    exit;
	  }
        mysql_query("INSERT INTO users (username,gecos,homedir,password)
                     VALUES ('$_POST[username]', 'No Name', '/home/$_POST[username]', ENCRYPT('$_POST[password]'))");
        mysql_query("INSERT INTO groups (name) VALUES ('$_POST[username]')");
	Header("Location: login.php");
       exit;
     }
?>

    <center>
        <h1> Please fill out all information</h1>
        <form action='signup.php' method='POST'>
	  Email: <input type='text' name='email'><br>
	  User Name: <input type='text' name='username'><br>
	  Password: <input type='password' name='password'><br>
	  Retype Password: <input type='password' name='retypedPassword'><br>
	  <input type='submit'>
        </form>
    </center>
</body>
</html>
