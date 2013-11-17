<?php
    require_once "../login/dbinfo.php";

    
	 $con = mysql_connect($dbhost, $dbuser, $dbpwd) or die("Connection Failed");
	 mysql_select_db($db, $con);
	 
	 $result = mysql_query("SELECT fileName, user, count FROM files WHERE guid='$_GET[guid]'");
	 $row = mysql_fetch_assoc($result);
	 
	 if (isset($row['user']))
	 {
	     $path = "/home/$row[user]/upload/$row[fileName]";	
	 }
	 else
	 {
	     $path = "/var/www/upload/" . $_GET['guid'];
	 }
	 
	 if ($fd = fopen($path, "r"))
         {
 	 $queryString = sprintf("UPDATE files SET count=%d WHERE guid='$_GET[guid]'", $row['count'] + 1);
	 mysql_query($queryString);
	 mysql_close($con);
	 
	 $fsize = filesize($path);
	 
	 header('Content-type: application/octet-stream');
	 header("Content-Disposition: attachment; filename=$row[fileName]");
	 header("Content-length: $fsize");
	 header('Cache-control: no-cache, must-revalidate'); //use this to open files directly
	 while(!feof($fd)) 
	 {
            $buffer = fread($fd, 2048);
            echo $buffer;
         }
       }
       else
       {
         echo error_get_last();
       }
?>