<?php
require_once "../login/accesscontrol.php";
require_once "../login/dbinfo.php";
?>

<html>
  <body>
    <?php
       $guid = uniqid();
       if ($_FILES["file"]["error"] > 0)
       {
        echo "There was an error" . $_FILES["file"]["error"] . "<br>";
       }else{
        move_uploaded_file($_FILES["file"]["tmp_name"], "upload/".$guid);
       //store the guid, file name, type, time stamp, and owner in the database
       //Print out a direct access link to the file

        $con = mysql_connect($dbhost, $dbuser, $dbpwd) or die("Connection Failed");
        mysql_select_db($db, $con);

        $result = mysql_query("INSERT INTO files VALUES ('$guid', '$_FILES[file][name]', '$_SESSION[userid]'");
    
        echo "<a href='www.jqt3of5.com/download.php?guid=$guid'>www.jqt3of5.com/download.php?guid=$guid</a>";
        
       }
    ?>
  </body>
</html>
