<?php
require_once "accesscontrol.php";
require_once "dbinfo.php";
?>

<html>
  <head>
      <script src="domainTableFunction.js"></script>
  </head>

  <body>
    <h4> <?php echo $_SESSION['userid'] ?> is logged in! <a href="logout.php">Logout</a> </h4>
<?php

$con = mysql_connect($dbhost, $dbuser, $dbpwd) or die("Connection Failed");
mysql_select_db($db, $con);

$result = mysql_query("SELECT domain, zone FROM domains WHERE userid='$_SESSION[userid]'");

//This code should be changed  into a list of zones that can be edited. 
//Requests get sent to  zoneedit.php, verifications happens there
echo "<div id='response'> </div>";
echo "<table border='1' id='domainTable'>\n";
echo "<tr> <td><b>Domains Available</b></td> <td><b>Type</b></td> <td><b>Value</b></td></tr>\n";
$index = 1;
while ($row = mysql_fetch_array($result))
  {
    //use this spot to request from nodejs
    //========================================
    $type = "A";
    $value= "127.0.0.1";
    echo "<tr id='row$index'>\n";
    echo "<td id='domain$index'>".$row['domain'].".".$row['zone']."</td>\n";
    echo "<td id='type$index'>$type</td>\n";
    echo "<td id='value$index'>$value</td>\n";
    echo "</tr>\n";
    $index += 1;
  }
echo "</table>\n";

?>

<button id="addDomain" onClick="addrow()">Add Row</button>
<button id="submitChanges" onClick="submitChanges();location.reload();">Submit</button>

<br><br>

  </body>
</html>
