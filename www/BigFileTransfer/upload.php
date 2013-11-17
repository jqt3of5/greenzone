<?php
require_once "../login/accesscontrol.php";
require_once "../login/dbinfo.php";


if ($_FILES["file"]["error"] > 0)
  {
    echo "There was an error" . $_FILES["file"]["error"] . "<br>";
  }
 else
   {
     $guid = uniqid();
     if (isset($_SESSION['userid']))
     {
         move_uploaded_file($_FILES["file"]["tmp_name"], "/home/$_SESSION[userid]/upload/$_FILES[files][name]"); 
     }
     else
     {
         move_uploaded_file($_FILES["file"]["tmp_name"], "/var/www/upload/".$guid);
     }

     //store the guid, file name, type, time stamp, and owner in the database
     //Print out a direct access link to the file

     $con = mysql_connect($dbhost, $dbuser, $dbpwd) or die("Connection Failed");
     mysql_select_db($db, $con);

     $result = mysql_query("INSERT INTO files VALUES ('$guid', '$_SESSION[userid]', '" . $_FILES["file"]["name"] . "',0);");
											           
     echo "<a href='download.php?guid=$guid'>http://localhost/BigFileTransfer/download.php?guid=$guid</a>";
													           
   }
?>
