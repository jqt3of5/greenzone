<?php
require_once "../login/accesscontrol.php";
require_once "../login/dbinfo.php";
?>

<html>
  <head>
  </head>

  <body style="background: grey;">
    <div style="text-align: center; background: grey">
      <div id='header'  style="text-align: right;"> 
	<?php
	   if (isset($_SESSION['userid']))
	   {
	       echo "<a href='/login/account.php' id='accountButton' class='button'>Account</a> ";
	       echo "<a href='/login/logout.php' id='signoutButton' class='button'>Logout</a> ";
	   } else {
	       echo "<a href='/login/signup.php' id='signUpButton' class='button'>Sign Up</a> ";
	       echo "<a href='/login/login.php' id='loginButton' class='button'>Login</a> ";
	   }
	?>
      </div>
      <div style="margin-left: auto; margin-right: auto; width: 400px; background: #00aa00; overflow: hidden; border:3px solid; border-radius:20px;">
	<h1>Big File Transfer</h1><br>
	When E-Mail isn't enough. Use this utility to transfer large files. <br><br>

	<div id='uploadRegion' style="background: #00ff00;"> 
	  Drag and Drop files here. 
	  <form action="upload.php" method="post" enctype="multipart/form-data">
	    <input type="file" name="file" id="file"><br>
	    <input type="submit" value="Submit">
	  </form>
	</div>
	
	<div id='fileListRegion' style="background: darkgrey; ">
	  <table style="width: 100%; text-align: center;">
	    <?php
	       if (!isset($_SESSION['userid']))
	       {
	       exit(0);
	       }
	       $con = mysql_connect($dbhost, $dbuser, $dbpwd) or die("Connection Failed");
	       mysql_select_db($db, $con);
	       
	       $result = mysql_query("SELECT guid, fileName FROM files WHERE userid='$_SESSION[userid]'");
	       while ($row = mysql_fetch_array($result))
	       {
               echo "<tr>";
               echo "<td><a href='/BigFileTransfer/download.php?guid=$row[guid]'>$row[fileName]</a></td>";
               echo "</tr>";
	       }

	       mysql_close($con);
	       ?>
	  </table>

	</div>
      </div>
    </div>
  </body>
</html>
