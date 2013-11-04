<?php
    require_once "../login/dbinfo.php";

    $path = "upload/" . $_GET['guid'];
    if ($fd = fopen($path, "r"))
       {
	 $con = mysql_connect($dbhost, $dbuser, $dbpwd) or die("Connection Failed");
	 mysql_select_db($db, $con);

	 $result = mysql_query("SELECT name, count FROM files WHERE guid='$_GET[guid]'");
	 $queryString = sprintf("UPDATE files SET count=%d WHERE guid='$_GET[guid]'", $result['count'] + 1);
	 mysql_query($queryString);
	 $fsize = filesize($path);

	 header('Content-type: application/octet-stream');
	 header("Content-Disposition: filename=$result[name]");
	 header("Content-length: $fsize");
	 header('Cache-control: private'); //use this to open files directly
	 while(!feof($fd)) 
	 {
            $buffer = fread($fd, 2048);
            echo $buffer;
         }
       }
?>